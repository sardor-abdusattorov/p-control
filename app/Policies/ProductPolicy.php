<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view products') || $user->can('manage products');
    }

    public function view(User $user, Product $product): bool
    {
        if (!$user->can('view products') && !$user->can('manage products')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->can('manage products')) {
            return true;
        }

        return $product->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create products') || $user->can('manage products');
    }

    public function update(User $user, Product $product): bool
    {
        if (!$user->can('update products') && !$user->can('manage products')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->can('manage products')) {
            return true;
        }

        return $product->user_id === $user->id;
    }

    public function delete(User $user, Product $product): bool
    {
        if (!$user->can('delete products') && !$user->can('manage products')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        if ($user->can('manage products')) {
            return true;
        }

        return $product->user_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return ($user->can('delete products') || $user->can('manage products'))
            && ($user->hasRole('superadmin') || $user->can('manage products'));
    }
}
