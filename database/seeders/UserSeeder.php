<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@litterhero.com',
            'password' => Hash::make('password'),
        ]);

        $userA = User::factory()->create([
            'name' => 'Geni Jaho',
            'email' => 'jahogeni@gmail.com',
            'password' => Hash::make('password'),
        ]);

        $team = Team::factory()->create([
            'name' => 'Team A',
            'user_id' => $userA->id,
            'personal_team' => true,
        ]);

        $userA->teams()->attach($team);

        tap(User::factory()->create([
            'name' => 'Waste Wizard',
            'email' => 'wastewizard@litterhero.com',
            'password' => Hash::make('password'),
        ]), function (User $user) {
            $user->ownedTeams()->save(Team::forceCreate([
                'user_id' => $user->id,
                'name' => "Wizard's Team",
                'personal_team' => true,
            ]));
            $user->photos()->create([
                'path' => 'photos/default.jpg',
            ]);
            File::copy(
                storage_path('app/default.jpg'),
                public_path('storage/photos/default.jpg')
            );
        });

        User::factory()->create([
            'name' => 'Trash Killer',
            'email' => 'trashkiller@litterhero.com',
            'password' => Hash::make('password'),
        ]);
    }
}
