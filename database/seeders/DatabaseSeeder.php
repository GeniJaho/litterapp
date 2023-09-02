<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Geni Jaho',
            'email' => 'jahogeni@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $team = Team::factory()->create([
            'name' => 'Team A',
            'user_id' => $user->id,
            'personal_team' => true,
        ]);

        $user->teams()->attach($team);

        $this->call([
            TagSeeder::class,
        ]);
    }
}
