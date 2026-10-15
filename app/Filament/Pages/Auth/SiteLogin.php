<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login;

/**
 * The site has one login page (/login). Filament still needs a login route to
 * send signed-out visitors to, so this one simply hands over to /login. After
 * signing in, the visitor is returned to the admin dashboard.
 */
class SiteLogin extends Login
{
    public function mount(): void
    {
        $this->redirect(route('login'));
    }
}
