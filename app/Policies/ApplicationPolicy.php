<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view application');
    }

    public function view(User $user, Application $application): bool
    {
        return $user->can('view application');
    }

    public function create(User $user): bool
    {
        return $user->can('create application');
    }

    public function update(User $user, Application $application): bool
    {
        if (!$user->can('update application')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $application->user_id === $user->id;
    }

    public function delete(User $user, Application $application): bool
    {
        if (!$user->can('delete application')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $application->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete application') && $user->hasRole('superadmin');
    }

    public function submit(User $user, Application $application): bool
    {
        return $user->can('submit application');
    }

    public function approve(User $user): bool
    {
        return $user->can('approve application');
    }

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
