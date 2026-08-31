<?php

namespace App\Enums;

use App\Models\User;
use Illuminate\Support\Carbon;

enum DashboardPeriod
{
    case AllTime;
    case Last30Days;
    case ThisYear;

    public static function fromKey(?string $key): self
    {
        return match ($key) {
            'last_30_days' => self::Last30Days,
            'this_year' => self::ThisYear,
            default => self::AllTime,
        };
    }

    /**
     * @return list<array{key: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $period): array => ['key' => $period->key(), 'label' => $period->label()],
            self::cases(),
        );
    }

    public function key(): string
    {
        return match ($this) {
            self::AllTime => 'all',
            self::Last30Days => 'last_30_days',
            self::ThisYear => 'this_year',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::AllTime => 'All time',
            self::Last30Days => 'Last 30 days',
            self::ThisYear => 'This year',
        };
    }

    public function start(): ?Carbon
    {
        return match ($this) {
            self::AllTime => null,
            self::Last30Days => now()->subDays(29)->startOfDay(),
            self::ThisYear => now()->startOfYear(),
        };
    }

    public function granularity(): string
    {
        return $this === self::Last30Days ? 'day' : 'month';
    }

    public function cacheKey(User $user): string
    {
        return "user-stats:{$user->id}:{$this->key()}";
    }
}
