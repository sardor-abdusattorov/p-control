<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Determine if the user can view any applications.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view application');
    }

    /**
     * Determine if the user can view the application.
     */
    public function view(User $user, Application $application): bool
    {
        return $user->can('view application');
    }

    /**
     * Determine if the user can create applications.
     */
    public function create(User $user): bool
    {
        return $user->can('create application');
    }

    /**
     * Determine if the user can update the application.
     * Only the owner or superadmin can update.
     */
    public function update(User $user, Application $application): bool
    {
        if (!$user->can('update application')) {
            return false;
        }

        // Superadmin can update any application
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Regular users can only update their own applications
        return $application->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the application.
     */
    public function delete(User $user, Application $application): bool
    {
        if (!$user->can('delete application')) {
            return false;
        }

        // Superadmin can delete any application
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Regular users can only delete their own applications
        return $application->user_id === $user->id;
    }

    /**
     * Determine if the user can bulk delete applications.
     * Only superadmin can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete application') && $user->hasRole('superadmin');
    }

    /**
     * Determine if the user can submit the application for approval.
     */
    public function submit(User $user, Application $application): bool
    {
        return $user->can('submit application');
    }

    /**
     * Determine if the user can approve or reject applications.
     */
    public function approve(User $user): bool
    {
        return $user->can('approve application');
    }

    /**
     * Determine if the user can manage approvers (add/remove).
     * Only the owner or superadmin can manage approvers.
     */
    public function manageApprovers(User $user, Application $application): bool
    {
        if (!$user->can('update application')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $application->user_id === $user->id;
    }

    /**
     * Determine if the user can upload scan files.
     * Only the owner or superadmin can upload scans.
     */
    public function uploadScan(User $user, Application $application): bool
    {
        if (!$user->can('update application')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $application->user_id === $user->id;
    }
}
