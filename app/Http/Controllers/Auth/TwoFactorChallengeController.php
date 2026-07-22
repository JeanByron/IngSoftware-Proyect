<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactor\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * RNF-19: desafío del segundo factor tras validar la contraseña. El usuario
 * NO queda autenticado hasta introducir un código TOTP válido. El id del
 * usuario a la espera del 2FA viaja en la sesión ('2fa:user:id').
 */
class TwoFactorChallengeController extends Controller
{
    private const SESSION_KEY = '2fa:user:id';

    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_KEY)) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $userId = $request->session()->get(self::SESSION_KEY);
        $user   = $userId ? User::find($userId) : null;

        if (! $user || ! $user->two_factor_secret || ! TwoFactor::verify($user->two_factor_secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'El código no es válido.']);
        }

        $request->session()->forget(self::SESSION_KEY);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
