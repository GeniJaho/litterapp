<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'csrf_token' => csrf_token(),
            'grafana' => [
                'nav_link' => config('services.grafana.nav_link'),
                'map_link' => config('services.grafana.map_link'),
            ],
            'nav' => [
                'facebook_link' => config('social.facebook_link'),
                'twitter_link' => config('social.twitter_link'),
            ],
            'auth.user' => fn () => $request->user()
                ? $request->user()->append(['is_admin', 'is_being_impersonated'])
                : null,
        ]);
    }
}
