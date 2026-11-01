<?php

use App\DTO\PhotoExport;
use App\Exports\PhotosCsvExport;
use App\Models\Photo;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertSame;

test('a user can export their photos', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photo = Photo::factory()->for($user)->create();

    $response = $this->get('/photos/export');

    $response->assertOk();
    $response->assertDownload('photos.json');
    $response->assertStreamedJsonContent([
        'photos' => [[
            'id' => $photo->id,
            'original_file_name' => $photo->original_file_name,
            'latitude' => $photo->latitude,
            'longitude' => $photo->longitude,
            'taken_at_local' => $photo->taken_at_local,
            'created_at' => $photo->created_at->toIso8601String(),
            'items' => [],
        ]],
    ]);
});

test('if there are no photos the export still works', function (): void {
    $this->actingAs($user = User::factory()->create());

    $response = $this->get('/photos/export');

    $response->assertOk();
    $response->assertDownload('photos.json');
    $response->assertStreamedJsonContent([
        'photos' => [],
    ]);
});

test('the user can download photos in CSV format', function (): void {
    Excel::fake();
    $this->actingAs($user = User::factory()->create());

    $photo = Photo::factory()->for($user)->create();

    $response = $this->get('/photos/export?format=csv');

    $response->assertOk();

    Excel::assertDownloaded('photos.csv', function (PhotosCsvExport $export) use ($photo): bool {
        $dto = $export->generator()->current();

        assertInstanceOf(PhotoExport::class, $dto);
        assertSame([
            'id' => $photo->id,
            'original_file_name' => $photo->original_file_name,
            'latitude' => $photo->latitude,
            'longitude' => $photo->longitude,
            'taken_at_local' => $photo->taken_at_local,
            'created_at' => $photo->created_at->toIso8601String(),
            'items' => [],
        ], $dto->toArray());

        return true;
    });
});

test('the CSV download contains a heading row and the photo data', function (): void {
    $this->actingAs($user = User::factory()->create());

    $photo = Photo::factory()->for($user)->create();

    $response = $this->get('/photos/export?format=csv');

    $response->assertOk();
    $response->assertDownload('photos.csv');

    /** @var BinaryFileResponse $download */
    $download = $response->baseResponse;
    /** @var string[] $lines */
    $lines = file($download->getFile()->getPathname(), FILE_IGNORE_NEW_LINES);
    $rows = array_map(fn (string $line): array => str_getcsv($line, escape: ''), $lines);

    expect($rows)->toHaveCount(2)
        ->and($rows[0])->toBe(['ID', 'Original File Name', 'Latitude', 'Longitude', 'Taken At Local', 'Created At', 'Items'])
        ->and($rows[1][0])->toBe((string) $photo->id)
        ->and($rows[1][1])->toBe($photo->original_file_name)
        ->and($rows[1][5])->toBe($photo->created_at->toIso8601String());
});
