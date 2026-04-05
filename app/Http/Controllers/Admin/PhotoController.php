<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserPhoto;
use App\Services\PhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function __construct(private PhotoService $photoService) {}

    /**
     * Upload photo for user.
     */
    public function upload(Request $request, User $user): JsonResponse
    {
        try {
            $request->validate([
                'photo' => 'required|image|mimes:jpeg,png,gif,webp|max:5120', // 5MB
            ]);

            if (! $request->hasFile('photo')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No file uploaded',
                ], 422);
            }

            $photo = $this->photoService->uploadPhoto($request->file('photo'), $user);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'photo' => [
                    'id' => $photo->id,
                    'url' => $photo->photo_url,
                    'filename' => $photo->filename,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a specific photo.
     */
    public function delete(Request $request, User $user, UserPhoto $photo): JsonResponse
    {
        try {
            // Check if photo belongs to this user
            if ($photo->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $this->photoService->deletePhoto($photo);

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Set photo as profile photo.
     */
    public function setAsProfile(Request $request, User $user, UserPhoto $photo): JsonResponse
    {
        try {
            // Check if photo belongs to this user
            if ($photo->user_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $this->photoService->setAsProfilePhoto($photo);

            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get all photos for a user.
     */
    public function getUserPhotos(Request $request, User $user): JsonResponse
    {
        try {
            $photos = $user->photos()->get();

            $photosData = $photos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'url' => $photo->photo_url,
                    'filename' => $photo->filename,
                    'is_profile' => $photo->is_profile_photo,
                ];
            });

            return response()->json([
                'success' => true,
                'photos' => $photosData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
