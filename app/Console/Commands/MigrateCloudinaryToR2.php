<?php

namespace App\Console\Commands;

use App\Models\SupportingDocument;
use App\Models\UserPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MigrateCloudinaryToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:migrate-cloudinary-to-r2
                            {--dry-run : Preview records to migrate without writing files or updating DB}
                            {--chunk=100 : Number of records per batch}
                            {--limit=0 : Max records per model to scan (0 = no limit)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate legacy Cloudinary user photos and supporting documents to Cloudflare R2 (s3 disk).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $limit = max(0, (int) $this->option('limit'));

        $this->info('Starting Cloudinary to R2 migration...');
        $this->line('Mode: '.($dryRun ? 'dry-run' : 'write'));
        $this->line('Chunk size: '.$chunkSize);
        $this->line('Limit per model: '.($limit > 0 ? $limit : 'none'));

        $photoStats = $this->migrateUserPhotos($dryRun, $chunkSize, $limit);
        $docStats = $this->migrateSupportingDocuments($dryRun, $chunkSize, $limit);

        $this->newLine();
        $this->table(
            ['Model', 'Scanned', 'Migrated', 'Already', 'Planned', 'Skipped', 'Failed'],
            [
                [
                    'UserPhoto',
                    $photoStats['scanned'],
                    $photoStats['migrated'],
                    $photoStats['already'],
                    $photoStats['planned'],
                    $photoStats['skipped'],
                    $photoStats['failed'],
                ],
                [
                    'SupportingDocument',
                    $docStats['scanned'],
                    $docStats['migrated'],
                    $docStats['already'],
                    $docStats['planned'],
                    $docStats['skipped'],
                    $docStats['failed'],
                ],
            ]
        );

        $failedTotal = $photoStats['failed'] + $docStats['failed'];
        if ($failedTotal > 0) {
            $this->warn('Migration completed with failures. Review logs and rerun if needed.');
        } else {
            $this->info('Migration completed successfully.');
        }

        return self::SUCCESS;
    }

    private function migrateUserPhotos(bool $dryRun, int $chunkSize, int $limit): array
    {
        $stats = $this->emptyStats();
        $processed = 0;

        UserPhoto::query()
            ->orderBy('id')
            ->chunkById($chunkSize, function ($photos) use (&$stats, &$processed, $limit, $dryRun) {
                foreach ($photos as $photo) {
                    if ($limit > 0 && $processed >= $limit) {
                        return false;
                    }

                    $processed++;
                    $stats['scanned']++;

                    try {
                        $result = $this->migrateUserPhotoRecord($photo, $dryRun);
                    } catch (\Throwable $e) {
                        $this->error("UserPhoto #{$photo->id} migration crashed: {$e->getMessage()}");
                        $result = 'failed';
                    }

                    $stats[$result]++;
                }

                return true;
            });

        return $stats;
    }

    private function migrateSupportingDocuments(bool $dryRun, int $chunkSize, int $limit): array
    {
        $stats = $this->emptyStats();
        $processed = 0;

        SupportingDocument::query()
            ->orderBy('id')
            ->chunkById($chunkSize, function ($documents) use (&$stats, &$processed, $limit, $dryRun) {
                foreach ($documents as $document) {
                    if ($limit > 0 && $processed >= $limit) {
                        return false;
                    }

                    $processed++;
                    $stats['scanned']++;

                    try {
                        $result = $this->migrateSupportingDocumentRecord($document, $dryRun);
                    } catch (\Throwable $e) {
                        $this->error("SupportingDocument #{$document->id} migration crashed: {$e->getMessage()}");
                        $result = 'failed';
                    }

                    $stats[$result]++;
                }

                return true;
            });

        return $stats;
    }

    private function migrateUserPhotoRecord(UserPhoto $photo, bool $dryRun): string
    {
        $existingKey = $photo->storage_key;
        if ($existingKey && Storage::disk('s3')->exists($existingKey)) {
            return 'already';
        }

        $sourceUrl = $this->firstCloudinaryUrl([$photo->path, $photo->filename]);
        if (! $sourceUrl) {
            return 'skipped';
        }

        $download = $this->downloadRemoteFile($sourceUrl);
        if (! $download['ok']) {
            $this->error("UserPhoto #{$photo->id} download failed: {$download['error']}");

            return 'failed';
        }

        $extension = $this->determineExtension(
            $photo->original_name,
            $sourceUrl,
            $download['mime_type']
        );

        $safeBaseName = $this->sanitizeBaseName(pathinfo((string) $photo->original_name, PATHINFO_FILENAME), 'photo');
        $key = 'user_photos/'.$photo->user_id.'/migrated/'.now()->format('Ymd_His').'_'.$photo->id.'_'.$safeBaseName.'.'.$extension;

        if ($dryRun) {
            $this->line("[dry-run] UserPhoto #{$photo->id} -> {$key}");

            return 'planned';
        }

        $uploaded = Storage::disk('s3')->put($key, $download['body'], [
            'ContentType' => $download['mime_type'] ?: ($photo->mime_type ?: 'application/octet-stream'),
        ]);

        if (! $uploaded) {
            $this->error("UserPhoto #{$photo->id} upload failed.");

            return 'failed';
        }

        $photo->filename = $key;
        $photo->path = $key;
        if (! $photo->mime_type && $download['mime_type']) {
            $photo->mime_type = $download['mime_type'];
        }
        $photo->file_size = strlen($download['body']);
        $photo->save();

        $this->info("Migrated UserPhoto #{$photo->id}");

        return 'migrated';
    }

    private function migrateSupportingDocumentRecord(SupportingDocument $document, bool $dryRun): string
    {
        $existingKey = $document->storage_key;
        if ($existingKey && Storage::disk('s3')->exists($existingKey)) {
            return 'already';
        }

        $sourceUrl = $this->firstCloudinaryUrl([$document->path, $document->filename]);
        if (! $sourceUrl) {
            return 'skipped';
        }

        $download = $this->downloadRemoteFile($sourceUrl);
        if (! $download['ok']) {
            $this->error("SupportingDocument #{$document->id} download failed: {$download['error']}");

            return 'failed';
        }

        $extension = $this->determineExtension(
            $document->original_name,
            $sourceUrl,
            $download['mime_type']
        );

        $safeBaseName = $this->sanitizeBaseName(pathinfo((string) $document->original_name, PATHINFO_FILENAME), 'document');
        $docType = $this->sanitizeBaseName((string) $document->documentable_type, 'document_type');
        $key = 'supporting_documents/'
            .$document->user_id
            .'/migrated/'
            .$docType
            .'/'
            .$document->documentable_id
            .'/'
            .now()->format('Ymd_His')
            .'_'
            .$document->id
            .'_'
            .$safeBaseName
            .'.'
            .$extension;

        if ($dryRun) {
            $this->line("[dry-run] SupportingDocument #{$document->id} -> {$key}");

            return 'planned';
        }

        $uploaded = Storage::disk('s3')->put($key, $download['body'], [
            'ContentType' => $download['mime_type'] ?: ($document->mime_type ?: 'application/octet-stream'),
        ]);

        if (! $uploaded) {
            $this->error("SupportingDocument #{$document->id} upload failed.");

            return 'failed';
        }

        $document->filename = $key;
        $document->path = $key;
        if (! $document->mime_type && $download['mime_type']) {
            $document->mime_type = $download['mime_type'];
        }
        $document->file_size = strlen($download['body']);
        $document->save();

        $this->info("Migrated SupportingDocument #{$document->id}");

        return 'migrated';
    }

    private function emptyStats(): array
    {
        return [
            'scanned' => 0,
            'migrated' => 0,
            'already' => 0,
            'planned' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];
    }

    private function firstCloudinaryUrl(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '' && $this->isCloudinaryUrl($value)) {
                return $value;
            }
        }

        return null;
    }

    private function isCloudinaryUrl(string $value): bool
    {
        if (! str_starts_with($value, 'http://') && ! str_starts_with($value, 'https://')) {
            return false;
        }

        $host = strtolower((string) parse_url($value, PHP_URL_HOST));

        return $host !== '' && str_contains($host, 'cloudinary.com');
    }

    private function downloadRemoteFile(string $url): array
    {
        try {
            $response = Http::timeout(45)->retry(2, 500)->get($url);

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'HTTP '.$response->status(),
                    'body' => '',
                    'mime_type' => null,
                ];
            }

            return [
                'ok' => true,
                'error' => '',
                'body' => $response->body(),
                'mime_type' => trim((string) $response->header('Content-Type')) ?: null,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'body' => '',
                'mime_type' => null,
            ];
        }
    }

    private function determineExtension(?string $originalName, string $sourceUrl, ?string $mimeType): string
    {
        $fromOriginal = strtolower((string) pathinfo((string) $originalName, PATHINFO_EXTENSION));
        if ($fromOriginal !== '') {
            return $fromOriginal;
        }

        $sourcePath = (string) parse_url($sourceUrl, PHP_URL_PATH);
        $fromUrl = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
        if ($fromUrl !== '') {
            return $fromUrl;
        }

        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/plain' => 'txt',
        ];

        $normalizedMime = strtolower(trim((string) $mimeType));
        if ($normalizedMime !== '' && isset($mimeMap[$normalizedMime])) {
            return $mimeMap[$normalizedMime];
        }

        return 'bin';
    }

    private function sanitizeBaseName(string $value, string $fallback): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower(trim($value))) ?? '';
        $sanitized = trim($sanitized, '_-');

        return $sanitized !== '' ? $sanitized : $fallback;
    }
}
