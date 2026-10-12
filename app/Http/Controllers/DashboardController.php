<?php

namespace App\Http\Controllers;

use App\Actions\Stats\GetUserStatsAction;
use App\Enums\DashboardPeriod;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const CACHE_TTL_MINUTES = 60;

    public function __invoke(
        Request $request,
        GetUserStatsAction $getUserStatsAction,
        #[CurrentUser] User $user,
    ): Response {
        $period = DashboardPeriod::fromKey($request->string('period')->toString());

        return Inertia::render('Dashboard', [
            'period' => $period->key(),
            'availablePeriods' => DashboardPeriod::options(),
            'stats' => Inertia::defer(fn (): array => Cache::remember(
                $period->cacheKey($user),
                now()->addMinutes(self::CACHE_TTL_MINUTES),
                fn (): array => $getUserStatsAction->run($user, $period),
            )),
        ]);
    }
}
