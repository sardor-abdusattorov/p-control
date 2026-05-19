<?php

namespace App\Policies;

use App\Models\ProjectCategory;
use App\Models\User;

class ProjectCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage project categories');
    }

    public function view(User $user, ProjectCategory $projectCategory): bool
    {
        return $user->can('manage project categories');
    }

    public function create(User $user): bool
    {
        return $user->can('manage project categories');
    }

    public function update(User $user, ProjectCategory $projectCategory): bool
    {
        return $user->can('manage project categories');
    }

    public function delete(User $user, ProjectCategory $projectCategory): bool
    {
        return $user->can('manage project categories');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('manage project categories') && $user->hasRole('superadmin');
    }
}
