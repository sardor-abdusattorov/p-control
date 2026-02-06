<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;

class ContactPolicy
{
    /**
     * Determine if the user can view any contacts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('read contact');
    }

    /**
     * Determine if the user can view the contact.
     */
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

    /**
     * Determine if the user can create contacts.
     */
    public function create(User $user): bool
    {
        return $user->can('create contact');
    }

    /**
     * Determine if the user can update the contact.
     * Only the owner or superadmin can update.
     */
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

    /**
     * Determine if the user can delete the contact.
     */
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

    /**
     * Determine if the user can bulk delete contacts.
     * Only superadmin can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete contact') && $user->hasRole('superadmin');
    }
}
