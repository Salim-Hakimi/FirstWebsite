<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserAccountService $userAccounts)
    {
    }

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
        $this->userAccounts->create($request);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'کاربر جدید ساخته شد و نقش او تنظیم گردید.');
    }

    public function edit(User $user): View
    {
        $this->userAccounts->preventManagingProtectedUser($user, auth()->id());

        return view('admin.users.form', [
            'user' => $user,
            'roleOptions' => auth()->user()->manageableRoleOptions(),
            'statusOptions' => User::statusOptions(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->userAccounts->update($request, $user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'معلومات کاربر به‌روزرسانی شد.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->userAccounts->delete($request, $user);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'کاربر از سیستم حذف شد.');
    }
}
