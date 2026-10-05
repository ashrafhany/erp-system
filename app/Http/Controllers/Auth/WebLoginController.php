<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WebLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])->onlyInput('email');
        }

        if (! $request->user()->roles()->where('guard_name', 'admin')->exists()) {
            Auth::logout();
            return back()->withErrors(['email' => 'هذا الحساب لا يملك صلاحية دخول الويب.'])->onlyInput('email');
        }

        $home = collect(array_keys(getMenuData()))
            ->first(fn ($section) => $request->user()->can("{$section}.view"));
        if (! $home) {
            Auth::logout();
            return back()->withErrors(['email' => 'هذا الحساب لا يملك صلاحية عرض أي قسم.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($home === 'dashboard' ? route('dashboard') : route("{$home}.index"));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
