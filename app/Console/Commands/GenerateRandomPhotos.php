<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\progress;

class GenerateRandomPhotos extends Command
{
    protected $signature = 'app:generate-random-photos';

    public function handle(): void
    {
        $user = User::query()
            ->where('email', 'trashkiller@litterhero.com')
            ->first();

        $bar = progress('Generating 1M photos with tags...', 1_000_000);

        $bar->start();

        $now = now()->toDateTimeString();

        for ($i = 0; $i < 200; $i++) {
            $photos = [];
            $items = [];
            $tags = [];

            for ($j = 0; $j < 5000; $j++) {
                $photos[] = [
                    'user_id' => $user->id,
                    'path' => 'photos/default.jpg',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('photos')->insert($photos);

            for ($j = 0; $j < 5000; $j++) {
                $items[] = [
                    'photo_id' => $i * 5000 + $j + 2,
                    'item_id' => random_int(1, 300),
                    'picked_up' => 0,
                    'recycled' => 0,
                    'quantity' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('photo_items')->insert($items);

            for ($k = 0; $k < 5000; $k++) {
                $tags[] = [
                    'photo_item_id' => $i * 5000 + $k + 1,
                    'tag_id' => random_int(1, 1500),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('photo_item_tag')->insert($tags);

            $bar->advance(5000);
        }

        $bar->finish();
    }
}
