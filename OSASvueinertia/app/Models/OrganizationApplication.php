<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationApplication extends Model
{
    use HasFactory;

    protected $fillable = [
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
        'director_name'
    ];
}

