<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can("user.view-any");
    }

    public function view(User $user, User $object): bool
    {
        return $user->parent_id === 0 && $object->parent_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, User $object): bool
    {
        return false;
    }

    public function delete(User $user, User $object): bool
    {
        return false;
    }
}
