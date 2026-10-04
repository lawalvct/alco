<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Page $page): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Page $page): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Page $page): bool
    {
        return (bool) $user->is_admin && $page->published_data === null;
    }

    public function publish(User $user, Page $page): bool
    {
        return (bool) $user->is_admin;
    }
}
