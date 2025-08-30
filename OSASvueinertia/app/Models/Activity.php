<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'objective',
        'name',
        'description',
        'persons_involved',
        'target_date',
        'budget',
    ];
    protected $casts = [
        'target_date' => 'date:Y-m-d'
    ];

    public function organizationApplication()
    {
        return $this->belongsTo(OrganizationApplication::class);
    }
}
