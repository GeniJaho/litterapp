<?php

namespace App\Http\Controllers;

use App\Actions\Stats\GetUserStatsAction;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request, GetUserStatsAction $getUserStatsAction): Response
    {
        /** @var User $user */
        $user = $request->user();

        $period = $getUserStatsAction->resolvePeriod($request->string('period')->toString());

        return Inertia::render('Dashboard', [
            'period' => $period,
            'availablePeriods' => $getUserStatsAction->availablePeriods(),
            'stats' => Inertia::defer(fn (): array => $getUserStatsAction->run($user, $period)),
        ]);
    }
}
