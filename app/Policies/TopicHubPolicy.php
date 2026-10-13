<?php

namespace App\Policies;

use App\Models\TopicHub;
use App\Models\User;

class TopicHubPolicy
{
    public function viewAny(User $user): bool { return (bool) $user->is_admin; }
    public function view(User $user, TopicHub $topicHub): bool { return (bool) $user->is_admin; }
    public function create(User $user): bool { return (bool) $user->is_admin; }
    public function update(User $user, TopicHub $topicHub): bool { return (bool) $user->is_admin; }
    public function delete(User $user, TopicHub $topicHub): bool { return (bool) $user->is_admin && $topicHub->published_data === null; }
    public function publish(User $user, TopicHub $topicHub): bool { return (bool) $user->is_admin; }
}
