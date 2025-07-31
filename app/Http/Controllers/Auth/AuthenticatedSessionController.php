<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Models\Impresion;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Toasts para pliegos impresos diferentes
        $diferencias = Impresion::with('orden')
            ->whereNotNull('cantidad_pliegos')
            ->whereNotNull('cantidad_pliegos_impresos')
            ->get()
            ->filter(fn($i) => $i->cantidad_pliegos !== $i->cantidad_pliegos_impresos);

        if ($diferencias->isNotEmpty()) {
            $mensajesToast = $diferencias->map(function ($i) {
                $orden = $i->orden->numero_orden ?? 'N/A';
                if ($i->cantidad_pliegos_impresos > $i->cantidad_pliegos) {
                    return "⚠️ La orden <strong>#{$orden}</strong> tiene más pliegos impresos que los solicitados.";
                } else {
                    return "⚠️ La orden <strong>#{$orden}</strong> tiene menos pliegos impresos que los solicitados.";
                }
            });

            session()->flash('toast_pliegos_diferentes', $mensajesToast->toArray());
        }

        session()->flash('mostrar_toast_impresion', true);

        return redirect()->intended(route('impresiones.index'));
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
