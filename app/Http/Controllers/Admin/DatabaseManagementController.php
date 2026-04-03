<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\DatabaseBackupService;
use App\Services\ActivityLogService;
use Symfony\Component\Process\Process;

class DatabaseManagementController extends Controller
{
    protected $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display backup listing and database stats.
     */
    public function index()
    {
        // Get database stats
        $dbName = config('database.connections.mysql.database');

        // Get database size
        $sizeResult = DB::select("SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
            FROM information_schema.tables
            WHERE table_schema = ?", [$dbName]);
        $dbSize = $sizeResult[0]->size_mb ?? 0;

        // Get table count
        $tableCount = count(DB::select("SHOW TABLES"));

        // Get backups from storage
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $allBackups = collect(glob($backupDir . '/*.sql'))
            ->map(function ($path) {
                return [
                    'filename' => basename($path),
                    'size' => filesize($path),
                    'size_formatted' => $this->formatBytes(filesize($path)),
                    'created_at' => date('M d, Y H:i', filemtime($path)),
                    'timestamp' => filemtime($path),
                ];
            })
            ->sortByDesc('timestamp')
            ->values();

        // Manual Pagination
        $page = request()->get('page', 1);
        $perPage = 10;
        $total = $allBackups->count();
        
        $paginatedItems = $allBackups->slice(($page - 1) * $perPage, $perPage)->values();

        $backups = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedItems,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $backupCount = $total;

        // Load Settings
        $settingsPath = storage_path('app/backup_settings.json');
        $settings = file_exists($settingsPath) 
            ? json_decode(file_get_contents($settingsPath), true) 
            : ['enabled' => false, 'frequency' => 'daily', 'time' => '00:00'];

        return view('admin.database.index', compact('dbSize', 'tableCount', 'backups', 'backupCount', 'dbName', 'settings'));
    }

    /**
     * Create a database backup.
     */
    public function backup()
    {
        $result = $this->backupService->createBackup();

        if ($result['success']) {
            ActivityLogService::log('backup_created', 'Created database backup');
            return redirect()->route('admin.database.index')
                ->with('success', $result['message']);
        } else {
            return redirect()->route('admin.database.index')
                ->with('error', $result['message']);
        }
    }

    /**
     * Download a backup file.
     */
    public function download($filename)
    {
        $filePath = storage_path('app/backups/' . basename($filename));

        if (!file_exists($filePath)) {
            return redirect()->route('admin.database.index')
                ->with('error', 'Backup file not found.');
        }

        return response()->download($filePath);
    }

    /**
     * Restore database from a backup file.
     */
    public function restore($filename)
    {
        try {
            $filePath = storage_path('app/backups/' . basename($filename));

            if (!file_exists($filePath)) {
                return redirect()->route('admin.database.index')
                    ->with('error', 'Backup file not found.');
            }

            $sql = file_get_contents($filePath);
            if ($sql === false) {
                return redirect()->route('admin.database.index')
                    ->with('error', 'Restore failed: Unable to read SQL backup file.');
            }

            $statements = $this->parseSqlStatements($sql);
            $filteredStatements = $this->filterRestorableStatements($statements);
            $skippedCount = count($statements) - count($filteredStatements);

            if (empty($filteredStatements)) {
                return redirect()->route('admin.database.index')
                    ->with('error', 'Restore failed: The selected SQL file has no executable statements after safety filtering.');
            }

            $this->restoreUsingSqlStatements($filteredStatements);

            ActivityLogService::log('backup_restored', 'Restored database from backup: ' . $filename);

            $message = "Database restored successfully from: {$filename}";
            if ($skippedCount > 0) {
                $message .= " ({$skippedCount} session/cache statements were skipped to keep active logins.)";
            }

            return redirect()->route('admin.database.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->route('admin.database.index')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Restore SQL statements through PDO.
     */
    private function restoreUsingSqlStatements(array $statements): void
    {
        $connection = DB::connection('mysql');
        $connection->unprepared('SET FOREIGN_KEY_CHECKS=0;');

        try {
            foreach ($statements as $statement) {
                $connection->unprepared($statement);
            }
        } finally {
            $connection->unprepared('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Remove statements that target volatile runtime tables so active logins are preserved.
     */
    private function filterRestorableStatements(array $statements): array
    {
        $excludedTables = $this->getRestoreExcludedTables();

        return array_values(array_filter($statements, function ($statement) use ($excludedTables) {
            $sql = trim((string) $statement);

            if ($sql === '') {
                return false;
            }

            foreach ($excludedTables as $table) {
                if ($table !== '' && $this->statementReferencesTable($sql, $table)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Resolve restore-excluded tables from runtime config.
     */
    private function getRestoreExcludedTables(): array
    {
        $candidates = [
            config('session.table', 'sessions'),
            config('cache.stores.database.table', 'cache'),
            config('cache.stores.database.lock_table', 'cache_locks'),
        ];

        $normalized = array_map(function ($table) {
            return strtolower(trim((string) $table));
        }, $candidates);

        return array_values(array_unique(array_filter($normalized)));
    }

    /**
     * Detect whether a SQL statement targets a given table name.
     */
    private function statementReferencesTable(string $statement, string $table): bool
    {
        $escaped = preg_quote($table, '/');

        $pattern = '/\b(?:from|into|update|join|table|truncate|alter\s+table|create\s+table|drop\s+table|delete\s+from|replace\s+into|lock\s+tables)\s+`?(?:[a-zA-Z0-9_]+`?\.)?`?' . $escaped . '`?\b/i';

        if (preg_match($pattern, $statement)) {
            return true;
        }

        return false;
    }

    /**
     * Parse a SQL script into executable statements with delimiter support.
     */
    private function parseSqlStatements(string $sql): array
    {
        $statements = [];
        $buffer = '';
        $delimiter = ';';

        $lines = preg_split('/\r\n|\r|\n/', $sql) ?: [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if ($trimmed === '--' || str_starts_with($trimmed, '-- ') || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (preg_match('/^DELIMITER\s+(.+)$/i', $trimmed, $match)) {
                $delimiter = trim($match[1]);
                continue;
            }

            if (str_starts_with($trimmed, '/*') && !str_starts_with($trimmed, '/*!') && str_ends_with($trimmed, '*/')) {
                continue;
            }

            $buffer .= $line . PHP_EOL;

            if ($this->endsWithSqlDelimiter($buffer, $delimiter)) {
                $statement = trim($this->stripSqlDelimiter($buffer, $delimiter));

                if ($statement !== '') {
                    $statements[] = $statement;
                }

                $buffer = '';
            }
        }

        $tail = trim($buffer);
        if ($tail !== '') {
            $statements[] = $tail;
        }

        return $statements;
    }

    /**
     * Check whether a SQL buffer ends with the active delimiter outside quoted strings.
     */
    private function endsWithSqlDelimiter(string $buffer, string $delimiter): bool
    {
        $trimmed = rtrim($buffer);

        if ($delimiter === '' || !str_ends_with($trimmed, $delimiter)) {
            return false;
        }

        $withoutDelimiter = substr($trimmed, 0, strlen($trimmed) - strlen($delimiter));

        return $this->hasBalancedQuotes($withoutDelimiter);
    }

    /**
     * Remove trailing SQL delimiter from a parsed statement.
     */
    private function stripSqlDelimiter(string $buffer, string $delimiter): string
    {
        $trimmed = rtrim($buffer);

        if ($delimiter !== '' && str_ends_with($trimmed, $delimiter)) {
            return substr($trimmed, 0, strlen($trimmed) - strlen($delimiter));
        }

        return $trimmed;
    }

    /**
     * Determine if quote scopes are balanced in SQL text.
     */
    private function hasBalancedQuotes(string $sql): bool
    {
        $inSingle = false;
        $inDouble = false;
        $inBacktick = false;
        $escaped = false;

        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($inSingle || $inDouble) {
                if ($char === '\\' && !$escaped) {
                    $escaped = true;
                    continue;
                }

                if ($inSingle && $char === "'" && !$escaped) {
                    $inSingle = false;
                } elseif ($inDouble && $char === '"' && !$escaped) {
                    $inDouble = false;
                }

                $escaped = false;
                continue;
            }

            if ($inBacktick) {
                if ($char === '`') {
                    $inBacktick = false;
                }
                continue;
            }

            if ($char === "'") {
                $inSingle = true;
                continue;
            }

            if ($char === '"') {
                $inDouble = true;
                continue;
            }

            if ($char === '`') {
                $inBacktick = true;
            }
        }

        return !$inSingle && !$inDouble && !$inBacktick;
    }

    /**
     * Delete a backup file.
     */
    public function delete($filename)
    {
        $filePath = storage_path('app/backups/' . basename($filename));

        if (!file_exists($filePath)) {
            return redirect()->route('admin.database.index')
                ->with('error', 'Backup file not found.');
        }

        unlink($filePath);

        ActivityLogService::log('backup_deleted', 'Deleted backup file: ' . $filename);

        return redirect()->route('admin.database.index')
            ->with('success', "Backup deleted: {$filename}");
    }

    /**
     * Upload an external SQL file as a backup.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'sql_file' => 'required|file|max:102400', // 100MB max
        ]);

        $file = $request->file('sql_file');
        $originalName = $file->getClientOriginalName();

        // Validate it's a .sql file
        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return redirect()->route('admin.database.index')
                ->with('error', 'Only .sql files are allowed.');
        }

        // Validate MIME type (text/plain or application/sql are expected for .sql files)
        $allowedMimes = ['text/plain', 'application/sql', 'application/x-sql', 'text/x-sql', 'application/octet-stream'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return redirect()->route('admin.database.index')
                ->with('error', 'Invalid file type. Only SQL files are allowed.');
        }

        // Validate file content: check first bytes for SQL-like content
        $firstBytes = file_get_contents($file->getRealPath(), false, null, 0, 4096);
        if ($firstBytes === false) {
            return redirect()->route('admin.database.index')
                ->with('error', 'Unable to read uploaded file.');
        }

        // Check for common SQL markers (mysqldump headers, SQL statements, comments)
        $hasSqlContent = preg_match('/^\s*(--|\/\*|CREATE|INSERT|DROP|ALTER|SET|USE|BEGIN|START|LOCK|UNLOCK|DELIMITER)/mi', $firstBytes);
        if (!$hasSqlContent) {
            return redirect()->route('admin.database.index')
                ->with('error', 'File does not appear to contain valid SQL content.');
        }

        // Sanitize filename to prevent path traversal
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($originalName, PATHINFO_FILENAME));

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Avoid filename collision (using sanitized name)
        $filename = $safeName . '_uploaded_' . date('His') . '.sql';
        $file->move($backupDir, $filename);

        ActivityLogService::log('backup_uploaded', 'Uploaded backup file: ' . $filename);

        return redirect()->route('admin.database.index')
            ->with('success', "Backup uploaded: {$filename}");
    }

    /**
     * Update backup settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'enabled' => 'nullable|in:on,1,true',
            'frequency' => 'required|in:daily,weekly,monthly',
            'time' => 'required|date_format:H:i',
        ]);

        $settings = [
            'enabled' => $request->has('enabled'),
            'frequency' => $validated['frequency'],
            'time' => $validated['time'],
        ];

        Storage::put('backup_settings.json', json_encode($settings, JSON_PRETTY_PRINT));

        ActivityLogService::log('settings_updated', 'Updated backup settings');

        return redirect()->route('admin.database.index')
            ->with('success', 'Backup settings updated successfully.');
    }

    /**
     * Find mysql binary path.
     */
    private function findMysql(): string
    {
        $paths = [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            'C:\\xampp\\mysql\\bin\\mysql',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            '/usr/bin/mariadb',
            '/usr/local/bin/mariadb',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        foreach (['mysql', 'mariadb'] as $command) {
            if ($this->isCommandAvailable($command)) {
                return $command;
            }
        }

        throw new \RuntimeException(
            'No database client binary found. Install mysql or mariadb in the runtime image.'
        );
    }

    /**
     * Detect whether a command is available in PATH.
     */
    private function isCommandAvailable(string $command): bool
    {
        $lookup = DIRECTORY_SEPARATOR === '\\'
            ? ['where', $command]
            : ['which', $command];

        $process = new Process($lookup);
        $process->run();

        return $process->isSuccessful() && trim($process->getOutput()) !== '';
    }

    /**
     * Build a useful process error message for UI display.
     */
    private function formatProcessError(Process $process, string $fallback): string
    {
        $stderr = trim($process->getErrorOutput());
        if ($stderr !== '') {
            return $stderr;
        }

        $stdout = trim($process->getOutput());
        if ($stdout !== '') {
            return $stdout;
        }

        $exitCode = $process->getExitCode();
        if ($exitCode !== null) {
            return $fallback . ' (exit code ' . $exitCode . ')';
        }

        return $fallback;
    }

    /**
     * Format bytes to human readable.
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
