<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentOrg extends Model
{
    use HasFactory;

    protected $fillable = [
        'college_id',
        'name',
        'acronym',
        'description',
        'logo_path',
        'status',
    ];

    /**
     * Get the college that owns the student organization.
     */
    public function college()
    {
        return $this->belongsTo(College::class);
    }
}