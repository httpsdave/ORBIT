<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Download a complete backup of the database and user files
     * Memory-efficient version that works for both local and production
     */
    public function downloadBackup(Request $request)
    {
        // Try to set high limits (will fail silently in restricted environments)
        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', '0'); // No timeout
        @set_time_limit(0); // Alternative timeout setting
        
        // Disable output buffering for streaming
        if (ob_get_level()) ob_end_clean();
        
        try {
            $timestamp = now()->format('Y-m-d_His');
            $zipFilename = "osas_backup_{$timestamp}.zip";
            $zipPath = storage_path("app/{$zipFilename}");
            
            // Create ZIP file
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Could not create ZIP file");
            }
            
            // 1. Export and add database
            $sqlFile = $this->exportDatabaseToFile($timestamp);
            if (File::exists($sqlFile)) {
                $zip->addFile($sqlFile, "database_backup_{$timestamp}.sql");
            }
            
            // 2. Add uploaded files (with limits to prevent memory issues)
            $this->addFilesToZip($zip, 'storage/app/public', 'uploaded_files');
            
            // Close the ZIP
            $zip->close();
            
            // Clean up SQL file
            if (File::exists($sqlFile)) {
                @unlink($sqlFile);
            }
            
            // Stream the ZIP file in chunks to avoid memory issues
            return response()->stream(function() use ($zipPath) {
                $stream = fopen($zipPath, 'rb');
                if ($stream === false) {
                    throw new \Exception("Could not open ZIP file for streaming");
                }
                
                while (!feof($stream)) {
                    $chunk = fread($stream, 8192); // Read 8KB at a time
                    if ($chunk === false) break;
                    echo $chunk;
                    
                    // Flush output buffers
                    if (ob_get_level() > 0) {
                        @ob_flush();
                    }
                    @flush();
                }
                fclose($stream);
                
                // Delete after streaming
                @unlink($zipPath);
            }, 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zipFilename . '"',
                'Content-Length' => filesize($zipPath),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
                'X-Accel-Buffering' => 'no', // Disable nginx buffering
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Backup download failed: ' . $e->getMessage());
            
            // Clean up temp files
            if (isset($sqlFile) && File::exists($sqlFile)) {
                @unlink($sqlFile);
            }
            if (isset($zipPath) && File::exists($zipPath)) {
                @unlink($zipPath);
            }
            
            return redirect()->route('profile.edit')->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Export database to a temporary SQL file
     */
    private function exportDatabaseToFile($timestamp)
    {
        $sqlFile = storage_path("app/temp_db_backup_{$timestamp}.sql");
        
        $dbType = config('database.default');
        $dbConfig = config("database.connections.{$dbType}");
        
        if ($dbType === 'mysql') {
            $host = $dbConfig['host'];
            $port = $dbConfig['port'] ?? 3306;
            $database = $dbConfig['database'];
            $username = $dbConfig['username'];
            $password = $dbConfig['password'];
            
            // Try mysqldump first (may not be available in all production environments)
            $mysqldumpPath = $this->findMysqldump();
            
            if ($mysqldumpPath) {
                $command = sprintf(
                    '%s --host=%s --port=%s --user=%s --password=%s --skip-comments --compact %s > %s 2>&1',
                    $mysqldumpPath,
                    escapeshellarg($host),
                    escapeshellarg($port),
                    escapeshellarg($username),
                    escapeshellarg($password),
                    escapeshellarg($database),
                    escapeshellarg($sqlFile)
                );
                
                exec($command, $output, $returnVar);
                
                // If mysqldump succeeds, we're done
                if ($returnVar === 0 && File::exists($sqlFile) && File::size($sqlFile) > 100) {
                    return $sqlFile;
                }
            }
            
            // Fallback to manual export (works everywhere)
            $this->manualDatabaseExport($sqlFile);
        } else {
            $this->manualDatabaseExport($sqlFile);
        }
        
        return $sqlFile;
    }
    
    /**
     * Try to find mysqldump executable in common locations
     */
    private function findMysqldump()
    {
        // Common paths for mysqldump
        $possiblePaths = [
            'mysqldump', // In PATH
            '/usr/bin/mysqldump', // Linux
            '/usr/local/bin/mysqldump', // Linux/Mac
            'C:\\xampp\\mysql\\bin\\mysqldump.exe', // XAMPP Windows
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe', // MySQL Windows
        ];
        
        foreach ($possiblePaths as $path) {
            // Test if command exists
            $test = @shell_exec(sprintf('%s --version 2>&1', $path));
            if ($test && stripos($test, 'mysqldump') !== false) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Manual database export - writes directly to file to save memory
     */
    private function manualDatabaseExport($sqlFile)
    {
        $handle = fopen($sqlFile, 'w');
        
        fwrite($handle, "-- Database Backup\n");
        fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
        
        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $tableKey = "Tables_in_{$dbName}";
        
        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            
            // Get CREATE TABLE statement
            $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
            fwrite($handle, "-- Table: {$tableName}\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
            fwrite($handle, $createTable->{'Create Table'} . ";\n\n");
            
            // Get table data in chunks to avoid memory issues
            $chunkSize = 1000;
            $offset = 0;
            
            do {
                $rows = DB::table($tableName)->offset($offset)->limit($chunkSize)->get();
                
                if ($rows->count() > 0) {
                    if ($offset === 0) {
                        fwrite($handle, "-- Data for table: {$tableName}\n");
                    }
                    
                    foreach ($rows as $row) {
                        $row = (array) $row;
                        $values = array_map(function($value) {
                            if (is_null($value)) {
                                return 'NULL';
                            }
                            return "'" . addslashes($value) . "'";
                        }, $row);
                        
                        $columns = array_keys($row);
                        $sql = sprintf(
                            "INSERT INTO `%s` (`%s`) VALUES (%s);\n",
                            $tableName,
                            implode('`, `', $columns),
                            implode(', ', $values)
                        );
                        fwrite($handle, $sql);
                    }
                }
                
                $offset += $chunkSize;
            } while ($rows->count() === $chunkSize);
            
            fwrite($handle, "\n");
        }
        
        fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        fclose($handle);
    }
    
    /**
     * Add ALL files to ZIP without any size limits
     * Uses addFile() which references files by path instead of loading content
     */
    private function addFilesToZip($zip, $sourcePath, $zipPath)
    {
        $fullSourcePath = base_path($sourcePath);
        
        if (!File::exists($fullSourcePath)) {
            \Log::info("Source path does not exist: {$fullSourcePath}");
            return;
        }
        
        $fileCount = 0;
        $totalSize = 0;
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullSourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $fileSize = $file->getSize();
                    
                    $relativePath = str_replace($fullSourcePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $zipFilePath = $zipPath . '/' . str_replace('\\', '/', $relativePath);
                    
                    // Add file reference to ZIP (doesn't load into memory)
                    if ($zip->addFile($file->getPathname(), $zipFilePath)) {
                        $fileCount++;
                        $totalSize += $fileSize;
                    }
                }
            }
            
            \Log::info("Added {$fileCount} files to backup (" . round($totalSize / 1024 / 1024, 2) . " MB)");
            
        } catch (\Exception $e) {
            \Log::error("Error adding files to ZIP: " . $e->getMessage());
        }
    }
}
