<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Audit;
use App\Support\SecurityRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()
                ->whereIn('role', array_keys(User::roleOptions()))
                ->latest()
                ->get(),
            'roleLabels' => User::roleOptions(),
            'statusLabels' => User::statusOptions(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User([
                'role' => User::ROLE_GUARD,
                'status' => User::STATUS_ACTIVE,
            ]),
            'roleOptions' => auth()->user()->manageableRoleOptions(),
            'statusOptions' => User::statusOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

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

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'کاربر جدید ساخته شد و نقش او تنظیم گردید.');
    }

    public function edit(User $user): View
    {
        $this->preventManagingProtectedUser($user);

        return view('admin.users.form', [
            'user' => $user,
            'roleOptions' => auth()->user()->manageableRoleOptions(),
            'statusOptions' => User::statusOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->preventManagingProtectedUser($user);

        $validated = $this->validateUser($request, $user);

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

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'معلومات کاربر به‌روزرسانی شد.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->preventManagingProtectedUser($user);

        $oldValues = $user->only(['name', 'email', 'phone', 'role', 'status']);
        if ($user->profile_photo_path) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->delete();
        Audit::record('user_deleted', $user, $oldValues, [], request());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'کاربر از سیستم حذف شد.');
    }

    private function validateUser(Request $request, ?User $user = null): array
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

    private function preventManagingProtectedUser(User $user): void
    {
        if (auth()->id() === $user->id) {
            abort(403, 'شما نمی‌توانید نقش یا وضعیت حساب خودتان را از این بخش تغییر دهید.');
        }

        if (! array_key_exists($user->role, User::roleOptions())) {
            abort(403, 'این نقش دیگر در سیستم فعال نیست.');
        }
    }
}
