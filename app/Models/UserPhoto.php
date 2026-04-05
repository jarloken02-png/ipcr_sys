<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'path',
        'original_name',
        'mime_type',
        'file_size',
        'is_profile_photo',
    ];

    protected $casts = [
        'is_profile_photo' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the user that owns this photo.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL path to the photo.
     */
    public function getPhotoUrlAttribute()
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

    private function isAbsoluteUrl(string $value): bool
    {
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }

    /**
     * Get the file size in MB.
     */
    public function getFileSizeInMBAttribute()
    {
        return round($this->file_size / 1024 / 1024, 2);
    }
}
