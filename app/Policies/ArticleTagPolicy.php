<?php

namespace App\Policies;

use App\Models\ArticleTag;
use App\Models\User;

class ArticleTagPolicy
{
    public function viewAny(User $user): bool { return (bool) $user->is_admin; }
    public function view(User $user, ArticleTag $tag): bool { return (bool) $user->is_admin; }
    public function create(User $user): bool { return (bool) $user->is_admin; }
    public function update(User $user, ArticleTag $tag): bool { return (bool) $user->is_admin; }
    public function delete(User $user, ArticleTag $tag): bool { return (bool) $user->is_admin && ! $tag->posts()->exists(); }
}
