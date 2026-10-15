<?php

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

/**
 * After signing in (with or without a two-factor code) the visitor goes to the
 * page they asked for, or the admin dashboard. The destination is usually
 * Filament, which is not an Inertia page, so the login form must do a full
 * page load; a plain redirect would open the admin inside a pop-up.
 */
class SiteLoginResponse implements LoginResponseContract, TwoFactorLoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['two_factor' => false]);
        }

        $target = redirect()->intended(Fortify::redirects('login'))->getTargetUrl();

        return $request->header('X-Inertia')
            ? Inertia::location($target)
            : redirect()->to($target);
    }
}
