<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Services\IpcrImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IpcrImportController extends Controller
{
    /**
     * Parse an uploaded IPCR/OPCR xlsx file and return structured data.
     */
    public function import(Request $request, IpcrImportService $importService): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        $file = $request->file('file');
        $tempPath = $file->getRealPath();

        try {
            $data = $importService->parse($tempPath);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse the uploaded file. Please ensure it follows the IPCR/OPCR layout.',
            ], 422);
        }
    }
}
