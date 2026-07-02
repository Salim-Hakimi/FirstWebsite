<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BookLoan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryLoansController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $this->syncOverdueLoans();

        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);
        $canWrite = $request->user()?->role === User::ROLE_LIBRARIAN;

        $loans = BookLoan::query()
            ->with(['member', 'book', 'copy', 'recordedBy'])
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($validated['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('borrowed_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('borrowed_at', '<=', $date))
            ->when($validated['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('loan_code', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('member', function ($memberQuery) use ($search): void {
                            $memberQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('father_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('member_code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('book', function ($bookQuery) use ($search): void {
                            $bookQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('author', 'like', "%{$search}%")
                                ->orWhere('isbn', 'like', "%{$search}%");
                        })
                        ->orWhereHas('copy', function ($copyQuery) use ($search): void {
                            $copyQuery
                                ->where('copy_code', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('borrowed_at')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $loans->getCollection()
                ->map(fn (BookLoan $loan): array => $this->loanPayload($loan, $canWrite))
                ->values(),
            'meta' => [
                'current_page' => $loans->currentPage(),
                'last_page' => $loans->lastPage(),
                'per_page' => $loans->perPage(),
                'total' => $loans->total(),
                'from' => $loans->firstItem(),
                'to' => $loans->lastItem(),
            ],
            'summary' => $this->summaryPayload(),
            'filters' => [
                'q' => $validated['q'] ?? '',
                'status' => $validated['status'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'statuses' => $this->statusLabels(),
            ],
        ]);
    }

    private function loanPayload(BookLoan $loan, bool $canWrite): array
    {
        return [
            'id' => $loan->id,
            'loan_code' => $loan->loan_code,
            'status' => $loan->status,
            'status_label' => $this->statusLabels()[$loan->status] ?? $loan->status,
            'borrowed_at' => $loan->borrowed_at?->toDateString(),
            'due_at' => $loan->due_at?->toDateString(),
            'returned_at' => $loan->returned_at?->toDateString(),
            'fine_amount' => (int) $loan->fine_amount,
            'is_late' => in_array($loan->status, ['borrowed', 'late'], true) && $loan->due_at && $loan->due_at->isPast(),
            'member' => $loan->member ? [
                'id' => $loan->member->id,
                'full_name' => $loan->member->full_name,
                'member_code' => $loan->member->member_code,
                'phone' => $loan->member->phone,
            ] : null,
            'book' => $loan->book ? [
                'id' => $loan->book->id,
                'title' => $loan->book->title,
                'author' => $loan->book->author,
            ] : null,
            'copy' => $loan->copy ? [
                'id' => $loan->copy->id,
                'copy_code' => $loan->copy->copy_code,
                'barcode' => $loan->copy->barcode,
            ] : null,
            'recorded_by' => $loan->recordedBy?->name ?: 'سیستم',
            'links' => [
                'member' => $loan->member ? route('library.members.show', $loan->member) : null,
                'edit' => $canWrite ? route('library.loans.edit', $loan) : null,
            ],
        ];
    }

    private function summaryPayload(): array
    {
        return [
            'active' => BookLoan::query()->whereIn('status', ['borrowed', 'late'])->count(),
            'late' => BookLoan::query()->where('status', 'late')->count(),
            'returned' => BookLoan::query()->where('status', 'returned')->count(),
            'lost' => BookLoan::query()->where('status', 'lost')->count(),
        ];
    }

    private function syncOverdueLoans(): void
    {
        BookLoan::query()
            ->where('status', 'borrowed')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', today())
            ->update(['status' => 'late']);
    }

    private function statusLabels(): array
    {
        return [
            'borrowed' => 'امانت',
            'late' => 'دیرشده',
            'returned' => 'برگشت‌شده',
            'lost' => 'گم‌شده',
        ];
    }
}
