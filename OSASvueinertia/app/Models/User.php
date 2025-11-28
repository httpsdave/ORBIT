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
        'has_seen_tutorial',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_recovery_codes',
    ];

    protected $appends = ['profile_photo_url'];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'last_name_change_at' => 'datetime',
        'social_links' => 'array',
        'has_seen_tutorial' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
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
     * Get all organization IDs that this user can view applications from.
     * Includes: self, parent organization, and sibling sub-organizations.
     */
    public function getViewableOrganizationIds()
    {
        $viewableIds = [$this->id]; // Always include self
        
        // If this user has a parent organization, add it and all siblings
        if ($this->parent_organization_id) {
            $viewableIds[] = $this->parent_organization_id;
            
            // Add all sibling sub-organizations (same parent)
            $siblings = User::where('parent_organization_id', $this->parent_organization_id)
                ->where('id', '!=', $this->id)
                ->pluck('id')
                ->toArray();
            $viewableIds = array_merge($viewableIds, $siblings);
        }
        
        // If this user is a parent organization, add all sub-organizations
        $subOrgIds = $this->subOrganizations()->pluck('id')->toArray();
        $viewableIds = array_merge($viewableIds, $subOrgIds);
        
        return array_unique($viewableIds);
    }

    /**
     * Check if this user can view an application.
     */
    public function canViewApplication($application)
    {
        // Admins can view everything
        if ($this->isAdmin()) {
            return true;
        }
        
        // Check if application belongs to viewable organizations
        return in_array($application->user_id, $this->getViewableOrganizationIds());
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
