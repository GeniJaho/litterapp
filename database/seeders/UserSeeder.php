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

        tap(User::factory()->create([
            'name' => 'Waste Wizard',
            'email' => 'wastewizard@litterhero.com',
            'password' => Hash::make('password'),
        ]), fn (User $user) => $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => "Wizard's Team",
            'personal_team' => true,
        ])));

        User::factory()->create([
            'name' => 'Trash Killer',
            'email' => 'trashkiller@litterhero.com',
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
