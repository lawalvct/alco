<?php

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Faq $faq): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Faq $faq): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, Faq $faq): bool
    {
        return (bool) $user->is_admin;
    }

    public function reorder(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
