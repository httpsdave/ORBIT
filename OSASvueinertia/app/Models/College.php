<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'acronym',
        'description',
        'logo_path',
    ];

    /**
     * Get the student organizations for the college.
     */
    public function studentOrgs()
    {
        return $this->hasMany(StudentOrg::class);
    }

    /**
     * Get the users for the college.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}