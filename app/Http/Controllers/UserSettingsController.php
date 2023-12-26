<?php

namespace App\Http\Controllers;

use App\DTO\UserSettings;

class UserSettingsController extends Controller
{
    public function update(UserSettings $userSettings): void
    {
        auth()->user()->update(['settings' => $userSettings]);
    }
}
