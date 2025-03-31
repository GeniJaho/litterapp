<?php

use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

test('anyone can view the events page', function (): void {
    $response = $this->get('/events');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page): AssertableJson => $page
        ->component('Events')
        ->where('grafanaDashboard', config('services.grafana.events_map_link'))
        ->etc()
    );
});
