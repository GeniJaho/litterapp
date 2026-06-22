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

        return Inertia::render('Dashboard', [
            'stats' => $getUserStatsAction->run($user, $request->string('period')->toString()),
        ]);
    }
}
