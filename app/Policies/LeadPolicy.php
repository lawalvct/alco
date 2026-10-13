<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Lead $lead): bool
    {
        return (bool) $user->is_admin;
    }

    /** Enquiries arrive through the website forms only. */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Lead $lead): bool
    {
        return (bool) $user->is_admin;
    }

    /** For spam and data-deletion requests. */
    public function delete(User $user, Lead $lead): bool
    {
        return (bool) $user->is_admin;
    }

    public function deleteAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
