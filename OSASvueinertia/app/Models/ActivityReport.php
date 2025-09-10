<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_application_id',
        'activity_page_number',
        'report_type',
        'file_path',
        'original_filename',
        'status',
        'feedback',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    /**
     * Get the organization application that owns the report.
     */
    public function organizationApplication()
    {
        return $this->belongsTo(OrganizationApplication::class);
    }

    /**
     * Check if the report has been submitted
     */
    public function isSubmitted()
    {
        return $this->status !== 'pending' && !is_null($this->submitted_at);
    }

    /**
     * Get the status color class for UI
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'text-gray-500 bg-gray-100',
            'submitted' => 'text-blue-600 bg-blue-100',
            'approved' => 'text-green-600 bg-green-100',
            'rejected' => 'text-red-600 bg-red-100',
            default => 'text-gray-500 bg-gray-100'
        };
    }

    /**
     * Get the display name for report type
     */
    public function getReportTypeDisplayAttribute()
    {
        return match($this->report_type) {
            'LSPU-OSAS-SF-FINANCIAL' => 'Financial Report',
            'LSPU-OSAS-SF-NARRATIVE' => 'Narrative Report',
            'LSPU-OSAS-SF-ACCOMPLISHMENT' => 'Accomplishment Report',
            default => $this->report_type
        };
    }
}