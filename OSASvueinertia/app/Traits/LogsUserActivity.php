<?php

namespace App\Traits;

use App\Models\UserActivity;

trait LogsUserActivity
{
    /**
     * Log user activity.
     */
    protected function logActivity(string $action, string $description, $subject = null, array $metadata = []): UserActivity
    {
        return UserActivity::log($action, $description, $subject, $metadata);
    }

    /**
     * Log application creation.
     */
    protected function logApplicationCreated($application): UserActivity
    {
        return $this->logActivity(
            'application_created',
            'Created a new organization application',
            $application,
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
            ]
        );
    }

    /**
     * Log application update.
     */
    protected function logApplicationUpdated($application): UserActivity
    {
        return $this->logActivity(
            'application_updated',
            'Updated organization application',
            $application,
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
            ]
        );
    }

    /**
     * Log application deletion.
     */
    protected function logApplicationDeleted($application): UserActivity
    {
        return $this->logActivity(
            'application_deleted',
            'Deleted organization application',
            null, // Subject is deleted, so we pass null
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
                'deleted_id' => $application->id,
            ]
        );
    }

    /**
     * Log document upload.
     */
    protected function logDocumentUploaded($application, string $fileName): UserActivity
    {
        return $this->logActivity(
            'document_uploaded',
            'Uploaded a signed document',
            $application,
            [
                'file_name' => $fileName,
                'organization_name' => $application->organization_name,
            ]
        );
    }

    /**
     * Log document deletion.
     */
    protected function logDocumentDeleted($application, string $fileName): UserActivity
    {
        return $this->logActivity(
            'document_deleted',
            'Deleted a signed document',
            $application,
            [
                'file_name' => $fileName,
                'organization_name' => $application->organization_name,
            ]
        );
    }

    /**
     * Log application view.
     */
    protected function logApplicationViewed($application): UserActivity
    {
        return $this->logActivity(
            'application_viewed',
            'Viewed organization application details',
            $application,
            [
                'form_type' => $application->form_type,
                'organization_name' => $application->organization_name,
            ]
        );
    }
}