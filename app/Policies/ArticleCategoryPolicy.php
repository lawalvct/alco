<?php

namespace App\Policies;

use App\Models\ArticleCategory;
use App\Models\User;

class ArticleCategoryPolicy
{
    public function viewAny(User $user): bool { return (bool) $user->is_admin; }
    public function view(User $user, ArticleCategory $category): bool { return (bool) $user->is_admin; }
    public function create(User $user): bool { return (bool) $user->is_admin; }
    public function update(User $user, ArticleCategory $category): bool { return (bool) $user->is_admin; }
    public function delete(User $user, ArticleCategory $category): bool { return (bool) $user->is_admin && ! $category->posts()->exists(); }
}
