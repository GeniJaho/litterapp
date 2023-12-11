<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\TagType;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $brand = TagType::query()->create(['name' => 'Brand', 'slug' => 'brand']);
        $material = TagType::query()->create(['name' => 'Material', 'slug' => 'material']);

        Tag::query()->insert([
            //brands
            ['name' => 'Acadia', 'slug' => 'acadia', 'tag_type_id' => $brand->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Applegreen', 'slug' => 'applegreen', 'tag_type_id' => $brand->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Asahi', 'slug' => 'asahi', 'tag_type_id' => $brand->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Avoca', 'slug' => 'avoca', 'tag_type_id' => $brand->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bacardi', 'slug' => 'bacardi', 'tag_type_id' => $brand->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ballygowan', 'slug' => 'ballygowan', 'tag_type_id' => $brand->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bewleys', 'slug' => 'bewleys', 'tag_type_id' => $brand->id, 'created_at' => $now, 'updated_at' => $now],

            //materials
            ['name' => 'Aluminium', 'slug' => 'aluminium', 'tag_type_id' => $material->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bronze', 'slug' => 'bronze', 'tag_type_id' => $material->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Copper', 'slug' => 'copper', 'tag_type_id' => $material->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fiberglass', 'slug' => 'fiberglass', 'tag_type_id' => $material->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Glass', 'slug' => 'glass', 'tag_type_id' => $material->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Iron or Steel', 'slug' => 'iron_or_steel', 'tag_type_id' => $material->id, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Wood', 'slug' => 'wood', 'tag_type_id' => $material->id, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
