<?php

use App\DTO\UserSettings;
use App\Jobs\SuggestPhotoItem;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoSuggestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('measures model accuracy against the items users tagged', function (): void {
    [$item1, $item2, $item3] = Item::factory()->count(3)->create();

    $topOneHit = PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id, $item3->id])
        ->create(['is_accepted' => true]);
    PhotoItem::factory()->create(['photo_id' => $topOneHit->photo_id, 'item_id' => $item1->id]);

    $topThreeHit = PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id, $item3->id])
        ->create();
    PhotoItem::factory()->create(['photo_id' => $topThreeHit->photo_id, 'item_id' => $item3->id]);

    $miss = PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create(['is_accepted' => false]);
    PhotoItem::factory()->create(['photo_id' => $miss->photo_id, 'item_id' => $item3->id]);

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create();

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsTable(
            ['Score Range', 'Tagged Photos', 'Top-1 Hit', 'Top-3 Hit'],
            [
                ['40-59', 3, '33.3%', '66.7%'],
                ['All', 3, '33.3%', '66.7%'],
            ],
        );
});

it('filters by minimum score', function (): void {
    [$predictedItem, $otherItem] = Item::factory()->count(2)->create();

    $hit = PhotoSuggestion::factory()
        ->withPredictions([$predictedItem->id])
        ->create(['item_score' => 90]);
    PhotoItem::factory()->create(['photo_id' => $hit->photo_id, 'item_id' => $predictedItem->id]);

    $lowScoreMiss = PhotoSuggestion::factory()
        ->withPredictions([$predictedItem->id])
        ->create(['item_score' => 20]);
    PhotoItem::factory()->create(['photo_id' => $lowScoreMiss->photo_id, 'item_id' => $otherItem->id]);

    $this->artisan('app:suggestion-metrics --min-score=50')
        ->assertSuccessful()
        ->expectsOutputToContain('min score: 50')
        ->expectsTable(
            ['Score Range', 'Tagged Photos', 'Top-1 Hit', 'Top-3 Hit'],
            [
                ['80-100', 1, '100%', '100%'],
                ['All', 1, '100%', '100%'],
            ],
        );
});

it('only counts photos uploaded since the given date', function (): void {
    $this->travelTo('2026-10-12 12:00:00');
    [$predictedItem, $otherItem] = Item::factory()->count(2)->create();

    $oldHit = PhotoSuggestion::factory()
        ->withPredictions([$predictedItem->id])
        ->create(['photo_id' => Photo::factory()->create(['created_at' => '2026-09-01 10:00:00'])]);
    PhotoItem::factory()->create(['photo_id' => $oldHit->photo_id, 'item_id' => $predictedItem->id]);

    $recentMiss = PhotoSuggestion::factory()
        ->withPredictions([$predictedItem->id])
        ->create(['photo_id' => Photo::factory()->create(['created_at' => '2026-10-01 10:00:00'])]);
    PhotoItem::factory()->create(['photo_id' => $recentMiss->photo_id, 'item_id' => $otherItem->id]);

    $this->artisan('app:suggestion-metrics --since=2026-09-28')
        ->assertSuccessful()
        ->expectsOutputToContain('(since 2026-09-28)')
        ->expectsTable(
            ['Score Range', 'Tagged Photos', 'Top-1 Hit', 'Top-3 Hit'],
            [
                ['40-59', 1, '0%', '0%'],
                ['All', 1, '0%', '0%'],
            ],
        );
});

it('rejects an invalid since date', function (): void {
    $this->artisan('app:suggestion-metrics --since=not-a-date')
        ->assertFailed()
        ->expectsOutputToContain('Invalid --since date: not-a-date');
});

it('reports suggestion coverage and failed jobs grouped by error', function (): void {
    $this->travelTo('2026-10-12 12:00:00');
    $item = Item::factory()->create();

    PhotoSuggestion::factory()->withPredictions([$item->id])->create();
    Photo::factory()->create();
    Photo::factory()->create([
        'user_id' => User::factory()->create(['settings' => new UserSettings(litterbot_enabled: false)]),
    ]);

    foreach ([101, 202] as $photoId) {
        DB::table('failed_jobs')->insert([
            'uuid' => fake()->uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => SuggestPhotoItem::class]),
            'exception' => "RuntimeException: Failed to get photo suggestions for photo {$photoId}\n#0 stack trace",
            'failed_at' => now(),
        ]);
    }

    $this->artisan('app:suggestion-metrics --since=2026-10-12')
        ->assertSuccessful()
        ->expectsTable(
            ['Metric', 'Value', 'Target'],
            [
                ['Uploaded photos', '2', '-'],
                ['Got a suggestion', '1 (50%)', '~100%'],
                ['Shown to user (score >= 30)', '1 (50%)', '-'],
                ['Failed suggestion jobs', '2', '0'],
                ['Queued or retrying now', '0', '-'],
            ],
        )
        ->expectsTable(
            ['Error', 'Count', 'Last failed'],
            [
                ['RuntimeException: Failed to get photo suggestions for photo N', 2, '2026-10-12 12:00:00'],
            ],
        );
});

it('labels the lowest score bucket from the minimum score', function (): void {
    $item = Item::factory()->create();

    $suggestion = PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create(['item_score' => 35]);
    PhotoItem::factory()->create(['photo_id' => $suggestion->photo_id, 'item_id' => $item->id]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsTable(
            ['Score Range', 'Tagged Photos', 'Top-1 Hit', 'Top-3 Hit'],
            [
                ['30-39', 1, '100%', '100%'],
                ['All', 1, '100%', '100%'],
            ],
        );
});

it('handles no suggestions gracefully', function (): void {
    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('No tagged photos with multi-item suggestions yet');
});
