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
        'dean_name',
        'coordinator_name',
        'status',
        'college',
        'academic_year_start',
        'academic_year_end',
        'chairperson_name',
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

        // New fields for the certification form
        
        'student_name' ,
        'course_year_section' ,
        'position_rank',
        'is_bonafide' ,
        'is_not_academic_probation',
        'is_not_disciplinary_probation',
        'has_position',

        // New fields for the list of members form
        'semester',
        'second_adviser',

         // Student Activity Attendance Sheet fields
         'activity_name',
         'activity_date',



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

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}

