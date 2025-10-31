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
        'reviewed_by',
        'reviewed_at',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the organization application that owns the report.
     */
    public function organizationApplication()
    {
        return $this->belongsTo(OrganizationApplication::class);
    }

    /**
     * Get the user who reviewed this report.
     */
    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if the report has been submitted
     */
    public function isSubmitted()
    {
        return !in_array($this->status, ['Pending', 'pending']) && !is_null($this->submitted_at);
    }

    /**
     * Get the status color class for UI
     */
    public function getStatusColorAttribute()
    {
        return match(strtolower($this->status)) {
            'pending' => 'text-gray-500 bg-gray-100',
            'approved' => 'text-green-600 bg-green-100',
            'disapproved', 'rejected' => 'text-red-600 bg-red-100',
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
            'LSPU-OSAS-SF-EVAL' => 'Evaluation Summary',
            'LSPU-OSAS-SF-009' => 'Activity Attendance Sheet',
            'LSPU-OSAS-SF-STATUS-REPORT' => 'Status Report',
            default => $this->report_type
        };
    }
}