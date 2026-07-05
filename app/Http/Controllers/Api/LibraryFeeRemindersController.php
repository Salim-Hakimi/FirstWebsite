<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LibraryMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryFeeRemindersController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ]);

        $status = $validated['status'] ?? 'due_soon';
        $perPage = (int) ($validated['per_page'] ?? 10);

        $members = LibraryMember::query()
            ->with(['membershipCards' => fn ($query) => $query->where('scope', 'library')->latest('expires_at')])
            ->where('status', 'active')
            ->when($status === 'due_soon', fn ($query) => $query->whereDate('next_payment_due_at', '<=', today()->addDays(3)))
            ->when($status === 'overdue', fn ($query) => $query->whereDate('next_payment_due_at', '<', today()))
            ->when($validated['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('next_payment_due_at')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $members->getCollection()
                ->map(fn (LibraryMember $member): array => $this->memberPayload($member))
                ->values(),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'from' => $members->firstItem(),
                'to' => $members->lastItem(),
            ],
            'summary' => [
                'due_soon' => $this->baseReminderQuery()
                    ->whereDate('next_payment_due_at', '<=', today()->addDays(3))
                    ->count(),
                'overdue' => $this->baseReminderQuery()
                    ->whereDate('next_payment_due_at', '<', today())
                    ->count(),
            ],
            'filters' => [
                'status' => $status,
                'q' => $validated['q'] ?? '',
                'statuses' => $this->statusLabels(),
            ],
        ]);
    }

    private function baseReminderQuery()
    {
        return LibraryMember::query()->where('status', 'active');
    }

    private function memberPayload(LibraryMember $member): array
    {
        $fine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
        $balance = (int) $member->membership_fee + $fine;
        $isOverdue = $member->next_payment_due_at && $member->next_payment_due_at->isPast();
        $message = $this->reminderMessage($member, $fine, $balance, $isOverdue);
        $whatsappDigits = $this->whatsappDigits($member->phone);

        return [
            'id' => $member->id,
            'member_code' => $member->member_code,
            'full_name' => $member->full_name,
            'father_name' => $member->father_name,
            'phone' => $member->phone,
            'membership_fee' => (int) $member->membership_fee,
            'fine_amount' => $fine,
            'balance' => $balance,
            'is_overdue' => (bool) $isOverdue,
            'next_payment_due_at' => $member->next_payment_due_at?->toDateString(),
            'last_fee_reminder_at' => $member->last_fee_reminder_at?->toDateString(),
            'message' => $message,
            'profile_photo_url' => $member->profile_photo_path ? asset('storage/'.$member->profile_photo_path) : null,
            'links' => [
                'show' => route('library.members.show', $member),
                'whatsapp' => $whatsappDigits ? 'https://wa.me/'.$whatsappDigits.'?text='.rawurlencode($message) : null,
            ],
        ];
    }

    private function reminderMessage(LibraryMember $member, int $fine, int $balance, bool $isOverdue): string
    {
        $dueDate = $member->next_payment_due_at?->format('Y-m-d') ?: 'ثبت نشده';

        if ($isOverdue) {
            return "سلام {$member->full_name} عزیز، تاریخ پرداخت فیس ماهانه کتابخانه شما گذشته است. مبلغ قابل پرداخت فعلی {$balance} افغانی است که شامل {$fine} افغانی جریمه می‌باشد.";
        }

        return "سلام {$member->full_name} عزیز، فیس ماهانه کتابخانه شما تا تاریخ {$dueDate} باید پرداخت شود. در صورت تأخیر، روزانه ".((int) $member->monthly_fee_daily_fine).' افغانی جریمه می‌شود.';
    }

    private function whatsappDigits(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone ?? '');

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            return '93'.substr($digits, 1);
        }

        return $digits;
    }

    private function statusLabels(): array
    {
        return [
            'due_soon' => 'نزدیک به سررسید',
            'overdue' => 'گذشته از سررسید',
            'all' => 'همه پرداخت‌نشده‌ها',
        ];
    }
}
