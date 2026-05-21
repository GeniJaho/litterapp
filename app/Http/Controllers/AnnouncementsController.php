<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementsController extends Controller
{
    public function __invoke(): Response
    {
        $announcements = Announcement::query()
            ->published()
            ->orderByDesc('published_at')
            ->paginate(20)
            ->through(fn (Announcement $announcement): array => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'body' => $announcement->body,
                'link_url' => $announcement->link_url,
                'link_label' => $announcement->link_label,
                'image_url' => $announcement->image_url,
                'published_at' => $announcement->published_at?->toIso8601String(),
            ]);

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }
}
