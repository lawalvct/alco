<?php

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, MediaAsset $mediaAsset): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, MediaAsset $mediaAsset): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, MediaAsset $mediaAsset): bool
    {
        // Images shown on the site must be replaced before they can be removed.
        return (bool) $user->is_admin && ! $mediaAsset->isInUse();
    }
}
