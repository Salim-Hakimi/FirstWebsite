<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LibraryMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LibraryMembersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'in:active,suspended,left'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:24'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 8);
        $canWrite = $request->user()?->role === User::ROLE_LIBRARIAN;

        $members = LibraryMember::query()
            ->with(['membershipCards' => fn ($query) => $query->where('scope', 'library')->latest('expires_at')])
            ->withCount(['loans as active_loans_count' => fn ($query) => $query->whereIn('status', ['borrowed', 'late'])])
            ->when($validated['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tazkira_number', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%")
                        ->orWhere('education_place', 'like', "%{$search}%");
                });
            })
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $members->getCollection()->map(fn (LibraryMember $member): array => $this->memberPayload($member, $canWrite))->values(),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'from' => $members->firstItem(),
                'to' => $members->lastItem(),
            ],
            'links' => [
                'first' => $members->url(1),
                'last' => $members->url($members->lastPage()),
                'prev' => $members->previousPageUrl(),
                'next' => $members->nextPageUrl(),
            ],
            'filters' => [
                'q' => $validated['q'] ?? '',
                'status' => $validated['status'] ?? '',
            ],
        ]);
    }

    private function memberPayload(LibraryMember $member, bool $canWrite): array
    {
        $card = $member->membershipCards->first();

        return [
            'id' => $member->id,
            'member_code' => $member->member_code,
            'full_name' => $member->full_name,
            'father_name' => $member->father_name,
            'phone' => $member->phone,
            'status' => $member->status,
            'payment_status' => $member->payment_status,
            'membership_fee' => (int) $member->membership_fee,
            'monthly_balance' => (int) $member->monthlyFeeBalance(),
            'active_loans_count' => (int) $member->active_loans_count,
            'joined_at' => $member->joined_at?->toDateString(),
            'next_payment_due_at' => $member->next_payment_due_at?->toDateString(),
            'membership_expires_at' => $member->membership_expires_at?->toDateString() ?: $card?->expires_at?->toDateString(),
            'profile_photo_url' => $member->profile_photo_path ? asset('storage/'.$member->profile_photo_path) : null,
            'links' => [
                'show' => route('library.members.show', $member),
                'edit' => $canWrite ? route('library.members.edit', $member) : null,
            ],
        ];
    }
}
