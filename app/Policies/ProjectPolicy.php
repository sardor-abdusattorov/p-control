<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view project');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('view project');
    }

    public function create(User $user): bool
    {
        return $user->can('create project');
    }

    public function update(User $user, Project $project): bool
    {
        if (!$user->can('update project')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return true;
    }

    public function delete(User $user, Project $project): bool
    {
        if (!$user->can('delete project')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete project') && $user->hasRole('superadmin');
    }
}
