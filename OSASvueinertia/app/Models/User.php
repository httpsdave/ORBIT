<?php

// app/Models/User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomPasswordResetNotification;

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
        'description',
        'last_name_change_at',
        'status',
        'coordinator_name',
        'director_name',
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
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : null;
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
