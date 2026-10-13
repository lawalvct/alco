<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool { return (bool) $user->is_admin; }
    public function view(User $user, Post $post): bool { return (bool) $user->is_admin; }
    public function create(User $user): bool { return (bool) $user->is_admin; }
    public function update(User $user, Post $post): bool { return (bool) $user->is_admin; }
    public function delete(User $user, Post $post): bool { return (bool) $user->is_admin && $post->published_data === null; }
    public function publish(User $user, Post $post): bool { return (bool) $user->is_admin; }
}
