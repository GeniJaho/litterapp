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

    /**
     * Photos are attributed to a period by when they were taken (taken_at_local).
     * "All time" totals include photos without a taken date; the windowed periods
     * and the time-series charts only cover photos that have one.
     *
     * @return array{
     *     period: string,
     *     available_periods: list<array{key: string, label: string}>,
     *     totals: array{photos: int, items: int, tags: int},
     *     needs_tagging: int,
     *     charts: array{
     *         activity: array{labels: list<string>, photos: list<int>, items: list<int>},
     *         top_items: array{labels: list<string>, quantities: list<int>},
     *         disposal: array{picked_up: int, recycled: int, deposit: int}
     *     }
     * }
     */
    public function run(User $user, ?string $period = null): array
    {
        $period = array_key_exists((string) $period, self::PERIODS) ? (string) $period : 'all';
        $from = $this->periodStart($period);

        return [
            'period' => $period,
            'available_periods' => $this->availablePeriods(),
            'totals' => $this->totals($user, $from),
            'needs_tagging' => Photo::query()
                ->where('user_id', $user->id)
                ->whereDoesntHave('items')
                ->count(),
            'charts' => [
                'activity' => $this->activity($user, $from, $this->granularity($period)),
                'top_items' => $this->topItems($user, $from),
                'disposal' => $this->disposal($user, $from),
            ],
        ];
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    private function availablePeriods(): array
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
        return match ($period) {
            'last_30_days' => 'day',
            'this_year' => 'month',
            default => 'year',
        };
    }

    /**
     * @return array{photos: int, items: int, tags: int}
     */
    private function totals(User $user, ?Carbon $from): array
    {
        $photos = DB::table('photos')
            ->where('user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('taken_at_local', '>=', $from))
            ->count();

        $items = DB::table('photo_items')
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->where('photos.user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('photos.taken_at_local', '>=', $from))
            ->sum('photo_items.quantity');

        $tags = DB::table('photo_item_tag')
            ->join('photo_items', 'photo_items.id', '=', 'photo_item_tag.photo_item_id')
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->where('photos.user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('photos.taken_at_local', '>=', $from))
            ->count();

        return [
            'photos' => $photos,
            'items' => $this->toInt($items),
            'tags' => $tags,
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

        $labels = [];
        $photos = [];
        $items = [];

        foreach ($this->buckets($user, $from, $granularity) as $entry) {
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
     * @return array{picked_up: int, recycled: int, deposit: int}
     */
    private function disposal(User $user, ?Carbon $from): array
    {
        $row = DB::table('photo_items')
            ->join('photos', 'photos.id', '=', 'photo_items.photo_id')
            ->where('photos.user_id', $user->id)
            ->when($from, fn (Builder $query): Builder => $query->where('photos.taken_at_local', '>=', $from))
            ->selectRaw('coalesce(sum(photo_items.picked_up), 0) as picked_up, coalesce(sum(photo_items.recycled), 0) as recycled, coalesce(sum(photo_items.deposit), 0) as deposit')
            ->first();

        return [
            'picked_up' => $this->toInt($row->picked_up ?? 0),
            'recycled' => $this->toInt($row->recycled ?? 0),
            'deposit' => $this->toInt($row->deposit ?? 0),
        ];
    }

    /**
     * Ordered, gap-filled buckets for the period so the time series is continuous.
     *
     * @return list<array{key: string, label: string}>
     */
    private function buckets(User $user, ?Carbon $from, string $granularity): array
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

        if ($granularity === 'month') {
            $cursor = ($from instanceof Carbon ? $from->copy() : now()->startOfYear())->startOfMonth();
            $end = now()->startOfMonth();
            $buckets = [];

            while ($cursor->lte($end)) {
                $buckets[] = ['key' => $cursor->format('Y-m'), 'label' => $cursor->format('M')];
                $cursor->addMonth();
            }

            return $buckets;
        }

        /** @var ?string $earliest */
        $earliest = DB::table('photos')
            ->where('user_id', $user->id)
            ->whereNotNull('taken_at_local')
            ->min('taken_at_local');

        if ($earliest === null) {
            return [];
        }

        $buckets = [];

        for ($year = Carbon::parse($earliest)->year; $year <= now()->year; $year++) {
            $buckets[] = ['key' => (string) $year, 'label' => (string) $year];
        }

        return $buckets;
    }

    /**
     * Cross-database date bucketing: MySQL in production, SQLite under test.
     */
    private function bucketExpression(string $column, string $granularity): string
    {
        $format = match ($granularity) {
            'day' => '%Y-%m-%d',
            'month' => '%Y-%m',
            default => '%Y',
        };

        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('{$format}', {$column})"
            : "DATE_FORMAT({$column}, '{$format}')";
    }

    private function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }
}
