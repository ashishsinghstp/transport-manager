<?php

namespace App\Http\Controllers;

use App\Http\Requests\Web\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebAuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->user() !== null) {
            return redirect()->route('drivers.index');
        }

        return view('auth.login');
    }

    /**
     * Authenticate the user with a web session.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $authenticated = Auth::attempt(
            [
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ],
            (bool) ($credentials['remember'] ?? false),
        );

        if (! $authenticated) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.failed')]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('drivers.index'));
    }

    /**
     * Log the user out and invalidate the session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
