<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $creds = $request->validated();
        if (! Auth::attempt(['email' => $creds['email'], 'password' => $creds['password']], $request->boolean('remember')) || ! Auth::user()->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Identifiants incorrects ou compte désactivé.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        Auth::user()->update(['last_login_at' => now()]);

        return redirect()->intended('/');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $colors = ['#3B82F6', '#8B5CF6', '#10B981', '#F59E0B', '#EC4899', '#06B6D4'];
        $user = User::create($data + ['role' => Role::Customer, 'avatar_color' => $colors[array_rand($colors)]]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Bienvenue sur Ticketly, '.$user->name.' !');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
