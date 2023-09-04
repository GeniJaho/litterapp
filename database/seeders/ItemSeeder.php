<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Item::factory(10)->sequence(
            ['name' => 'Bottle', 'slug' => 'bottle'],
            ['name' => 'Cup', 'slug' => 'cup'],
            ['name' => 'Plate', 'slug' => 'plate'],
            ['name' => 'Spoon', 'slug' => 'spoon'],
            ['name' => 'Fork', 'slug' => 'fork'],
            ['name' => 'Knife', 'slug' => 'knife'],
            ['name' => 'Bowl', 'slug' => 'bowl'],
            ['name' => 'Pan', 'slug' => 'pan'],
            ['name' => 'Pot', 'slug' => 'pot'],
            ['name' => 'Glass', 'slug' => 'glass'],
        )->create();
    }
}
