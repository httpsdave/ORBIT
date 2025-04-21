<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Officer extends Model
{
    protected $fillable = [
        'student_name',
        'position',
        'student_number',
        'photo_path',
        'organization_application_id'
    ];

    public function application()
    {
        return $this->belongsTo(OrganizationApplication::class, 'organization_application_id');
    }
}