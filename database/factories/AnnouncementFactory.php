<?php

namespace Database\Factories;

use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'link_url' => null,
            'link_label' => null,
            'image_path' => null,
            'published_at' => now(),
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (): array => [
            'published_at' => null,
        ]);
    }

    public function scheduled(): self
    {
        return $this->state(fn (): array => [
            'published_at' => now()->addDay(),
        ]);
    }
}
