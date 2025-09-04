<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Vista de login.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesar login.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        // Si quieres forzar siempre /dashboard, usa:
        // return redirect()->route('dashboard');

        // Por defecto respeta la URL previa (intended) o HOME:
        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Logout -> redirigir al login con mensaje.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Antes: return redirect('/');
        return to_route('login')->with('status', 'Has cerrado sesión.');
    }
}
