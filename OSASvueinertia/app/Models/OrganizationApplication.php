<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Activity;


class OrganizationApplication extends Model
{
    use HasFactory;

    protected $fillable = [

        'user_id',
        'form_type',
        'organization_name',
        'president_name',
        'adviser_name',
        'adviser_prefix',
        'adviser_suffix',
        'dean_name',
        'dean_prefix',
        'dean_suffix',
        'coordinator_name',
        'status',
        'is_archived',
        'archived_at',
        'archived_by',
        'academic_year_archived',
        'college',
        'academic_year_start',
        'academic_year_end',
        'application_date',
        'director_name',

        // New fields from the commitment form
        'adviser_signature',
        'adviser_college',
        'adviser_rank',
        'adviser_address',
        'adviser_contact',
        'form_date',

        // New fields for the plan of activities form
        'secretary_name',

        // New fields for the list of members form
        'semester',
        'second_adviser',
        'second_adviser_prefix',
        'second_adviser_suffix',

         // Student Activity Attendance Sheet fields
         'activity_name',
         'activity_date',
         'activity_title',
         'venue',
         'date',
         'time',
         'ratings',

         // New date and time range fields for evaluation form
         'date_start',
         'date_end',
         'time_start',
         'time_end',
        'comments_suggestions',

        'accomplishment_report_path',
        'narrative_report_path',
        'bylaws_path',
        'financial_report_path',
        'event_letter_path',
        'signed_document_path',
        'signed_document_link',

    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
        'ratings' => 'array',
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function officers()
    {
        return $this->hasMany(Officer::class);
    }

    public function studentCertifications()
    {
        return $this->hasMany(StudentCertification::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    // Scope to get only non-archived applications
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    // Scope to get only archived applications
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

}

