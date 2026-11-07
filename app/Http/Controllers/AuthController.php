<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Session::get('logged_in')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'branch'   => 'required|string|in:QLJ,QLKP,QLTMG,PUSAT',
        ]);

        $username = $request->input('username');
        $password = $request->input('password');
        $branch   = $request->input('branch');

        if ($username === 'Queen Latifa' && $password === 'Hospital@2026') {
            Session::put('logged_in', true);
            Session::put('username', $username);
            Session::put('branch', $branch);
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors(['username' => 'Username atau password salah.'])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Session::forget(['logged_in', 'username']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
