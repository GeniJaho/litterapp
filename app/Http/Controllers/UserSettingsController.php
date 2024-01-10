<?php

namespace App\Http\Controllers;

use App\DTO\UserSettings;
use App\Models\User;

class UserSettingsController extends Controller
{
    public function update(UserSettings $userSettings): void
    {
        /** @var User $user */
        $user = auth()->user();

        $user->update(['settings' => $userSettings]);
    }
}
