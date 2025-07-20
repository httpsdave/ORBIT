<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_application_id',
        'student_name',
        'course_year_section',
        'position_rank',
        'is_bonafide',
        'is_not_academic_probation',
        'is_not_disciplinary_probation',
        'has_position',
        'certification_date',
    ];

    protected $casts = [
        'is_bonafide' => 'boolean',
        'is_not_academic_probation' => 'boolean',
        'is_not_disciplinary_probation' => 'boolean',
        'has_position' => 'boolean',
        'certification_date' => 'date',
    ];

    public function organizationApplication()
    {
        return $this->belongsTo(OrganizationApplication::class);
    }
} 