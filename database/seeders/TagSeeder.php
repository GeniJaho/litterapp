<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
        Tag::query()->insert([
            //brands
            ['name' => 'Acadia', 'slug' => 'acadia', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Applegreen', 'slug' => 'applegreen', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Asahi', 'slug' => 'asahi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Avoca', 'slug' => 'avoca', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bacardi', 'slug' => 'bacardi', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ballygowan', 'slug' => 'ballygowan', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bewleys', 'slug' => 'bewleys', 'created_at' => $now, 'updated_at' => $now],

            //materials
            ['name' => 'Aluminium', 'slug' => 'aluminium', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Bronze', 'slug' => 'bronze', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Copper', 'slug' => 'copper', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fiberglass', 'slug' => 'fiberglass', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Glass', 'slug' => 'glass', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Iron or Steel', 'slug' => 'iron_or_steel', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Wood', 'slug' => 'wood', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
