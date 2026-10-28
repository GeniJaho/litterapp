<?php

namespace App\Console\Commands;

use App\Models\PhotoSuggestion;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SuggestionMetrics extends Command
{
    private const OFFLINE_TOP1_ACCURACY = 68.7;

    private const OFFLINE_TOP3_ACCURACY = 86.2;

    protected $signature = 'app:suggestion-metrics
        {--min-score=30 : Only count multi-item suggestions with item_score >= this value}
        {--since= : Only count photos uploaded on or after this date (e.g. 2026-09-28)}';

    protected $description = 'Display kNN photo suggestion accuracy metrics';

    public function handle(): int
    {
        $minScore = (int) $this->option('min-score');
        $sinceOption = $this->option('since');
        $since = null;

        if (is_string($sinceOption) && $sinceOption !== '') {
            try {
                $since = Carbon::parse($sinceOption);
            } catch (InvalidFormatException) {
                $this->components->error("Invalid --since date: {$sinceOption}");

                return self::FAILURE;
            }
        }

        $healthSince = $since ?? $this->firstSuggestedUploadAt();

        if ($healthSince instanceof Carbon) {
            $this->displayServiceHealth($minScore, $healthSince);
            $this->displayFailedJobs($healthSince);
        }

        $this->displayModelAccuracy($minScore, $since);

        return 0;
    }

    /**
     * Suggestions have no timestamps; they are created right after upload, so the photo's upload time stands in for them.
     */
    private function baseSuggestionsQuery(int $minScore, ?Carbon $since): Builder
    {
        return DB::table('photo_suggestions')
            ->whereNotNull('predictions')
            ->when($minScore > 0, fn (Builder $query): Builder => $query->where('item_score', '>=', $minScore))
            ->when($since, fn (Builder $query, Carbon $since): Builder => $query->whereIn(
                'photo_id',
                fn (Builder $query): Builder => $query->select('id')->from('photos')->where('created_at', '>=', $since),
            ));
    }

    private function windowLabel(int $minScore, ?Carbon $since): string
    {
        return ($minScore > 0 ? " (min score: {$minScore})" : '')
            .($since instanceof Carbon ? " (since {$since->toDateString()})" : '');
    }

    private function firstSuggestedUploadAt(): ?Carbon
    {
        /** @var ?string $firstUploadAt */
        $firstUploadAt = DB::table('photos')
            ->whereIn('id', fn (Builder $query): Builder => $query->select('photo_id')->from('photo_suggestions')->whereNotNull('predictions'))
            ->min('created_at');

        return $firstUploadAt !== null ? Carbon::parse($firstUploadAt) : null;
    }

    private function displayServiceHealth(int $minScore, Carbon $since): void
    {
        $uploadsQuery = DB::table('photos')
            ->where('created_at', '>=', $since)
            ->whereNotIn('user_id', fn (Builder $query): Builder => $query->select('id')
                ->from('users')
                ->where('settings->litterbot_enabled', false));

        $uploaded = (clone $uploadsQuery)->count();

        if ($uploaded === 0) {
            $this->components->warn("No uploads from users with LitterBot enabled since {$since->toDateString()}.");

            return;
        }

        $withSuggestion = (clone $uploadsQuery)
            ->whereExists(fn (Builder $query): Builder => $query->select(DB::raw(1))
                ->from('photo_suggestions')
                ->whereColumn('photo_suggestions.photo_id', 'photos.id')
                ->whereNotNull('predictions'))
            ->count();

        $shownToUser = (clone $uploadsQuery)
            ->whereExists(fn (Builder $query): Builder => $query->select(DB::raw(1))
                ->from('photo_suggestions')
                ->whereColumn('photo_suggestions.photo_id', 'photos.id')
                ->whereNotNull('predictions')
                ->where('item_score', '>=', $minScore))
            ->count();

        $failedJobs = DB::table('failed_jobs')
            ->whereLike('payload', '%SuggestPhotoItem%')
            ->where('failed_at', '>=', $since)
            ->count();

        $queuedJobs = DB::table('jobs')
            ->whereLike('payload', '%SuggestPhotoItem%')
            ->count();

        $coverageRate = round(100 * $withSuggestion / $uploaded, 1);
        $shownRate = round(100 * $shownToUser / $uploaded, 1);

        $this->components->info("Service Health (uploads since {$since->toDateString()}, users with LitterBot enabled)");

        $this->table(
            ['Metric', 'Value', 'Target'],
            [
                ['Uploaded photos', (string) $uploaded, '-'],
                ['Got a suggestion', "{$withSuggestion} ({$coverageRate}%)", '~100%'],
                ["Shown to user (score >= {$minScore})", "{$shownToUser} ({$shownRate}%)", '-'],
                ['Failed suggestion jobs', (string) $failedJobs, '0'],
                ['Queued or retrying now', (string) $queuedJobs, '-'],
            ],
        );
    }

    private function displayFailedJobs(Carbon $since): void
    {
        $failures = DB::table('failed_jobs')
            ->whereLike('payload', '%SuggestPhotoItem%')
            ->where('failed_at', '>=', $since)
            ->oldest('failed_at')
            ->get([DB::raw('substr(exception, 1, 300) as error'), 'failed_at']);

        if ($failures->isEmpty()) {
            return;
        }

        /** @var array<string, array{count: int, last_failed_at: string}> $summary */
        $summary = [];

        foreach ($failures as $failure) {
            /** @var object{error: string, failed_at: string} $failure */
            $error = $this->summarizeError($failure->error);

            $summary[$error] = [
                'count' => ($summary[$error]['count'] ?? 0) + 1,
                'last_failed_at' => $failure->failed_at,
            ];
        }

        uasort($summary, fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        $this->components->info('Failed Suggestion Jobs by Error');

        $this->table(
            ['Error', 'Count', 'Last failed'],
            array_map(
                fn (string $error, array $row): array => [$error, $row['count'], $row['last_failed_at']],
                array_keys($summary),
                $summary,
            ),
        );
    }

    /**
     * First line of the exception with numbers masked, so failures differing only by photo ID, IP or timing group together.
     */
    private function summarizeError(string $exception): string
    {
        return Str::of($exception)
            ->before("\n")
            ->replaceMatches('/\d+/', 'N')
            ->limit(100)
            ->value();
    }

    /**
     * Compares predictions against the items users actually tagged, independent of how the suggestion was (or wasn't) used.
     */
    private function displayModelAccuracy(int $minScore, ?Carbon $since): void
    {
        $outcomes = PhotoSuggestion::query()
            ->select(['id', 'photo_id', 'item_id', 'item_score', 'predictions'])
            ->whereIn('id', $this->baseSuggestionsQuery($minScore, $since)
                ->whereExists(fn (Builder $query): Builder => $query->select(DB::raw(1))
                    ->from('photo_items')
                    ->whereColumn('photo_items.photo_id', 'photo_suggestions.photo_id'))
                ->select('id'))
            ->with(['photo:id', 'photo.photoItems:id,photo_id,item_id'])
            ->lazyById(500)
            ->map(function (PhotoSuggestion $suggestion) use ($minScore): array {
                /** @var array<int, int> $taggedItemIds */
                $taggedItemIds = $suggestion->photo->photoItems->pluck('item_id')->all();
                $rank = $suggestion->predictedItemRank($taggedItemIds);

                return [
                    'bucket' => $this->scoreBucket($suggestion->item_score, $minScore),
                    'top1' => $rank === 1,
                    'top3' => $rank !== null,
                ];
            })
            ->collect();

        if ($outcomes->isEmpty()) {
            $this->components->warn('No tagged photos with multi-item suggestions yet — model accuracy unavailable.');

            return;
        }

        $groups = collect(['80-100', '60-79', '40-59', $this->lowestScoreBucket($minScore)])
            ->mapWithKeys(fn (string $bucket): array => [$bucket => $outcomes->where('bucket', $bucket)])
            ->put('All', $outcomes)
            ->filter(fn (Collection $group): bool => $group->isNotEmpty());

        $this->components->info('Model Accuracy vs Final Tags (tagged photos only)'.$this->windowLabel($minScore, $since));

        $this->table(
            ['Score Range', 'Tagged Photos', 'Top-1 Hit', 'Top-3 Hit'],
            $groups->map(fn (Collection $group, string $bucket): array => [
                $bucket,
                $group->count(),
                round(100 * $group->whereStrict('top1', true)->count() / $group->count(), 1).'%',
                round(100 * $group->whereStrict('top3', true)->count() / $group->count(), 1).'%',
            ])->values()->all(),
        );

        $this->components->info(sprintf(
            'Offline baseline: top-1 %s%%, top-3 %s%%.',
            self::OFFLINE_TOP1_ACCURACY,
            self::OFFLINE_TOP3_ACCURACY,
        ));
    }

    private function scoreBucket(int $score, int $minScore): string
    {
        return match (true) {
            $score >= 80 => '80-100',
            $score >= 60 => '60-79',
            $score >= 40 => '40-59',
            default => $this->lowestScoreBucket($minScore),
        };
    }

    private function lowestScoreBucket(int $minScore): string
    {
        return $minScore > 0 && $minScore < 40 ? "{$minScore}-39" : '0-39';
    }
}
