<?php

namespace App\Console\Commands;

use App\Support\MediaAsset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigratePublicImagesToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:migrate-public-images-to-r2
                            {--dry-run : Preview uploads without writing to R2}
                            {--delete-local : Delete local files after successful upload}
                            {--prefix=images : Destination prefix on the s3 disk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate files from public/images to Cloudflare R2 (s3 disk).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceDirectory = public_path('images');
        $dryRun = (bool) $this->option('dry-run');
        $deleteLocal = (bool) $this->option('delete-local');
        $destinationPrefix = trim((string) $this->option('prefix'), '/');

        if (! is_dir($sourceDirectory)) {
            $this->error('Source directory does not exist: '.$sourceDirectory);

            return self::FAILURE;
        }

        if ($destinationPrefix === '') {
            $this->error('The destination prefix cannot be empty.');

            return self::FAILURE;
        }

        $files = collect(File::allFiles($sourceDirectory))
            ->sortBy(function ($file) {
                return str_replace('\\', '/', $file->getRelativePathname());
            })
            ->values();

        if ($files->isEmpty()) {
            $this->warn('No files found in public/images. Nothing to migrate.');

            return self::SUCCESS;
        }

        $this->info('Starting public image migration to R2...');
        $this->line('Mode: '.($dryRun ? 'dry-run' : 'write'));
        $this->line('Delete local after upload: '.($deleteLocal ? 'yes' : 'no'));
        $this->line('Destination prefix: '.$destinationPrefix);

        $stats = [
            'scanned' => 0,
            'uploaded' => 0,
            'already' => 0,
            'planned' => 0,
            'deleted' => 0,
            'failed' => 0,
        ];

        foreach ($files as $file) {
            $stats['scanned']++;

            $relativePath = str_replace('\\', '/', $file->getRelativePathname());
            $storageKey = $destinationPrefix.'/'.$relativePath;

            try {
                $s3Disk = Storage::disk('s3');

                if ($s3Disk->exists($storageKey)) {
                    $stats['already']++;
                    $this->line('Already exists: '.$storageKey);

                    if ($deleteLocal && ! $dryRun && File::exists($file->getPathname()) && File::delete($file->getPathname())) {
                        $stats['deleted']++;
                        $this->line('Deleted local: '.$relativePath);
                    }

                    continue;
                }

                if ($dryRun) {
                    $stats['planned']++;
                    $this->line('[dry-run] Upload: '.$relativePath.' -> '.$storageKey);

                    continue;
                }

                $stream = fopen($file->getPathname(), 'rb');
                if ($stream === false) {
                    throw new \RuntimeException('Unable to open local file stream.');
                }

                $uploaded = $s3Disk->put($storageKey, $stream, [
                    'ContentType' => $this->detectMimeType($file->getPathname()),
                ]);

                if (is_resource($stream)) {
                    fclose($stream);
                }

                if (! $uploaded) {
                    throw new \RuntimeException('Upload to s3 disk returned false.');
                }

                $stats['uploaded']++;
                $this->info('Uploaded: '.$storageKey);

                if ($deleteLocal && File::delete($file->getPathname())) {
                    $stats['deleted']++;
                    $this->line('Deleted local: '.$relativePath);
                }
            } catch (\Throwable $e) {
                $stats['failed']++;
                $this->error('Failed: '.$relativePath.' ('.$e->getMessage().')');
            }
        }

        if ($deleteLocal && ! $dryRun) {
            $this->cleanupEmptyDirectories($sourceDirectory);
        }

        $this->newLine();
        $this->table(
            ['Scanned', 'Uploaded', 'Already', 'Planned', 'Deleted', 'Failed'],
            [[
                $stats['scanned'],
                $stats['uploaded'],
                $stats['already'],
                $stats['planned'],
                $stats['deleted'],
                $stats['failed'],
            ]]
        );

        if ($stats['failed'] > 0) {
            $this->warn('Migration completed with errors. Resolve failures and rerun.');

            return self::FAILURE;
        }

        $this->info('Public image migration completed successfully.');
        $this->line('Use MediaAsset helper in Blade/PHP: '.MediaAsset::class.'::publicImageUrl(...)');

        return self::SUCCESS;
    }

    private function detectMimeType(string $filePath): string
    {
        $mimeType = File::mimeType($filePath);

        return is_string($mimeType) && $mimeType !== ''
            ? $mimeType
            : 'application/octet-stream';
    }

    private function cleanupEmptyDirectories(string $rootDirectory): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootDirectory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $entry) {
            if (! $entry->isDir()) {
                continue;
            }

            $directoryPath = $entry->getPathname();
            $remainingEntries = scandir($directoryPath);
            if ($remainingEntries !== false && count($remainingEntries) === 2) {
                @rmdir($directoryPath);
            }
        }
    }
}
