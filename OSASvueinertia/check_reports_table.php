<?php

// Simple script to check if the activity_reports table exists and has the correct structure
// Run this in production to debug the issue

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Check if the table exists
    $exists = Schema::hasTable('activity_reports');
    echo "Table 'activity_reports' exists: " . ($exists ? 'YES' : 'NO') . "\n";
    
    if ($exists) {
        // Check columns
        $columns = Schema::getColumnListing('activity_reports');
        echo "Columns: " . implode(', ', $columns) . "\n";
        
        // Check if status column has the right enum values
        $statusColumn = DB::select("SHOW COLUMNS FROM activity_reports WHERE Field = 'status'")[0] ?? null;
        if ($statusColumn) {
            echo "Status column type: " . $statusColumn->Type . "\n";
        }
        
        // Check count of records
        $count = DB::table('activity_reports')->count();
        echo "Total records: $count\n";
    }
    
    // Test creating a dummy record (don't commit)
    DB::beginTransaction();
    try {
        $testInsert = DB::table('activity_reports')->insert([
            'organization_application_id' => 1,
            'activity_page_number' => 1,
            'report_type' => 'LSPU-OSAS-SF-FINANCIAL',
            'file_path' => 'test/path.pdf',
            'original_filename' => 'test.pdf',
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now()
        ]);
        echo "Test insert: " . ($testInsert ? 'SUCCESS' : 'FAILED') . "\n";
        DB::rollBack(); // Don't actually save this
    } catch (Exception $e) {
        echo "Test insert ERROR: " . $e->getMessage() . "\n";
        DB::rollBack();
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}