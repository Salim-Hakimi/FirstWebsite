<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUsersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'role' => ['nullable', Rule::in(array_keys(User::roleOptions()))],
            'status' => ['nullable', Rule::in(array_keys(User::statusOptions()))],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);
        $currentUserId = $request->user()?->id;

        $users = User::query()
            ->whereIn('role', array_keys(User::roleOptions()))
            ->when($validated['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($validated['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $users->getCollection()
                ->map(fn (User $user): array => $this->userPayload($user, $currentUserId))
                ->values(),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
            'summary' => $this->summaryPayload(),
            'filters' => [
                'q' => $validated['q'] ?? '',
                'role' => $validated['role'] ?? '',
                'status' => $validated['status'] ?? '',
                'roles' => User::roleOptions(),
                'statuses' => User::statusOptions(),
            ],
        ]);
    }

    private function userPayload(User $user, ?int $currentUserId): array
    {
        $isCurrentUser = $currentUserId === $user->id;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $user->role,
            'role_label' => User::roleOptions()[$user->role] ?? $user->role,
            'status' => $user->status,
            'status_label' => User::statusOptions()[$user->status] ?? $user->status,
            'is_current_user' => $isCurrentUser,
            'created_at' => $user->created_at?->toDateString(),
            'profile_photo_url' => $user->profile_photo_path ? route('storage.public', ['path' => $user->profile_photo_path]) : null,
            'links' => [
                'edit' => $isCurrentUser ? null : route('admin.users.edit', $user),
            ],
        ];
    }

    private function summaryPayload(): array
    {
        $users = User::query()
            ->whereIn('role', array_keys(User::roleOptions()))
            ->get(['id', 'role', 'status']);

        return [
            'total' => $users->count(),
            'active' => $users->where('status', User::STATUS_ACTIVE)->count(),
            'pending' => $users->where('status', User::STATUS_PENDING)->count(),
            'suspended' => $users->where('status', User::STATUS_SUSPENDED)->count(),
            'management' => $users->whereIn('role', User::managementRoles())->count(),
        ];
    }
}
