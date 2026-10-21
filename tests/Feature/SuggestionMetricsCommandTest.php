<?php

use App\DTO\UserSettings;
use App\Jobs\SuggestPhotoItem;
use App\Models\Item;
use App\Models\Photo;
use App\Models\PhotoItem;
use App\Models\PhotoSuggestion;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('displays suggestion metrics', function (): void {
    $photo1 = Photo::factory()->create();
    $photo2 = Photo::factory()->create();
    $photo3 = Photo::factory()->create();
    $item = Item::factory()->create();

    // Accepted suggestion
    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'photo_id' => $photo1->id,
            'item_id' => $item->id,
            'item_score' => 90,
            'is_accepted' => true,
        ]);

    // Rejected suggestion
    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'photo_id' => $photo2->id,
            'item_id' => $item->id,
            'item_score' => 40,
            'is_accepted' => false,
        ]);

    // Pending suggestion
    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'photo_id' => $photo3->id,
            'item_id' => $item->id,
            'item_score' => 70,
            'is_accepted' => null,
        ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('3');
});

it('displays acceptance by score bucket', function (): void {
    $item = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 95,
            'is_accepted' => true,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 30,
            'is_accepted' => false,
        ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Score Range');
});

it('filters by minimum score', function (): void {
    $item = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 90,
            'is_accepted' => true,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 20,
            'is_accepted' => false,
        ]);

    $this->artisan('app:suggestion-metrics --min-score=50')
        ->assertSuccessful()
        ->expectsOutputToContain('min score: 50')
        ->expectsOutputToContain('1 / 1 (100%)');
});

it('shows brand acceptance when brand tags exist', function (): void {
    $item = Item::factory()->create();
    $brand = Tag::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item->id], [$brand->id])
        ->create([
            'item_id' => $item->id,
            'item_score' => 90,
            'is_accepted' => true,
            'brand_accepted' => true,
        ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Brand Suggestion Acceptance (multi-item)');
});

it('displays rank distribution for multi-item suggestions', function (): void {
    $item1 = Item::factory()->create();
    $item2 = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'accepted_item_rank' => 1,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'accepted_item_rank' => 2,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'accepted_item_rank' => 2,
        ]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Accepted Item Rank Distribution (multi-item)')
        ->expectsOutputToContain('Multi-suggestion uplift');
});

it('applies min-score filtering to rank distribution', function (): void {
    $item1 = Item::factory()->create();
    $item2 = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'item_score' => 95,
            'accepted_item_rank' => 1,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item1->id, $item2->id])
        ->create([
            'is_accepted' => true,
            'item_score' => 20,
            'accepted_item_rank' => 2,
        ]);

    $this->artisan('app:suggestion-metrics --min-score=50')
        ->assertSuccessful()
        ->expectsOutputToContain('Multi-suggestion uplift (accepted with rank data): 0% were NOT rank 1');
});

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
            ['Score Range', 'Tagged Photos', 'Top-1 Hit', 'Top-3 Hit', 'Accepted', 'Rejected', 'Ignored'],
            [
                ['40-59', 3, '33.3%', '66.7%', 1, 1, 1],
                ['All', 3, '33.3%', '66.7%', 1, 1, 1],
            ],
        );
});

it('only counts photos uploaded since the given date', function (): void {
    $this->travelTo('2026-10-12 12:00:00');
    $item = Item::factory()->create();

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create([
            'photo_id' => Photo::factory()->create(['created_at' => '2026-09-01 10:00:00']),
            'is_accepted' => true,
        ]);

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create(['photo_id' => Photo::factory()->create(['created_at' => '2026-10-01 10:00:00'])]);

    $this->artisan('app:suggestion-metrics --since=2026-09-28')
        ->assertSuccessful()
        ->expectsOutputToContain('(since 2026-09-28)')
        ->expectsTable(
            ['Metric', 'Value'],
            [
                ['Total suggestions', 1],
                ['Photos with suggestions', 1],
                ['Accepted', 0],
                ['Rejected', 0],
                ['Pending', 1],
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

    PhotoSuggestion::factory()
        ->withPredictions([$item->id])
        ->create(['item_score' => 35, 'is_accepted' => true]);

    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('30-39');
});

it('handles no suggestions gracefully', function (): void {
    $this->artisan('app:suggestion-metrics')
        ->assertSuccessful()
        ->expectsOutputToContain('Overview');
});
