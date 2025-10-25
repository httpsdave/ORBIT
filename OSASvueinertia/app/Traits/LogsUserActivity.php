<?php

namespace App\Traits;

use App\Services\ActivityLogService;

trait LogsUserActivity
{
    /**
     * Get the activity log service instance
     */
    protected function getActivityLogService(): ActivityLogService
    {
        return app(ActivityLogService::class);
    }

    /**
     * Log user activity (using cache-based service)
     */
    protected function logActivity(string $action, string $description, $subject = null, array $metadata = []): array
    {
        $userId = auth()->id();
        
        if (!$userId) {
            return [];
        }

        // If subject provided, add its info to metadata
        if ($subject) {
            $metadata['subject_type'] = get_class($subject);
            $metadata['subject_id'] = $subject->id;
        }

        return $this->getActivityLogService()->log($userId, $action, $description, $metadata);
    }

    /**
     * Log application creation.
     */
    protected function logApplicationCreated($application): array
    {
        $userId = auth()->id();
        return $this->getActivityLogService()->logApplicationCreated($userId, $application);
    }

    /**
     * Log application update.
     */
    protected function logApplicationUpdated($application): array
    {
        $userId = auth()->id();
        return $this->getActivityLogService()->logApplicationUpdated($userId, $application);
    }

    /**
     * Log application deletion.
     */
    protected function logApplicationDeleted($application): array
    {
        $userId = auth()->id();
        return $this->getActivityLogService()->logApplicationDeleted($userId, $application);
    }

    /**
     * Log document upload.
     */
    protected function logDocumentUploaded($application, string $fileName): array
    {
        $userId = auth()->id();
        return $this->getActivityLogService()->logDocumentUploaded($userId, $application, $fileName);
    }

    /**
     * Log document deletion.
     */
    protected function logDocumentDeleted($application, string $fileName): array
    {
        $userId = auth()->id();
        return $this->getActivityLogService()->logDocumentDeleted($userId, $application, $fileName);
    }

    /**
     * Log application view.
     */
    protected function logApplicationViewed($application): array
    {
        $userId = auth()->id();
        return $this->getActivityLogService()->logApplicationViewed($userId, $application);
    }
}