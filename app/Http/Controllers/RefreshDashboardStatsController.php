<?php

namespace App\Http\Controllers;

use App\Enums\DashboardPeriod;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RefreshDashboardStatsController extends Controller
{
    public function __invoke(Request $request, #[CurrentUser] User $user): RedirectResponse
    {
        $period = DashboardPeriod::fromKey($request->string('period')->toString());

        Cache::forget($period->cacheKey($user));

        return back();
    }
}
