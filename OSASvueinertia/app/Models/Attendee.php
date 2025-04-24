<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_application_id',
        'name',
        'course_year_section',
        'signature',
    ];

    public function organizationApplication()
    {
        return $this->belongsTo(OrganizationApplication::class);
    }
}

