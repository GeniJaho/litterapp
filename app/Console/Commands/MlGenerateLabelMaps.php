<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagType;
use App\Traits\Training\ExcludesNonVisualItems;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MlGenerateLabelMaps extends Command
{
    use ExcludesNonVisualItems;

    protected $signature = 'ml:generate-label-maps';

    protected $description = 'Generate label_maps.json (item/brand/content ID-to-name mappings) for the kNN inference API';

    public function handle(): int
    {
        $this->components->info('Generating label maps...');

        $excludedItemIds = $this->getExcludedItemIds();
        $excludedTagIds = $this->getExcludedTagIds();
        $brandTypeId = TagType::query()->where('slug', 'brand')->value('id');
        $contentTypeId = TagType::query()->where('slug', 'content')->value('id');

        $items = Item::query()
            ->whereNotIn('id', $excludedItemIds)
            ->orderBy('id')
            ->pluck('name', 'id')
            ->mapWithKeys(fn (mixed $name, int|string $id): array => [(string) $id => $name]);

        $brands = Tag::query()
            ->where('tag_type_id', $brandTypeId)
            ->whereNotIn('id', $excludedTagIds)
            ->orderBy('id')
            ->pluck('name', 'id')
            ->mapWithKeys(fn (mixed $name, int|string $id): array => [(string) $id => $name]);

        $content = Tag::query()
            ->where('tag_type_id', $contentTypeId)
            ->whereNotIn('id', $excludedTagIds)
            ->orderBy('id')
            ->pluck('name', 'id')
            ->mapWithKeys(fn (mixed $name, int|string $id): array => [(string) $id => $name]);

        $labelMaps = [
            'items' => $items->toArray(),
            'brands' => $brands->toArray(),
            'content' => $content->toArray(),
        ];

        $json = (string) json_encode($labelMaps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $s3Path = 'ml/manifests/label_maps.json';
        Storage::disk('s3')->put($s3Path, $json);

        $this->table(
            ['Category', 'Count'],
            [
                ['Items', $items->count()],
                ['Brands', $brands->count()],
                ['Content', $content->count()],
            ]
        );

        $this->components->success("Uploaded to S3: {$s3Path}");

        return 0;
    }
}
