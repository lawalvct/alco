<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, TeamMember $teamMember): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, TeamMember $teamMember): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, TeamMember $teamMember): bool
    {
        return (bool) $user->is_admin;
    }

    public function reorder(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
