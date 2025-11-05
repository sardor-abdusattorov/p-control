<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine if the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view products') || $user->can('manage products');
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        if (!$user->can('view products') && !$user->can('manage products')) {
            return false;
        }

        // Superadmin can view any product
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // If user has manage products, can view all
        if ($user->can('manage products')) {
            return true;
        }

        // Regular users can only view their own products
        return $product->user_id === $user->id;
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->can('create products') || $user->can('manage products');
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        if (!$user->can('update products') && !$user->can('manage products')) {
            return false;
        }

        // Superadmin can update any product
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // If user has manage products, can update all
        if ($user->can('manage products')) {
            return true;
        }

        // Regular users can only update their own products
        return $product->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        if (!$user->can('delete products') && !$user->can('manage products')) {
            return false;
        }

        // Superadmin can delete any product
        if ($user->hasRole('superadmin')) {
            return true;
        }

        // If user has manage products, can delete all
        if ($user->can('manage products')) {
            return true;
        }

        // Regular users can only delete their own products
        return $product->user_id === $user->id;
    }

    /**
     * Determine if the user can bulk delete products.
     * Only superadmin and users with manage products can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return ($user->can('delete products') || $user->can('manage products'))
            && ($user->hasRole('superadmin') || $user->can('manage products'));
    }
}
