<?php

namespace App\Policies;

use App\Models\EngagementPackage;
use App\Models\User;

class EngagementPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, EngagementPackage $engagementPackage): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, EngagementPackage $engagementPackage): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, EngagementPackage $engagementPackage): bool
    {
        // Published packages are archived instead, so enquiry history stays intact.
        return (bool) $user->is_admin && $engagementPackage->published_data === null;
    }

    public function publish(User $user, EngagementPackage $engagementPackage): bool
    {
        return (bool) $user->is_admin;
    }
}
