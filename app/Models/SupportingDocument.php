<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupportingDocument extends Model
{
    protected $fillable = [
        'user_id',
        'documentable_type',
        'documentable_id',
        'so_label',
        'filename',
        'path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve the object key used by current storage backends.
     */
    public function getStorageKeyAttribute(): ?string
    {
        $filename = trim((string) $this->filename);
        if ($filename !== '' && ! $this->isAbsoluteUrl($filename)) {
            return ltrim($filename, '/');
        }

        $path = trim((string) $this->path);
        if ($path !== '' && ! $this->isAbsoluteUrl($path)) {
            return ltrim($path, '/');
        }

        return null;
    }

    /**
     * Resolve a browser-safe URL for previews.
     */
    public function getFileUrlAttribute(): string
    {
        $reference = trim((string) ($this->path ?: $this->filename));

        if ($reference === '') {
            return '';
        }

        if ($this->isAbsoluteUrl($reference)) {
            return $reference;
        }

        try {
            return Storage::disk('s3')->temporaryUrl(
                $reference,
                now()->addMinutes((int) config('filesystems.media_url_ttl_minutes', 30))
            );
        } catch (\Throwable $e) {
            // Fall through to non-signed URL options below.
        }

        try {
            return Storage::disk('s3')->url($reference);
        } catch (\Throwable $e) {
            // Fall through to local legacy storage fallback.
        }

        if (Storage::disk('public')->exists($reference)) {
            return asset('storage/'.ltrim($reference, '/'));
        }

        return $reference;
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }

    private function isAbsoluteUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }
}
