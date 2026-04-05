<?php

namespace App\Services;

use App\Models\UserPhoto;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PhotoService
{
    public function uploadPhoto($file, $user)
    {
        try {
            if (! $file) {
                throw new \Exception('No file provided');
            }

            $timestamp = now()->format('Y-m-d_H-i-s');
            $username = strtolower(str_replace(' ', '-', $user->username));
            $username = trim((string) preg_replace('/[^a-z0-9_-]/', '-', $username), '-');
            $username = $username !== '' ? $username : 'user';

            $extension = strtolower((string) ($file->getClientOriginalExtension() ?: 'jpg'));
            $filename = "{$timestamp}-{$username}.{$extension}";
            $folderPath = "user_photos/{$user->id}";

            $storedPath = Storage::disk('s3')->putFileAs(
                $folderPath,
                $file,
                $filename,
                [
                    'ContentType' => $file->getClientMimeType() ?: 'application/octet-stream',
                ]
            );

            if ($storedPath === false) {
                throw new \Exception('Failed to upload photo to cloud storage.');
            }

            // Unset current profile photo
            UserPhoto::where('user_id', $user->id)
                ->where('is_profile_photo', true)
                ->update(['is_profile_photo' => false]);

            // Save photo record to database
            $userPhoto = UserPhoto::create([
                'user_id' => $user->id,
                'filename' => $storedPath,
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType() ?: 'application/octet-stream',
                'file_size' => $file->getSize(),
                'is_profile_photo' => true,
            ]);

            return $userPhoto;
        } catch (\Exception $e) {
            Log::error('Photo upload error: '.$e->getMessage());
            throw new \Exception('Photo upload failed: '.$e->getMessage());
        }
    }

    public function deletePhoto(UserPhoto $photo)
    {
        try {
            $this->deleteStoredPhoto($photo);

            // Delete from database
            $photo->delete();
        } catch (\Exception $e) {
            Log::error('Photo delete error: '.$e->getMessage());
            throw new \Exception('Failed to delete photo: '.$e->getMessage());
        }
    }

    public function deleteAllUserPhotos($user)
    {
        try {
            $photos = UserPhoto::where('user_id', $user->id)->get();

            foreach ($photos as $photo) {
                try {
                    $this->deleteStoredPhoto($photo);
                } catch (\Exception $e) {
                    Log::warning("Failed to delete photo {$photo->id} from storage: ".$e->getMessage());
                }
            }

            // Delete all records from database
            UserPhoto::where('user_id', $user->id)->delete();
        } catch (\Exception $e) {
            Log::error('Delete all photos error: '.$e->getMessage());
            throw new \Exception('Failed to delete user photos: '.$e->getMessage());
        }
    }

    private function deleteStoredPhoto(UserPhoto $photo): void
    {
        $key = $photo->storage_key;

        if (! $key) {
            return;
        }

        $s3Disk = Storage::disk('s3');
        if ($s3Disk->exists($key)) {
            $s3Disk->delete($key);

            return;
        }

        // Legacy fallback for old local photos that may still exist.
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($key)) {
            $publicDisk->delete($key);
        }
    }

    public function setAsProfilePhoto(UserPhoto $photo)
    {
        try {
            // First, unset all other photos as profile
            UserPhoto::where('user_id', $photo->user_id)
                ->where('is_profile_photo', true)
                ->update(['is_profile_photo' => false]);

            // Then set the selected photo as profile
            // Touch the photo to update its timestamp (for cache busting)
            $photo->update([
                'is_profile_photo' => true,
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Set profile photo error: '.$e->getMessage());
            throw new \Exception('Failed to set profile photo: '.$e->getMessage());
        }
    }
}
