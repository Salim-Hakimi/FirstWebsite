<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'users' => User::query()->latest()->get(),
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

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'status' => $validated['status'],
            'profile_photo_path' => $this->storeProfilePhoto($request),
            'password' => Hash::make($validated['password']),
        ]);

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

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'معلومات کاربر به‌روزرسانی شد.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $roleOptions = array_keys($request->user()->manageableRoleOptions());

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['required', 'string', 'max:30'],
            'role' => ['required', Rule::in($roleOptions)],
            'status' => ['required', Rule::in(array_keys(User::statusOptions()))],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', 'min:8'],
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

        if ($user->role === User::ROLE_OWNER && auth()->user()->role !== User::ROLE_OWNER) {
            abort(403);
        }
    }
}
