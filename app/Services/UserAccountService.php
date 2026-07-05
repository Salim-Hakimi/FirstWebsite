<?php

namespace App\Services;

use App\Models\User;
use App\Support\Audit;
use App\Support\SecurityRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserAccountService
{
    /**
     * @return array<string, mixed>
     */
    public function validate(Request $request, ?User $user = null): array
    {
        $roleOptions = array_keys($request->user()->manageableRoleOptions());

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user)],
            'phone' => SecurityRules::phone(),
            'role' => ['required', Rule::in($roleOptions)],
            'status' => ['required', Rule::in(array_keys(User::statusOptions()))],
            'profile_photo' => SecurityRules::profileImage(),
            'remove_profile_photo' => ['nullable', 'boolean'],
            'password' => SecurityRules::strongPassword(! $user),
        ]);
    }

    public function create(Request $request): User
    {
        $validated = $this->validate($request);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'profile_photo_path' => $this->storeProfilePhoto($request),
            'password' => Hash::make($validated['password']),
        ]);

        Audit::record('user_created', $user, [], $user->only(['name', 'email', 'phone', 'role', 'status']), $request);

        return $user;
    }

    public function update(Request $request, User $user): User
    {
        $this->preventManagingProtectedUser($user, $request->user()?->id);

        $validated = $this->validate($request, $user);
        $oldValues = $user->only(['name', 'email', 'phone', 'role', 'status', 'profile_photo_path']);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'profile_photo_path' => $this->syncProfilePhoto($request, $user),
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        Audit::record('user_updated', $user, $oldValues, $user->fresh()->only(['name', 'email', 'phone', 'role', 'status', 'profile_photo_path']), $request);

        return $user->fresh();
    }

    public function delete(Request $request, User $user): void
    {
        $this->preventManagingProtectedUser($user, $request->user()?->id);

        $oldValues = $user->only(['name', 'email', 'phone', 'role', 'status']);

        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();
        Audit::record('user_deleted', $user, $oldValues, [], $request);
    }

    public function preventManagingProtectedUser(User $user, ?int $actorId = null): void
    {
        if ($actorId === $user->id) {
            abort(403, 'شما نمی‌توانید نقش یا وضعیت حساب خودتان را از این بخش تغییر دهید.');
        }

        if (! array_key_exists($user->role, User::roleOptions())) {
            abort(403, 'این نقش دیگر در سیستم فعال نیست.');
        }
    }

    private function storeProfilePhoto(Request $request): ?string
    {
        return $request->file('profile_photo')?->store('profile-photos/users', 'public');
    }

    private function syncProfilePhoto(Request $request, User $user): ?string
    {
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

            return $this->storeProfilePhoto($request);
        }

        return $user->profile_photo_path;
    }
}
