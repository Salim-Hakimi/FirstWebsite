<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Audit;
use App\Support\SecurityRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function createLogin(): View
    {
        return view('auth.login', [
            'hasUsers' => Schema::hasTable('users') && User::query()->exists(),
        ]);
    }

    public function storeLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'ایمیل یا رمز عبور درست نیست.',
            ]);
        }

        if (! $request->user()->isActive()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'حساب شما فعال نیست یا مسدود شده است.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function createRegister(): View
    {
        abort_if(User::query()->exists(), 404);

        return view('auth.register');
    }

    public function storeRegister(Request $request): RedirectResponse
    {
        abort_if(User::query()->exists(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'phone' => SecurityRules::phone(),
            'password' => SecurityRules::strongPassword(),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => User::ROLE_ADMIN,
            'status' => User::STATUS_ACTIVE,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        Audit::record('staff_setup_admin_created', $user, [], $user->only(['name', 'email', 'phone', 'role', 'status']), $request);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'حساب ادمین اول ساخته شد. اکنون می‌توانید کاربران سیستم را ثبت کنید.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('status', 'با موفقیت از سیستم خارج شدید.');
    }
}
