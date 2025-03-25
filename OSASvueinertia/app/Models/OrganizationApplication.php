<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizationApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_name',
        'president_name',
        'application_date',
        'requirements',
        'adviser_name',
        'dean_name',
        'coordinator_name',
        'director_name',
        'status',
        'form_type',
    ];
}

