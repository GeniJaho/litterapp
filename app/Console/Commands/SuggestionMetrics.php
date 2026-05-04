<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class SuggestionMetrics extends Command
{
    protected $signature = 'app:suggestion-metrics {--min-score=30 : Only count multi-item suggestions with item_score >= this value}';

    protected $description = 'Display kNN photo suggestion accuracy metrics';

    public function handle(): int
    {
        $minScore = (int) $this->option('min-score');

        $this->displayOverview($minScore);
        $this->displayItemAcceptance($minScore);
        $this->displayRankDistribution($minScore);
        $this->displayBrandAcceptance($minScore);
        $this->displayContentAcceptance($minScore);
        $this->displayAcceptanceByScoreBucket($minScore);

        return 0;
    }

    private function baseSuggestionsQuery(int $minScore): Builder
    {
        return DB::table('photo_suggestions')
            ->whereNotNull('predictions')
            ->when($minScore > 0, fn (Builder $query): Builder => $query->where('item_score', '>=', $minScore));
    }

    private function displayOverview(int $minScore): void
    {
        $stats = $this->baseSuggestionsQuery($minScore)
            ->selectRaw('COUNT(*) as total_suggestions')
            ->selectRaw('COUNT(DISTINCT photo_id) as photos_with_suggestions')
            ->selectRaw('SUM(is_accepted = 1) as accepted')
            ->selectRaw('SUM(is_accepted = 0) as rejected')
            ->selectRaw('SUM(is_accepted IS NULL) as pending')
            ->first();

        $totalTaggedPhotos = DB::table('photo_items')->distinct('photo_id')->count('photo_id');
        $noSuggestionCount = $totalTaggedPhotos > 0
            ? $totalTaggedPhotos - (int) DB::table('photo_suggestions')
                ->join('photo_items', 'photo_suggestions.photo_id', '=', 'photo_items.photo_id')
                ->whereNotNull('photo_suggestions.predictions')
                ->when($minScore > 0, fn (Builder $q) => $q->where('photo_suggestions.item_score', '>=', $minScore))
                ->distinct()
                ->count('photo_suggestions.photo_id')
            : 0;

        $noSuggestionRate = $totalTaggedPhotos > 0
            ? round(100 * $noSuggestionCount / $totalTaggedPhotos, 1)
            : 0;

        if ($stats === null) {
            return;
        }

        $this->components->info('Overview (multi-item only)'.($minScore > 0 ? " (min score: {$minScore})" : ''));

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total suggestions', $stats->total_suggestions],
                ['Photos with suggestions', $stats->photos_with_suggestions],
                ['Accepted', $stats->accepted],
                ['Rejected', $stats->rejected],
                ['Pending', $stats->pending],
                ['Tagged photos without suggestions', "{$noSuggestionCount} / {$totalTaggedPhotos} ({$noSuggestionRate}%)"],
            ],
        );
    }

    private function displayItemAcceptance(int $minScore): void
    {
        $reviewed = $this->baseSuggestionsQuery($minScore)
            ->whereNotNull('is_accepted')
            ->count();

        if ($reviewed === 0) {
            $this->components->warn('No reviewed multi-item suggestions yet — item acceptance rate unavailable.');

            return;
        }

        $accepted = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', true)
            ->count();

        // Also check: among rejected suggestions, did the user still tag the same item?
        $rejectedButItemTagged = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', false)
            ->whereExists(fn (Builder $q) => $q->select(DB::raw(1))
                ->from('photo_items')
                ->whereColumn('photo_items.photo_id', 'photo_suggestions.photo_id')
                ->whereColumn('photo_items.item_id', 'photo_suggestions.item_id'))
            ->count();

        $acceptanceRate = round(100 * $accepted / $reviewed, 1);
        $effectiveRate = round(100 * ($accepted + $rejectedButItemTagged) / $reviewed, 1);

        $this->components->info('Item Suggestion Acceptance (multi-item)');

        $this->table(
            ['Metric', 'Value', 'Target'],
            [
                ['Acceptance rate', "{$accepted} / {$reviewed} ({$acceptanceRate}%)", '> 60%'],
                ['Effective accuracy (incl. rejected but item matched)', "{$effectiveRate}%", '-'],
                ['Rejected but same item tagged', (string) $rejectedButItemTagged, '-'],
            ],
        );
    }

    private function displayRankDistribution(int $minScore): void
    {
        $ranks = $this->baseSuggestionsQuery($minScore)
            ->selectRaw('accepted_item_rank as `rank`')
            ->selectRaw('COUNT(*) as total')
            ->where('is_accepted', true)
            ->whereNotNull('accepted_item_rank')
            ->groupBy('accepted_item_rank')
            ->orderBy('accepted_item_rank')
            ->get();

        if ($ranks->isEmpty()) {
            return;
        }

        /** @var int $totalRanked */
        $totalRanked = $ranks->sum('total');
        /** @var int $notRankOne */
        $notRankOne = $ranks->where('rank', '>', 1)->sum('total');
        $uplift = $totalRanked > 0 ? round(100 * $notRankOne / $totalRanked, 1) : 0;

        $this->components->info('Accepted Item Rank Distribution (multi-item)');

        $this->table(
            ['Rank', 'Count', '%'],
            $ranks->map(fn (stdClass $row): array => [
                $row->rank,
                $row->total,
                round(100 * $row->total / $totalRanked, 1).'%',
            ])->all(),
        );

        $this->components->info("Multi-suggestion uplift (accepted with rank data): {$uplift}% were NOT rank 1");
    }

    private function displayBrandAcceptance(int $minScore): void
    {
        $acceptedWithBrandPredictions = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', true)
            ->whereRaw("JSON_LENGTH(predictions, '$.brands') > 0")
            ->count();

        $brandReviewed = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', true)
            ->whereRaw("JSON_LENGTH(predictions, '$.brands') > 0")
            ->whereNotNull('brand_accepted')
            ->count();

        if ($brandReviewed === 0) {
            $this->components->warn('No reviewed multi-item brand decisions yet — brand acceptance rate unavailable.');

            return;
        }

        $brandAccepted = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', true)
            ->whereRaw("JSON_LENGTH(predictions, '$.brands') > 0")
            ->where('brand_accepted', true)
            ->count();

        $brandMissingDecision = $acceptedWithBrandPredictions - $brandReviewed;
        $brandRate = round(100 * $brandAccepted / $brandReviewed, 1);

        $this->components->info('Brand Suggestion Acceptance (multi-item)');

        $this->table(
            ['Metric', 'Value', 'Target'],
            [
                ['Brand accepted', "{$brandAccepted} / {$brandReviewed} ({$brandRate}%)", '> 40%'],
                ['Accepted with brand predictions but missing decision', (string) $brandMissingDecision, '-'],
            ],
        );
    }

    private function displayContentAcceptance(int $minScore): void
    {
        $acceptedWithContentPredictions = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', true)
            ->whereRaw("JSON_LENGTH(predictions, '$.content') > 0")
            ->count();

        $contentReviewed = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', true)
            ->whereRaw("JSON_LENGTH(predictions, '$.content') > 0")
            ->whereNotNull('content_accepted')
            ->count();

        if ($contentReviewed === 0) {
            $this->components->warn('No reviewed multi-item content decisions yet — content acceptance rate unavailable.');

            return;
        }

        $contentAccepted = $this->baseSuggestionsQuery($minScore)
            ->where('is_accepted', true)
            ->whereRaw("JSON_LENGTH(predictions, '$.content') > 0")
            ->where('content_accepted', true)
            ->count();

        $contentMissingDecision = $acceptedWithContentPredictions - $contentReviewed;
        $contentRate = round(100 * $contentAccepted / $contentReviewed, 1);

        $this->components->info('Content Suggestion Acceptance (multi-item)');

        $this->table(
            ['Metric', 'Value', 'Target'],
            [
                ['Content accepted', "{$contentAccepted} / {$contentReviewed} ({$contentRate}%)", '> 40%'],
                ['Accepted with content predictions but missing decision', (string) $contentMissingDecision, '-'],
            ],
        );
    }

    private function displayAcceptanceByScoreBucket(int $minScore): void
    {
        $buckets = $this->baseSuggestionsQuery($minScore)
            ->selectRaw("CASE
                WHEN item_score >= 80 THEN '80-100'
                WHEN item_score >= 60 THEN '60-79'
                WHEN item_score >= 40 THEN '40-59'
                ELSE '0-39'
            END as score_bucket")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(is_accepted = 1) as accepted')
            ->selectRaw('SUM(is_accepted = 0) as rejected')
            ->whereNotNull('is_accepted')
            ->groupByRaw("CASE
                WHEN item_score >= 80 THEN '80-100'
                WHEN item_score >= 60 THEN '60-79'
                WHEN item_score >= 40 THEN '40-59'
                ELSE '0-39'
            END")
            ->orderByDesc('score_bucket')
            ->get();

        if ($buckets->isEmpty()) {
            return;
        }

        $this->components->info('Item Acceptance by Score Bucket (multi-item, reviewed only)');

        $this->table(
            ['Score Range', 'Reviewed', 'Accepted', 'Rejected', 'Acceptance Rate'],
            $buckets->map(fn (stdClass $row): array => [
                $row->score_bucket,
                $row->total,
                $row->accepted,
                $row->rejected,
                $row->total > 0 ? round(100 * $row->accepted / $row->total, 1).'%' : '-',
            ])->all(),
        );
    }
}
