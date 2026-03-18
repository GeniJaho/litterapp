<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

class SuggestionMetrics extends Command
{
    protected $signature = 'app:suggestion-metrics {--min-score=0 : Only count suggestions with item_score >= this value}';

    protected $description = 'Display kNN photo suggestion accuracy metrics';

    public function handle(): int
    {
        $minScore = (int) $this->option('min-score');

        $this->displayOverview($minScore);
        $this->displayItemAcceptance($minScore);
        $this->displayRankDistribution();
        $this->displayBrandAcceptance($minScore);
        $this->displayContentAcceptance($minScore);
        $this->displayAcceptanceByScoreBucket();

        return 0;
    }

    private function displayOverview(int $minScore): void
    {
        $stats = DB::query()
            ->selectRaw('COUNT(*) as total_suggestions')
            ->selectRaw('COUNT(DISTINCT photo_id) as photos_with_suggestions')
            ->selectRaw('SUM(is_accepted = 1) as accepted')
            ->selectRaw('SUM(is_accepted = 0) as rejected')
            ->selectRaw('SUM(is_accepted IS NULL) as pending')
            ->from('photo_suggestions')
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->first();

        $totalTaggedPhotos = DB::table('photo_items')->distinct('photo_id')->count('photo_id');
        $noSuggestionCount = $totalTaggedPhotos > 0
            ? $totalTaggedPhotos - (int) DB::table('photo_suggestions')
                ->join('photo_items', 'photo_suggestions.photo_id', '=', 'photo_items.photo_id')
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

        $this->components->info('Overview'.($minScore > 0 ? " (min score: {$minScore})" : ''));

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
        $reviewed = DB::table('photo_suggestions')
            ->whereNotNull('is_accepted')
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        if ($reviewed === 0) {
            $this->components->warn('No reviewed suggestions yet — item acceptance rate unavailable.');

            return;
        }

        $accepted = DB::table('photo_suggestions')
            ->where('is_accepted', true)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        // Also check: among rejected suggestions, did the user still tag the same item?
        $rejectedButItemTagged = DB::table('photo_suggestions')
            ->where('is_accepted', false)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->whereExists(fn (Builder $q) => $q->select(DB::raw(1))
                ->from('photo_items')
                ->whereColumn('photo_items.photo_id', 'photo_suggestions.photo_id')
                ->whereColumn('photo_items.item_id', 'photo_suggestions.item_id'))
            ->count();

        $acceptanceRate = round(100 * $accepted / $reviewed, 1);
        $effectiveRate = round(100 * ($accepted + $rejectedButItemTagged) / $reviewed, 1);

        $this->components->info('Item Suggestion Acceptance');

        $this->table(
            ['Metric', 'Value', 'Target'],
            [
                ['Acceptance rate', "{$accepted} / {$reviewed} ({$acceptanceRate}%)", '> 60%'],
                ['Effective accuracy (incl. rejected but item matched)', "{$effectiveRate}%", '-'],
                ['Rejected but same item tagged', (string) $rejectedButItemTagged, '-'],
            ],
        );
    }

    private function displayRankDistribution(): void
    {
        $ranks = DB::query()
            ->selectRaw('accepted_item_rank as `rank`')
            ->selectRaw('COUNT(*) as total')
            ->from('photo_suggestions')
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

        $this->components->info('Accepted Item Rank Distribution');

        $this->table(
            ['Rank', 'Count', '%'],
            $ranks->map(fn (stdClass $row): array => [
                $row->rank,
                $row->total,
                round(100 * $row->total / $totalRanked, 1).'%',
            ])->all(),
        );

        $this->components->info("Multi-suggestion uplift: {$uplift}% of accepted were NOT rank 1");
    }

    private function displayBrandAcceptance(int $minScore): void
    {
        $reviewedWithBrand = DB::table('photo_suggestions')
            ->whereNotNull('is_accepted')
            ->whereNotNull('brand_tag_id')
            ->where('brand_score', '>=', 50)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        if ($reviewedWithBrand === 0) {
            $this->components->warn('No reviewed suggestions with brand tags — brand acceptance rate unavailable.');

            return;
        }

        // Brand is accepted when suggestion is accepted AND brand_score >= 50 (auto-applied)
        $brandApplied = DB::table('photo_suggestions')
            ->where('is_accepted', true)
            ->whereNotNull('brand_tag_id')
            ->where('brand_score', '>=', 50)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->whereExists(fn (Builder $q) => $q->select(DB::raw(1))
                ->from('photo_items')
                ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                ->whereColumn('photo_items.photo_id', 'photo_suggestions.photo_id')
                ->whereColumn('photo_items.item_id', 'photo_suggestions.item_id')
                ->whereColumn('photo_item_tag.tag_id', 'photo_suggestions.brand_tag_id'))
            ->count();

        $brandRate = round(100 * $brandApplied / $reviewedWithBrand, 1);

        // New-style: count suggestions where brand_accepted = true (multi-item predictions)
        $newStyleBrandAccepted = DB::table('photo_suggestions')
            ->where('is_accepted', true)
            ->whereNotNull('predictions')
            ->where('brand_accepted', true)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        $newStyleBrandTotal = DB::table('photo_suggestions')
            ->where('is_accepted', true)
            ->whereNotNull('predictions')
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        $this->components->info('Brand Suggestion Acceptance (score >= 50)');

        $rows = [
            ['Brand applied with accepted suggestion (legacy)', "{$brandApplied} / {$reviewedWithBrand} ({$brandRate}%)", '> 40%'],
        ];

        if ($newStyleBrandTotal > 0) {
            $newBrandRate = round(100 * $newStyleBrandAccepted / $newStyleBrandTotal, 1);
            $rows[] = ['Brand accepted (multi-item)', "{$newStyleBrandAccepted} / {$newStyleBrandTotal} ({$newBrandRate}%)", '> 40%'];
        }

        $this->table(
            ['Metric', 'Value', 'Target'],
            $rows,
        );
    }

    private function displayContentAcceptance(int $minScore): void
    {
        $reviewedWithContent = DB::table('photo_suggestions')
            ->whereNotNull('is_accepted')
            ->whereNotNull('content_tag_id')
            ->where('content_score', '>=', 50)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        if ($reviewedWithContent === 0) {
            $this->components->warn('No reviewed suggestions with content tags — content acceptance rate unavailable.');

            return;
        }

        $contentApplied = DB::table('photo_suggestions')
            ->where('is_accepted', true)
            ->whereNotNull('content_tag_id')
            ->where('content_score', '>=', 50)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->whereExists(fn (Builder $q) => $q->select(DB::raw(1))
                ->from('photo_items')
                ->join('photo_item_tag', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
                ->whereColumn('photo_items.photo_id', 'photo_suggestions.photo_id')
                ->whereColumn('photo_items.item_id', 'photo_suggestions.item_id')
                ->whereColumn('photo_item_tag.tag_id', 'photo_suggestions.content_tag_id'))
            ->count();

        $contentRate = round(100 * $contentApplied / $reviewedWithContent, 1);

        // New-style: count suggestions where content_accepted = true (multi-item predictions)
        $newStyleContentAccepted = DB::table('photo_suggestions')
            ->where('is_accepted', true)
            ->whereNotNull('predictions')
            ->where('content_accepted', true)
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        $newStyleContentTotal = DB::table('photo_suggestions')
            ->where('is_accepted', true)
            ->whereNotNull('predictions')
            ->when($minScore > 0, fn (Builder $q) => $q->where('item_score', '>=', $minScore))
            ->count();

        $this->components->info('Content Suggestion Acceptance (score >= 50)');

        $rows = [
            ['Content applied with accepted suggestion (legacy)', "{$contentApplied} / {$reviewedWithContent} ({$contentRate}%)", '> 40%'],
        ];

        if ($newStyleContentTotal > 0) {
            $newContentRate = round(100 * $newStyleContentAccepted / $newStyleContentTotal, 1);
            $rows[] = ['Content accepted (multi-item)', "{$newStyleContentAccepted} / {$newStyleContentTotal} ({$newContentRate}%)", '> 40%'];
        }

        $this->table(
            ['Metric', 'Value', 'Target'],
            $rows,
        );
    }

    private function displayAcceptanceByScoreBucket(): void
    {
        $buckets = DB::query()
            ->selectRaw("CASE
                WHEN item_score >= 80 THEN '80-100'
                WHEN item_score >= 60 THEN '60-79'
                WHEN item_score >= 40 THEN '40-59'
                ELSE '0-39'
            END as score_bucket")
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(is_accepted = 1) as accepted')
            ->selectRaw('SUM(is_accepted = 0) as rejected')
            ->selectRaw('SUM(is_accepted IS NULL) as pending')
            ->from('photo_suggestions')
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

        $this->components->info('Item Acceptance by Score Bucket (reviewed only)');

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
