<?php

namespace App\Policies;

use App\Models\Calculator;
use App\Models\User;

class CalculatorPolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) $user->is_admin;
    }

    public function view(User $user, Calculator $calculator): bool
    {
        return (bool) $user->is_admin;
    }

    public function update(User $user, Calculator $calculator): bool
    {
        return (bool) $user->is_admin;
    }
}
