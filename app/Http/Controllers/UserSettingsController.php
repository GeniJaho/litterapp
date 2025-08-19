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

        $user->settings->picked_up_by_default = $userSettings->picked_up_by_default;
        $user->settings->recycled_by_default = $userSettings->recycled_by_default;
        $user->settings->deposit_by_default = $userSettings->deposit_by_default;
        $user->settings->litterbot_enabled = $userSettings->litterbot_enabled;
        $user->save();
    }
}
