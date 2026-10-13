<?php

namespace App\Policies;

use App\Models\HomepageHeroSlide;
use App\Models\User;

class HomepageHeroSlidePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, HomepageHeroSlide $homepageHeroSlide): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, HomepageHeroSlide $homepageHeroSlide): bool
    {
        return (bool) $user->is_admin;
    }

    public function delete(User $user, HomepageHeroSlide $homepageHeroSlide): bool
    {
        return (bool) $user->is_admin;
    }

    public function reorder(User $user): bool
    {
        return (bool) $user->is_admin;
    }
}
