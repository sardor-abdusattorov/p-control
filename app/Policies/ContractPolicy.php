<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view contract');
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->can('view contract');
    }

    public function create(User $user): bool
    {
        return $user->can('create contract');
    }

    public function update(User $user, Contract $contract): bool
    {
        if (!$user->can('update contract')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $contract->user_id === $user->id;
    }

    public function delete(User $user, Contract $contract): bool
    {
        if (!$user->can('delete contract')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $contract->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete contract') && $user->hasRole('superadmin');
    }

    public function submit(User $user, Contract $contract): bool
    {
        return $user->can('submit contract');
    }

    public function approve(User $user): bool
    {
        return $user->can('approve contract');
    }

    public function manageApprovers(User $user, Contract $contract): bool
    {
        if (!$user->can('update contract')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $contract->user_id === $user->id;
    }

    public function export(User $user): bool
    {
        return $user->can('export contract');
    }

    public function uploadScan(User $user, Contract $contract): bool
    {
        if (!$user->can('update contract')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $contract->user_id === $user->id;
    }
}
