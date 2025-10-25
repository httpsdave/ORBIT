<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ActivityLogService
{
    /**
     * Default TTL for activity logs (in seconds)
     * 48 hours = 2 days
     */
    protected int $ttl;

    /**
     * Maximum number of activities to store per user
     */
    protected int $maxActivities = 50;

    public function __construct()
    {
        // Get TTL from config or default to 48 hours
        $this->ttl = config('activity.cache_ttl', 48 * 60 * 60);
    }

    /**
     * Log a user activity to cache
     */
    public function log(int $userId, string $action, string $description, array $metadata = []): array
    {
        $activity = [
            'id' => uniqid('act_', true),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $cacheKey = $this->getCacheKey($userId);
        
        // Get existing activities
        $activities = $this->getActivities($userId);
        
        // Add new activity to the beginning
        array_unshift($activities, $activity);
        
        // Keep only the most recent activities
        $activities = array_slice($activities, 0, $this->maxActivities);
        
        // Store back to cache with TTL
        Cache::put($cacheKey, $activities, $this->ttl);
        
        return $activity;
    }

    /**
     * Get recent activities for a user
     */
    public function getActivities(int $userId, int $limit = 10): array
    {
        $cacheKey = $this->getCacheKey($userId);
        $activities = Cache::get($cacheKey, []);
        
        // Filter out expired activities (older than TTL)
        $cutoffTime = Carbon::now()->subSeconds($this->ttl);
        $activities = array_filter($activities, function ($activity) use ($cutoffTime) {
            $createdAt = Carbon::parse($activity['created_at']);
            return $createdAt->isAfter($cutoffTime);
        });
        
        // Re-index array after filtering
        $activities = array_values($activities);
        
        // Update cache with filtered activities
        if (count($activities) > 0) {
            Cache::put($cacheKey, $activities, $this->ttl);
        } else {
            Cache::forget($cacheKey);
        }
        
        // Return limited number of activities
        return array_slice($activities, 0, $limit);
    }

    /**
     * Clear all activities for a user
     */
    public function clearActivities(int $userId): void
    {
        $cacheKey = $this->getCacheKey($userId);
        Cache::forget($cacheKey);
    }

    /**
     * Get the cache key for a user's activities
     */
    protected function getCacheKey(int $userId): string
    {
        return "user_activities:{$userId}";
    }

    /**
     * Log application creation
     */
    public function logApplicationCreated(int $userId, $application): array
    {
        return $this->log(
            $userId,
            'application_created',
            'Created a new organization application',
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
                'application_id' => $application->id,
            ]
        );
    }

    /**
     * Log application update
     */
    public function logApplicationUpdated(int $userId, $application): array
    {
        return $this->log(
            $userId,
            'application_updated',
            'Updated organization application',
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
                'application_id' => $application->id,
            ]
        );
    }

    /**
     * Log application deletion
     */
    public function logApplicationDeleted(int $userId, $application): array
    {
        return $this->log(
            $userId,
            'application_deleted',
            'Deleted organization application',
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
                'application_id' => $application->id,
            ]
        );
    }

    /**
     * Log document upload
     */
    public function logDocumentUploaded(int $userId, $application, string $fileName): array
    {
        return $this->log(
            $userId,
            'document_uploaded',
            'Uploaded a signed document',
            [
                'file_name' => $fileName,
                'organization_name' => $application->organization_name,
                'application_id' => $application->id,
            ]
        );
    }

    /**
     * Log document deletion
     */
    public function logDocumentDeleted(int $userId, $application, string $fileName): array
    {
        return $this->log(
            $userId,
            'document_deleted',
            'Deleted a signed document',
            [
                'file_name' => $fileName,
                'organization_name' => $application->organization_name,
                'application_id' => $application->id,
            ]
        );
    }

    /**
     * Log application view
     */
    public function logApplicationViewed(int $userId, $application): array
    {
        return $this->log(
            $userId,
            'application_viewed',
            'Viewed organization application details',
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
                'application_id' => $application->id,
            ]
        );
    }
}
