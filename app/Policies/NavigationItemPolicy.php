<?php

namespace App\Policies;

use App\Models\NavigationItem;
use App\Models\User;

class NavigationItemPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, NavigationItem $navigationItem): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, NavigationItem $navigationItem): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, NavigationItem $navigationItem): bool
    {
        return (bool) $user->is_admin;
    }

    public function reorder(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
