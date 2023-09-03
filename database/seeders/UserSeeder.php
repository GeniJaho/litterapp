<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userA = User::factory()->create([
            'name' => 'Geni Jaho',
            'email' => 'jahogeni@gmail.com',
            'password' => Hash::make('password'),
        ]);
        User::factory()->create([
            'name' => 'Fred Steenbergen',
            'email' => 'fred@littertagger.com',
            'password' => Hash::make('password'),
        ]);

        $team = Team::factory()->create([
            'name' => 'Team A',
            'user_id' => $userA->id,
            'personal_team' => true,
        ]);

        $userA->teams()->attach($team);
    }
}
