<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomPasswordResetNotification;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'profile_photo_path',
        'college_id',
        'parent_organization_id',
        'description',
        'last_name_change_at',
        'status',
        'social_links',
    ];

    protected $appends = ['profile_photo_url'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_name_change_at' => 'datetime',
        'social_links' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole($role)
    {
        return $this->role->slug === $role;
    }

    public function isAdmin()
    {
        return $this->role === 'admin' || $this->hasRole('admin');
    }

    public function applications()
    {
        return $this->hasMany(OrganizationApplication::class);
    }

    public function organizationApplications()
    {
        return $this->hasMany(OrganizationApplication::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    /**
     * Get the parent organization.
     */
    public function parentOrganization()
    {
        return $this->belongsTo(User::class, 'parent_organization_id');
    }

    /**
     * Get the sub-organizations (children).
     */
    public function subOrganizations()
    {
        return $this->hasMany(User::class, 'parent_organization_id');
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->belongsToMany(Notification::class, 'user_notifications')
            ->withPivot('is_read')
            ->withTimestamps();
    }
    
    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute()
    {
        return $this->belongsToMany(Notification::class, 'user_notifications')
            ->wherePivot('is_read', false)
            ->count();
    }

    /**
     * Get the user's saved form data.
     */
    public function userFormData()
    {
        return $this->hasMany(UserFormData::class);
    }

    /**
     * Get the URL for the user's profile photo.
     */
    public function getProfilePhotoUrlAttribute()
    {
        if (!$this->profile_photo_path) {
            return null;
        }
        
        // Check if file exists before generating URL
        if (Storage::disk('public')->exists($this->profile_photo_path)) {
            return Storage::disk('public')->url($this->profile_photo_path);
        }
        
        // If file doesn't exist, return null to show default avatar
        return null;
    }

    public function latestApplication()
    {
        return $this->hasOne(OrganizationApplication::class)->latestOfMany();
    }

    public function latestApprovedApplication()
    {
        return $this->hasOne(OrganizationApplication::class)
            ->where('status', 'Approved')
            ->latestOfMany();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomPasswordResetNotification($token));
    }
}
