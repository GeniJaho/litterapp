<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Photo;
use App\Models\PhotoDetail;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PhotoDetail>
 */
class PhotoDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'photo_id' => Photo::factory(),
            'content_hash' => hash('sha256', $this->faker->unique()->uuid()),
        ];
    }
}
