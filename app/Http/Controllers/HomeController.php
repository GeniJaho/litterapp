<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Home', [
            'announcements' => Announcement::query()
                ->published()
                ->orderByDesc('published_at')
                ->limit(3)
                ->get()
                ->map(fn (Announcement $announcement): array => [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'body' => $announcement->body,
                    'link_url' => $announcement->link_url,
                    'link_label' => $announcement->link_label,
                    'image_url' => $announcement->image_url,
                    'published_at' => $announcement->published_at?->toIso8601String(),
                ])
                ->all(),
        ]);
    }
}
