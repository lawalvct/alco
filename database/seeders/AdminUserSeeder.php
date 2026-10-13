<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user. Defaults apply to local only; other
     * environments must set ADMIN_EMAIL and ADMIN_PASSWORD.
     */
    public function run(): void
    {
        $local = app()->environment('local');

        $email = env('ADMIN_EMAIL', $local ? 'admin@alco.test' : null);
        $password = env('ADMIN_PASSWORD', $local ? 'password' : null);

        if (! $email || ! $password) {
            $this->command?->warn('Skipped admin user: set ADMIN_EMAIL and ADMIN_PASSWORD.');

            return;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $user->name ?: 'ALCO Admin';
        $user->password = Hash::make($password);
        $user->email_verified_at ??= now();
        $user->is_admin = true;
        $user->save();
    }
}
