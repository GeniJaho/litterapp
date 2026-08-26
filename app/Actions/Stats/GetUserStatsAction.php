<?php

namespace App\Actions\Stats;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetUserStatsAction
{
    /**
     * @var array<string, string>
     */
    private const PERIODS = [
        'all' => 'All time',
        'last_30_days' => 'Last 30 days',
        'this_year' => 'This year',
    ];

    private const TOP_ITEMS_LIMIT = 8;

    private const MAX_MONTH_BUCKETS = 120;

    /**
     * Photos are attributed to a period by when they were taken (taken_at_local).
     * "All time" totals include photos without a taken date; the windowed periods
     * and the time-series charts only cover photos that have one.
     *
     * @return array{
     *     totals: array{photos: int, items: int, tags: int},
     *     needs_tagging: int,
     *     charts: array{
     *         activity: array{labels: list<string>, photos: list<int>, items: list<int>},
     *         top_items: array{labels: list<string>, quantities: list<int>},
     *         disposal: array{picked_up: int, recycled: int, deposit: int}
     *     }
     * }
     */
    public function run(User $user, string $period): array
    {
        $period = $this->resolvePeriod($period);
        $from = $this->periodStart($period);

        $itemTotals = $this->itemTotals($user, $from);

        return [
            'totals' => [
                'photos' => $this->photoTotal($user, $from),
                'items' => $itemTotals['items'],
                'tags' => $this->tagTotal($user, $from),
            ],
            'needs_tagging' => Photo::query()
                ->where('user_id', $user->id)
                ->whereDoesntHave('photoItems')
                ->count(),
            'charts' => [
                'activity' => $this->activity($user, $from, $this->granularity($period)),
                'top_items' => $this->topItems($user, $from),
                'disposal' => [
                    'picked_up' => $itemTotals['picked_up'],
                    'recycled' => $itemTotals['recycled'],
                    'deposit' => $itemTotals['deposit'],
                ],
            ],
        ];
    }

    public function resolvePeriod(?string $period): string
    {
        return array_key_exists((string) $period, self::PERIODS) ? (string) $period : 'all';
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public function availablePeriods(): array
    {
        $periods = [];

        foreach (self::PERIODS as $key => $label) {
            $periods[] = ['key' => $key, 'label' => $label];
        }

        return $periods;
    }

    private function periodStart(string $period): ?Carbon
    {
        return match ($period) {
            'last_30_days' => now()->subDays(29)->startOfDay(),
            'this_year' => now()->startOfYear(),
            default => null,
        };
    }

    private function granularity(string $period): string
    {
        return $period === 'last_30_days' ? 'day' : 'month';
    }

    private function photoTotal(User $user, ?Carbon $from): int
    {
        return DB::table('photos')
            ->where('user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('taken_at_local', '>=', $from))
            ->count();
    }

    private function tagTotal(User $user, ?Carbon $from): int
    {
        return DB::table('photo_item_tag')
            ->join('photo_items', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->where('photos.user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('photos.taken_at_local', '>=', $from))
            ->count();
    }

    /**
     * Every quantity aggregate over photo_items comes from this one scan. The
     * disposal columns are booleans, so they are weighted by quantity to stay in
     * the same unit as the items total.
     *
     * @return array{items: int, picked_up: int, recycled: int, deposit: int}
     */
    private function itemTotals(User $user, ?Carbon $from): array
    {
        $row = DB::table('photo_items')
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->where('photos.user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('photos.taken_at_local', '>=', $from))
            ->selectRaw(implode(', ', [
                'coalesce(sum(photo_items.quantity), 0) as items',
                'coalesce(sum(case when photo_items.picked_up = 1 then photo_items.quantity else 0 end), 0) as picked_up',
                'coalesce(sum(case when photo_items.recycled = 1 then photo_items.quantity else 0 end), 0) as recycled',
                'coalesce(sum(case when photo_items.deposit = 1 then photo_items.quantity else 0 end), 0) as deposit',
            ]))
            ->first();

        return [
            'items' => $this->toInt($row->items ?? 0),
            'picked_up' => $this->toInt($row->picked_up ?? 0),
            'recycled' => $this->toInt($row->recycled ?? 0),
            'deposit' => $this->toInt($row->deposit ?? 0),
        ];
    }

    /**
     * @return array{labels: list<string>, photos: list<int>, items: list<int>}
     */
    private function activity(User $user, ?Carbon $from, string $granularity): array
    {
        $bucket = $this->bucketExpression('photos.taken_at_local', $granularity);

        $photoCounts = DB::table('photos')
            ->where('user_id', $user->id)
            ->whereNotNull('taken_at_local')
            ->when($from, fn (Builder $query): Builder => $query->where('taken_at_local', '>=', $from))
            ->selectRaw("{$bucket} as bucket, count(*) as aggregate")
            ->groupBy(DB::raw($bucket))
            ->pluck('aggregate', 'bucket');

        $itemCounts = DB::table('photo_items')
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->where('photos.user_id', $user->id)
            ->whereNotNull('photos.taken_at_local')
            ->when($from, fn (Builder $query): Builder => $query->where('photos.taken_at_local', '>=', $from))
            ->selectRaw("{$bucket} as bucket, coalesce(sum(photo_items.quantity), 0) as aggregate")
            ->groupBy(DB::raw($bucket))
            ->pluck('aggregate', 'bucket');

        /** @var list<string> $filledBuckets */
        $filledBuckets = $photoCounts->keys()->map(fn (int|string $key): string => (string) $key)->all();

        $labels = [];
        $photos = [];
        $items = [];

        foreach ($this->buckets($from, $granularity, $filledBuckets) as $entry) {
            $labels[] = $entry['label'];
            $photos[] = $this->toInt($photoCounts[$entry['key']] ?? 0);
            $items[] = $this->toInt($itemCounts[$entry['key']] ?? 0);
        }

        return ['labels' => $labels, 'photos' => $photos, 'items' => $items];
    }

    /**
     * @return array{labels: list<string>, quantities: list<int>}
     */
    private function topItems(User $user, ?Carbon $from): array
    {
        $rows = DB::table('photo_items')
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->join('items', 'items.id', '=', 'photo_items.item_id')
            ->where('photos.user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('photos.taken_at_local', '>=', $from))
            ->selectRaw('items.name as name, coalesce(sum(photo_items.quantity), 0) as quantity')
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('quantity')
            ->limit(self::TOP_ITEMS_LIMIT)
            ->get();

        $labels = [];
        $quantities = [];

        foreach ($rows as $row) {
            $labels[] = (string) $row->name;
            $quantities[] = $this->toInt($row->quantity);
        }

        return ['labels' => $labels, 'quantities' => $quantities];
    }

    /**
     * Ordered, gap-filled buckets for the period so the time series is continuous.
     *
     * @param  list<string>  $filledBuckets  bucket keys that actually hold photos
     * @return list<array{key: string, label: string}>
     */
    private function buckets(?Carbon $from, string $granularity, array $filledBuckets): array
    {
        if ($granularity === 'day') {
            $cursor = ($from instanceof Carbon ? $from->copy() : now()->subDays(29))->startOfDay();
            $end = today();
            $buckets = [];

            while ($cursor->lte($end)) {
                $buckets[] = ['key' => $cursor->format('Y-m-d'), 'label' => $cursor->format('M j')];
                $cursor->addDay();
            }

            return $buckets;
        }

        if ($from instanceof Carbon) {
            return $this->monthRange($from->copy()->startOfMonth(), now()->startOfMonth(), 'M');
        }

        return $this->monthBuckets($filledBuckets);
    }
/**
     * @param  list<string>  $months  bucket keys in Y-m form
     * @return list<array{key: string, label: string}>
     */
    private function monthBuckets(array $months): array
    {
        $currentMonth = $this->monthIndex(now()->format('Y-m'));

        $withPhotos = array_filter(
            array_map($this->monthIndex(...), $months),
            fn (int $month): bool => $month <= $currentMonth,
        );

        if ($withPhotos === []) {
            return [];
        }

        $withinSpan = array_filter(
            $withPhotos,
            fn (int $month): bool => $month > $currentMonth - self::MAX_MONTH_BUCKETS,
        );

        $start = $withinSpan === [] ? max($withPhotos) : min($withinSpan);
        $end = min($currentMonth, max($withPhotos));

        return $this->monthRange($this->monthFromIndex($start), $this->monthFromIndex($end), 'M Y');
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private function monthRange(Carbon $start, Carbon $end, string $labelFormat): array
    {
        $cursor = $start->copy()->startOfMonth();
        $last = $end->copy()->startOfMonth();
        $buckets = [];

        while ($cursor->lte($last)) {
            $buckets[] = ['key' => $cursor->format('Y-m'), 'label' => $cursor->format($labelFormat)];
            $cursor->addMonth();
        }

        return $buckets;
    }

    private function monthIndex(string $key): int
    {
        $parts = explode('-', $key);
        $month = count($parts) > 1 ? (int) $parts[1] : 1;

        return (int) $parts[0] * 12 + $month - 1;
    }

    private function monthFromIndex(int $index): Carbon
    {
        return now()->startOfMonth()->setDate(intdiv($index, 12), $index % 12 + 1, 1);
    }

    private function bucketExpression(string $column, string $granularity): string
    {
        $format = $granularity === 'day' ? '%Y-%m-%d' : '%Y-%m';

        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('{$format}', {$column})"
            : "DATE_FORMAT({$column}, '{$format}')";
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
