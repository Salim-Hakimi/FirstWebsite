<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Support\SecurityRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => SecurityRules::phone(false),
            'theme' => ['required', Rule::in(['light', 'dark'])],
            'profile_photo' => SecurityRules::profileImage(),
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        $oldValues = $user->only(['name', 'email', 'phone', 'theme', 'profile_photo_path']);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'theme' => $validated['theme'],
            'profile_photo_path' => $this->syncProfilePhoto($request),
        ]);
        Audit::record('account_profile_updated', $user, $oldValues, $user->fresh()->only(['name', 'email', 'phone', 'theme', 'profile_photo_path']), $request);

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Account information updated.');
    }

    private function syncProfilePhoto(Request $request): ?string
    {
        $user = $request->user();

        if ($request->boolean('remove_profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            return null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            return $request->file('profile_photo')->store('profile-photos/users', 'public');
        }

        return $user->profile_photo_path;
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => SecurityRules::strongPassword(),
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);
        Audit::record('account_password_changed', $request->user(), [], ['password_changed' => true], $request);

        return redirect()
            ->route('settings.edit')
            ->with('status', 'Password changed successfully.');
    }
}
