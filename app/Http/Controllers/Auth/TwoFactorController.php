<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactor\TwoFactor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * RNF-19: gestión del 2FA por el propio usuario del panel (activar, confirmar,
 * desactivar) desde su perfil.
 */
class TwoFactorController extends Controller
{
    /** Inicia la activación: genera un secreto (aún SIN confirmar). */
    public function enable(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret'       => TwoFactor::generateSecret(),
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-setup');
    }

    /** Confirma la activación validando un código de la app autenticadora. */
    public function confirm(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->two_factor_secret || ! TwoFactor::verify($user->two_factor_secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'El código no es válido. Revisa la app e intenta de nuevo.']);
        }

        $user->forceFill(['two_factor_confirmed_at' => now()])->save();

        return back()->with('status', 'two-factor-enabled');
    }

    /** Desactiva el 2FA. */
    public function disable(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'two_factor_secret'       => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return back()->with('status', 'two-factor-disabled');
    }
}
