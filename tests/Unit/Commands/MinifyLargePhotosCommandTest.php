<?php

use App\Jobs\MinifyPhoto;
use App\Models\Photo;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\artisan;

test('it dispatches minify jobs for large photos or photos without size_kb', function (): void {
    Bus::fake();

    $photo1 = Photo::factory()->create(['size_kb' => null]);
    $photo2 = Photo::factory()->create(['size_kb' => 301]);
    $photo3 = Photo::factory()->create(['size_kb' => 300]);
    $photo4 = Photo::factory()->create(['size_kb' => 100]);

    artisan('app:minify-large-photos')
        ->expectsOutputToContain('Dispatching 2 photos for minification...')
        ->assertSuccessful();

    Bus::assertDispatched(MinifyPhoto::class, 2);
    Bus::assertDispatched(fn (MinifyPhoto $job): bool => $job->photo->id === $photo1->id);
    Bus::assertDispatched(fn (MinifyPhoto $job): bool => $job->photo->id === $photo2->id);
    Bus::assertNotDispatched(fn (MinifyPhoto $job): bool => $job->photo->id === $photo3->id);
    Bus::assertNotDispatched(fn (MinifyPhoto $job): bool => $job->photo->id === $photo4->id);
});

test('it does not dispatch jobs if no photos match', function (): void {
    Bus::fake();

    Photo::factory()->create(['size_kb' => 300]);
    Photo::factory()->create(['size_kb' => 100]);

    artisan('app:minify-large-photos')
        ->expectsOutputToContain('No photos found for minification.')
        ->assertSuccessful();

    Bus::assertNotDispatched(MinifyPhoto::class);
});
