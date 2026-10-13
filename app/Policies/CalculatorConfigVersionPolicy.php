<?php

namespace App\Policies;

use App\Models\CalculatorConfigVersion;
use App\Models\User;

class CalculatorConfigVersionPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, CalculatorConfigVersion $rule): bool
    {
        return (bool) $user->is_admin;
    }

    public function create(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, CalculatorConfigVersion $rule): bool
    {
        return (bool) $user->is_admin && $rule->status !== 'approved';
    }

    public function approve(User $user, CalculatorConfigVersion $rule): bool
    {
        return (bool) $user->is_admin && $rule->status !== 'approved';
    }
}
