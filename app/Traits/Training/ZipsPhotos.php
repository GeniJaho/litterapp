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
        $zipDirName = "zips/photos{$zipPrefix}_{$limitPerClass}_".now()->format('Y_m_d_H_i');

        if (! Storage::disk(self::LOCAL_DISK)->exists($zipDirName)) {
            Storage::disk(self::LOCAL_DISK)->makeDirectory($zipDirName);
        }

        $this->components->info('Zipping images to '.$zipDirName);

        $bar = $this->output->createProgressBar($totalPhotos);
        $bar->start();

        $badPhotos = [];
        $localZipFiles = [];

        foreach ($results as $result) {
            if (empty($result['photos'])) {
                continue;
            }

            $zipFilePath = "{$zipDirName}/{$result['slug']}.zip";
            $zipFilePathOnDisk = Storage::disk(self::LOCAL_DISK)->path($zipFilePath);
            $localZipFiles[] = $zipFilePath;

            $zip = new ZipArchive;

            if ($zip->open($zipFilePathOnDisk, ZipArchive::CREATE) !== true) {
                $this->components->error("Failed to open zip file: {$zipFilePathOnDisk}");

                continue;
            }

            foreach ($result['photos'] as $photoPath) {
                $contents = Storage::get($photoPath);

                if ($contents === null) {
                    $badPhotos[] = $photoPath;
                } else {
                    $zip->addFromString(
                        basename((string) $photoPath),
                        $contents
                    );
                }

                $bar->advance();
            }

            $zip->close();

            $this->uploadZipFileToS3($zipFilePath);
        }

        $bar->finish();

        $this->newLine(2);

        if ($badPhotos !== []) {
            $this->components->warn(sprintf(
                'Warning: %d photos could not be found and were skipped. Check logs for details.',
                count($badPhotos)
            ));

            logger()->debug('Bad photos during zipping:', $badPhotos);
        }

        $this->components->info(sprintf(
            'Peak memory used: [%s]',
            $this->formatStorageSize(memory_get_peak_usage(true))
        ));

        $this->deleteLocalZipFiles($localZipFiles, $zipDirName);

        $this->components->success('Done!');
    }

    protected function uploadZipFileToS3(string $zipFilePath): void
    {
        $contents = Storage::disk(self::LOCAL_DISK)->get($zipFilePath);

        if ($contents === null) {
            $this->components->error("Failed to read local zip file: {$zipFilePath}");

            return;
        }

        $uploadResult = Storage::disk('s3')->put($zipFilePath, $contents);

        if (! $uploadResult) {
            $this->components->error("Failed to upload zip file to S3: {$zipFilePath}");
        }
    }

    /**
     * @param  list<string>  $localZipFiles
     */
    protected function deleteLocalZipFiles(array $localZipFiles, string $zipDirName): void
    {
        foreach ($localZipFiles as $zipFilePath) {
            Storage::disk(self::LOCAL_DISK)->delete($zipFilePath);
        }

        Storage::disk(self::LOCAL_DISK)->deleteDirectory($zipDirName);
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
