<?php

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    /**
     * Determine if the user can view any contracts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view contract');
    }

    /**
     * Determine if the user can view the contract.
     */
    public function view(User $user, Contract $contract): bool
    {
        return $user->can('view contract');
    }

    /**
     * Determine if the user can create contracts.
     */
    public function create(User $user): bool
    {
        return $user->can('create contract');
    }

    /**
     * Determine if the user can update the contract.
     * Only the owner or superadmin can update.
     */
    public function update(User $user, Contract $contract): bool
    {
        if (!$user->can('update contract')) {
            return false;
        }

        // Superadmin can update any contract
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Regular users can only update their own contracts
        return $contract->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the contract.
     */
    public function delete(User $user, Contract $contract): bool
    {
        if (!$user->can('delete contract')) {
            return false;
        }

        // Superadmin can delete any contract
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // Regular users can only delete their own contracts
        return $contract->user_id === $user->id;
    }

    /**
     * Determine if the user can bulk delete contracts.
     * Only superadmin can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete contract') && $user->hasRole('superadmin');
    }

    /**
     * Determine if the user can submit the contract for approval.
     */
    public function submit(User $user, Contract $contract): bool
    {
        return $user->can('submit contract');
    }

    /**
     * Determine if the user can approve or reject contracts.
     */
    public function approve(User $user): bool
    {
        return $user->can('approve contract');
    }

    /**
     * Determine if the user can manage approvers (add/remove).
     * Only the owner or superadmin can manage approvers.
     */
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

    /**
     * Determine if the user can upload scan files.
     * Only the owner or superadmin can upload scans.
     */
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
