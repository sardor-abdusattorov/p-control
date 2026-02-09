<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view project');
    }

    /**
     * Determine if the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->can('view project');
    }

    /**
     * Determine if the user can create projects.
     */
    public function create(User $user): bool
    {
        return $user->can('create project');
    }

    /**
     * Determine if the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        if (!$user->can('update project')) {
            return false;
        }

        // Superadmin can update any project
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return true;
    }

    /**
     * Determine if the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        if (!$user->can('delete project')) {
            return false;
        }

        // Superadmin can delete any project
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return true;
    }

    /**
     * Determine if the user can bulk delete projects.
     * Only superadmin can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete project') && $user->hasRole('superadmin');
    }
}
