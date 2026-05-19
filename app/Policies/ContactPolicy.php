<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('read contact');
    }

    public function view(User $user, Contact $contact): bool
    {
        if (!$user->can('read contact')) {
            return false;
        }

        if ($user->hasRole('superadmin') || $user->can('view all contacts')) {
            return true;
        }

        return $contact->owner_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('create contact');
    }

    public function update(User $user, Contact $contact): bool
    {
        if (!$user->can('update contact')) {
            return false;
        }

        if ($user->hasRole('superadmin') || $user->can('view all contacts')) {
            return true;
        }

        return $contact->owner_id === $user->id;
    }

    public function delete(User $user, Contact $contact): bool
    {
        if (!$user->can('delete contact')) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
            return true;
        }

        return $contact->owner_id === $user->id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete contact') && $user->hasRole('superadmin');
    }

    public function export(User $user): bool
    {
        return $user->can('read contact');
    }
}
