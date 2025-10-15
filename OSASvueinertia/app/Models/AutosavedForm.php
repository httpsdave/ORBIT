<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutosavedForm extends Model
{
    protected $fillable = [
        'user_id',
        'form_type',
        'form_data',
    ];

    protected $casts = [
        'form_data' => 'array',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the autosaved form.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
