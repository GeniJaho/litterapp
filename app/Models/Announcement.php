<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AnnouncementFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property ?string $link_url
 * @property ?string $link_label
 * @property ?string $image_path
 * @property ?Carbon $published_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Announcement extends Model
{
    /** @use HasFactory<AnnouncementFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * @param  Builder<Announcement>  $query
     */
    protected function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
