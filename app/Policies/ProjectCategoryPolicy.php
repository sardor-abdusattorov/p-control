<?php

namespace App\Policies;

use App\Models\ProjectCategory;
use App\Models\User;

class ProjectCategoryPolicy
{
    /**
     * Determine if the user can view any project categories.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage project categories');
    }

    /**
     * Determine if the user can view the project category.
     */
    public function view(User $user, ProjectCategory $projectCategory): bool
    {
        return $user->can('manage project categories');
    }

    /**
     * Determine if the user can create project categories.
     */
    public function create(User $user): bool
    {
        return $user->can('manage project categories');
    }

    /**
     * Determine if the user can update the project category.
     */
    public function update(User $user, ProjectCategory $projectCategory): bool
    {
        return $user->can('manage project categories');
    }

    /**
     * Determine if the user can delete the project category.
     */
    public function delete(User $user, ProjectCategory $projectCategory): bool
    {
        return $user->can('manage project categories');
    }

    /**
     * Determine if the user can bulk delete project categories.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('manage project categories') && $user->hasRole('superadmin');
    }
}
