<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\SupportingDocument;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SupportingDocumentController extends Controller
{
    /**
     * Get all supporting documents for a specific SO.
     * Queries across both template and submission types for sync.
     * Supports optional owner_id for dean viewing faculty documents.
     */
    public function index(Request $request)
    {
        $request->validate([
            'documentable_type' => 'required|string|in:ipcr_submission,opcr_submission,ipcr_template,opcr_template,ipcr_saved_copy,opcr_saved_copy',
            'documentable_id' => 'required|integer',
            'so_label' => 'required|string',
            'owner_id' => 'nullable|integer',
        ]);

        // Determine which user's documents to fetch
        $userId = auth()->id();
        if ($request->filled('owner_id')) {
            // Only dean/admin users can view another user's documents
            $user = auth()->user();
            if ($user->hasRole('dean') || $user->hasRole('admin')) {
                $userId = (int) $request->owner_id;
            }
            // Faculty users: silently ignore owner_id and use their own ID
        }

        // Query across both template and submission types for sync
        $isIpcr = str_starts_with($request->documentable_type, 'ipcr');
        $familyTypes = $isIpcr
            ? ['ipcr_template', 'ipcr_submission', 'ipcr_saved_copy']
            : ['opcr_template', 'opcr_submission', 'opcr_saved_copy'];

        $documents = SupportingDocument::where('user_id', $userId)
            ->whereIn('documentable_type', $familyTypes)
            ->where('so_label', $request->so_label)
            ->orderBy('created_at', 'desc')
            ->get()
            // Deduplicate same file copied between template/submission.
            ->unique(function (SupportingDocument $doc) {
                return $doc->storage_key ?: $doc->path;
            })
            ->values()
            ->map(fn (SupportingDocument $doc) => $this->mapDocument($doc));

        return response()->json([
            'success' => true,
            'documents' => $documents,
        ]);
    }

    /**
     * Upload a supporting document for a specific SO.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'documentable_type' => 'required|string|in:ipcr_submission,opcr_submission,ipcr_template,opcr_template,ipcr_saved_copy,opcr_saved_copy',
            'documentable_id' => 'required|integer',
            'so_label' => 'required|string',
        ]);

        try {
            $file = $request->file('file');
            $user = auth()->user();
            $timestamp = now()->format('Y-m-d_H-i-s');
            $safeOrigName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeOrigName = $this->sanitizePathSegment($safeOrigName, 'file');
            $extension = strtolower((string) ($file->getClientOriginalExtension() ?: 'bin'));

            // Path: supporting_documents/{user_id}/{type}/{doc_id}/{so_label}/{timestamp}_{filename}.{ext}
            $soFolder = $this->sanitizePathSegment($request->so_label, 'supporting_document');
            $folderPath = "supporting_documents/{$user->id}/{$request->documentable_type}/{$request->documentable_id}/{$soFolder}";
            $objectName = "{$timestamp}_{$safeOrigName}.{$extension}";
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';

            Log::info('Starting supporting document upload', [
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'folder' => $folderPath,
            ]);

            $storedPath = Storage::disk('s3')->putFileAs(
                $folderPath,
                $file,
                $objectName,
                [
                    'ContentType' => $mimeType,
                ]
            );

            if ($storedPath === false) {
                throw new \RuntimeException('Failed to upload document to cloud storage.');
            }

            Log::info('Supporting document upload successful', ['key' => $storedPath]);

            $document = SupportingDocument::create([
                'user_id' => $user->id,
                'documentable_type' => $request->documentable_type,
                'documentable_id' => $request->documentable_id,
                'so_label' => $request->so_label,
                'filename' => $storedPath,
                'path' => $storedPath,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'file_size' => $file->getSize(),
            ]);

            ActivityLogService::log('document_uploaded', 'Uploaded supporting document: '.$document->original_name, $document);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => $this->mapDocument($document),
            ]);
        } catch (\Exception $e) {
            Log::error('Supporting document upload error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a supporting document.
     */
    public function destroy($id)
    {
        $document = SupportingDocument::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            $this->deleteStoredDocument($document);

            $docName = $document->original_name;
            $document->delete();

            ActivityLogService::log('document_deleted', 'Deleted supporting document: '.$docName);

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Supporting document delete error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Delete failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rename a supporting document.
     * Updates both database and cloud object key for cleaner file management.
     */
    public function rename(Request $request, $id)
    {
        $request->validate([
            'original_name' => 'required|string|max:255',
        ]);

        $document = SupportingDocument::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            $newOriginalName = $request->original_name;

            $oldKey = $document->storage_key;

            if ($oldKey) {
                $pathInfo = pathinfo($oldKey);
                $folderPath = isset($pathInfo['dirname']) && $pathInfo['dirname'] !== '.'
                    ? trim((string) $pathInfo['dirname'], '/')
                    : '';
                $existingExtension = strtolower((string) ($pathInfo['extension'] ?? ''));
                $nameWithoutExt = pathinfo($newOriginalName, PATHINFO_FILENAME);
                $safeNewName = $this->sanitizePathSegment($nameWithoutExt, 'document');
                $timestamp = now()->format('Y-m-d_H-i-s');
                $extension = $existingExtension !== ''
                    ? $existingExtension
                    : strtolower((string) (pathinfo($newOriginalName, PATHINFO_EXTENSION) ?: 'bin'));

                $newObjectName = "{$timestamp}_{$safeNewName}.{$extension}";
                $newKey = $folderPath === '' ? $newObjectName : $folderPath.'/'.$newObjectName;

                if ($oldKey !== $newKey && ! $this->moveStoredDocument($oldKey, $newKey)) {
                    throw new \RuntimeException('Unable to rename the stored document object.');
                }

                $document->filename = $newKey;
                $document->path = $newKey;
                $document->original_name = $newOriginalName;
                $document->save();

                Log::info('Document renamed in cloud storage', [
                    'old_key' => $oldKey,
                    'new_key' => $newKey,
                ]);
            } else {
                // Legacy URL-based record: keep storage reference untouched and only rename display name.
                $document->original_name = $newOriginalName;
                $document->save();
            }

            ActivityLogService::log('document_renamed', 'Renamed supporting document to: '.$document->original_name, $document);

            return response()->json([
                'success' => true,
                'message' => 'Document renamed successfully.',
                'document' => [
                    'id' => $document->id,
                    'original_name' => $document->original_name,
                    'path' => $document->file_url,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Supporting document rename error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Rename failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download a supporting document with proper filename.
     * Allows faculty to download their own docs, and deans to download faculty docs.
     */
    public function download($id)
    {
        $document = SupportingDocument::findOrFail($id);

        // Check authorization: owner or dean role
        $user = auth()->user();
        $canDownload = $document->user_id === $user->id || $user->hasRole('dean') || $user->hasRole('admin');

        if (! $canDownload) {
            abort(403, 'Unauthorized');
        }

        try {
            $storageKey = $document->storage_key;

            if ($storageKey) {
                $stream = $this->openDocumentStream($storageKey);

                if ($stream === false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to read file from storage.',
                    ], 500);
                }

                ActivityLogService::log('document_downloaded', 'Downloaded supporting document: '.$document->original_name, $document);

                return response()->streamDownload(function () use ($stream) {
                    fpassthru($stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }, $document->original_name, [
                    'Content-Type' => $document->mime_type ?? 'application/octet-stream',
                ]);
            }

            $remoteUrl = trim((string) $document->path);
            if ($remoteUrl === '' || ! $this->isAllowedRemoteUrl($remoteUrl)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid document URL.',
                ], 400);
            }

            $response = Http::timeout(15)->get($remoteUrl);

            if (! $response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch file from storage.',
                ], 500);
            }

            $fileContent = $response->body();

            ActivityLogService::log('document_downloaded', 'Downloaded supporting document: '.$document->original_name, $document);

            // Return file with download headers
            return response($fileContent)
                ->header('Content-Type', $document->mime_type ?? 'application/octet-stream')
                ->header('Content-Disposition', 'attachment; filename="'.$document->original_name.'"')
                ->header('Content-Length', strlen($fileContent));
        } catch (\Exception $e) {
            Log::error('Supporting document download error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Download failed: '.$e->getMessage(),
            ], 500);
        }
    }

    private function mapDocument(SupportingDocument $document): array
    {
        return [
            'id' => $document->id,
            'original_name' => $document->original_name,
            'path' => $document->file_url,
            'mime_type' => $document->mime_type,
            'file_size_human' => $document->file_size_human,
            'created_at' => $document->created_at->format('M d, Y h:i A'),
        ];
    }

    private function sanitizePathSegment(string $value, string $fallback): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower(trim($value))) ?? '';
        $sanitized = trim($sanitized, '_-');

        return $sanitized !== '' ? $sanitized : $fallback;
    }

    private function deleteStoredDocument(SupportingDocument $document): void
    {
        $key = $document->storage_key;

        if (! $key) {
            return;
        }

        $s3Disk = Storage::disk('s3');
        if ($s3Disk->exists($key)) {
            $s3Disk->delete($key);

            return;
        }

        // Legacy fallback for older local records.
        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($key)) {
            $publicDisk->delete($key);
        }
    }

    private function moveStoredDocument(string $oldKey, string $newKey): bool
    {
        $s3Disk = Storage::disk('s3');
        if ($s3Disk->exists($oldKey)) {
            return $s3Disk->move($oldKey, $newKey);
        }

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($oldKey)) {
            return $publicDisk->move($oldKey, $newKey);
        }

        return false;
    }

    private function openDocumentStream(string $key)
    {
        $s3Disk = Storage::disk('s3');
        if ($s3Disk->exists($key)) {
            return $s3Disk->readStream($key);
        }

        $publicDisk = Storage::disk('public');
        if ($publicDisk->exists($key)) {
            return $publicDisk->readStream($key);
        }

        return false;
    }

    private function isAllowedRemoteUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }

        $allowedHosts = ['res.cloudinary.com', 'cloudinary.com'];

        foreach (['filesystems.disks.s3.url', 'filesystems.disks.s3.endpoint'] as $configKey) {
            $configuredUrl = trim((string) config($configKey, ''));
            if ($configuredUrl === '') {
                continue;
            }

            $configuredHost = strtolower((string) parse_url($configuredUrl, PHP_URL_HOST));
            if ($configuredHost !== '') {
                $allowedHosts[] = $configuredHost;
            }
        }

        $allowedHosts = array_values(array_unique($allowedHosts));

        if (in_array($host, $allowedHosts, true)) {
            return true;
        }

        foreach ($allowedHosts as $allowedHost) {
            if ($allowedHost !== '' && str_ends_with($host, '.'.$allowedHost)) {
                return true;
            }
        }

        return false;
    }
}
