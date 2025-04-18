<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_name',
        'student_number',
        'course_year_section',
        'photo_path',
        
    ];

    public function organizationApplication()
    {
        return $this->belongsTo(OrganizationApplication::class);
    }
}
