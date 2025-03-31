<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class EventsController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Events', [
            'grafanaDashboard' => config('services.grafana.events_map_link'),
        ]);
    }
}
