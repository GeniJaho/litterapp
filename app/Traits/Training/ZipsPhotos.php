<?php

namespace App\Traits\Training;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

trait ZipsPhotos
{
    private const LOCAL_DISK = 'local';

    /**
     * @phpstan-ignore-next-line
     */
    protected function zipPhotos(array $results, int $limitPerClass, int $totalPhotos, $zipPrefix = ''): void
    {
        $zipFilePath = "zips/photos{$zipPrefix}_{$limitPerClass}_".now()->format('Y_m_d_H_i').'.zip';
        $zipFilePathOnDisk = Storage::disk(self::LOCAL_DISK)->path($zipFilePath);

        if (! Storage::disk(self::LOCAL_DISK)->exists('zips')) {
            Storage::disk(self::LOCAL_DISK)->makeDirectory('zips');
        }

        $this->components->info('Zipping images at '.$zipFilePathOnDisk);

        $zip = new ZipArchive;

        if ($zip->open($zipFilePathOnDisk, ZipArchive::CREATE) !== true) {
            $this->components->error("Failed to create zip file: {$zipFilePathOnDisk}");

            return;
        }

        $bar = $this->output->createProgressBar($totalPhotos);
        $bar->start();

        $shouldUseFullUrl = config('filesystems.default') === 's3';
        foreach ($results as $result) {
            foreach ($result['photos'] as $photoPath) {
                $fullPath = $shouldUseFullUrl
                    ? Storage::url($photoPath)
                    : Storage::path($photoPath);

                $zip->addFile($fullPath, "/{$result['slug']}/".basename((string) $photoPath));

                $bar->advance();
            }
        }

        $bar->finish();

        $this->newLine(2);

        $this->components->info('Finalizing zip file');

        $zip->close();

        $this->components->info(sprintf(
            'Peak memory used: [%s]',
            $this->formatStorageSize(memory_get_peak_usage(true))
        ));
        if (Storage::disk(self::LOCAL_DISK)->exists($zipFilePath)) {
            $this->components->info(sprintf(
                'Zip file size: [%s]',
                $this->formatStorageSize(Storage::disk(self::LOCAL_DISK)->size($zipFilePath))
            ));

            $this->components->info('Uploading zip file to S3...');

            $this->uploadZipFileToS3($zipFilePath);
        }

        $this->components->success('Done!');
    }

    protected function uploadZipFileToS3(string $zipFilePath): void
    {
        $uploadResult = Storage::disk('s3')->putFile(
            $zipFilePath,
            Storage::disk(self::LOCAL_DISK)->path($zipFilePath),
        );

        if ($uploadResult) {
            Storage::disk(self::LOCAL_DISK)->delete($zipFilePath);
        } else {
            $this->components->error('Failed to upload zip file to S3.');
        }
    }

    protected function formatStorageSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $bytes = max($bytes, 0);
        $pow = (int) floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= 1024 ** $pow;

        return round($bytes, $precision).$units[$pow];
    }
}
