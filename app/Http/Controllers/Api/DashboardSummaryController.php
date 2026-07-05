<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\DormRoom;
use App\Models\DormStudent;
use App\Models\FinanceTransaction;
use App\Models\LibraryMember;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardSummaryController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'role' => $user->role,
            'generated_at' => now()->toIso8601String(),
            'cards' => $user->canAccessAdmin()
                ? $this->adminCards()
                : $this->roleCards($user),
        ]);
    }

    private function adminCards(): array
    {
        $rooms = DormRoom::query()
            ->withCount(['activeStudents as occupied_beds'])
            ->get();

        $totalBeds = (int) $rooms->sum('capacity');
        $occupiedBeds = (int) $rooms->sum('occupied_beds');
        $freeBeds = max(0, $totalBeds - $occupiedBeds);

        return [
            [
                'key' => 'active_students',
                'label' => 'محصلین فعال',
                'value' => DormStudent::where('status', 'active')->count(),
                'hint' => 'محصلین فعلی لیلیه',
            ],
            [
                'key' => 'free_beds',
                'label' => 'تخت‌های خالی',
                'value' => $freeBeds,
                'hint' => $totalBeds.' تخت مجموعی، '.$occupiedBeds.' اشغال‌شده',
            ],
            [
                'key' => 'library_members',
                'label' => 'اعضای کتابخانه',
                'value' => LibraryMember::where('status', 'active')->count(),
                'hint' => Book::count().' عنوان کتاب ثبت‌شده',
            ],
            [
                'key' => 'active_loans',
                'label' => 'امانت‌های فعال',
                'value' => BookLoan::whereIn('status', ['borrowed', 'late'])->count(),
                'hint' => 'کتاب‌های امانت یا دیرشده',
            ],
        ];
    }

    private function roleCards(User $user): array
    {
        if ($user->role === User::ROLE_LIBRARIAN) {
            $libraryFinance = FinanceTransaction::query()
                ->whereHas('category', fn ($query) => $query->where('name', 'like', 'کتابخانه -%'));

            return [
                [
                    'key' => 'active_members',
                    'label' => 'اعضای فعال',
                    'value' => LibraryMember::where('status', 'active')->count(),
                    'hint' => 'اعضایی با وضعیت فعال',
                ],
                [
                    'key' => 'book_titles',
                    'label' => 'عنوان‌های کتاب',
                    'value' => Book::count(),
                    'hint' => (int) Book::sum('available_copies').' نسخه قابل امانت',
                ],
                [
                    'key' => 'active_loans',
                    'label' => 'امانت‌های فعال',
                    'value' => BookLoan::whereIn('status', ['borrowed', 'late'])->count(),
                    'hint' => 'کتاب‌های امانت یا دیرشده',
                ],
                [
                    'key' => 'today_income',
                    'label' => 'درآمد امروز',
                    'value' => (int) (clone $libraryFinance)->where('type', 'income')->whereDate('transaction_date', today())->sum('amount'),
                    'hint' => 'ثبت‌های مالی کتابخانه',
                ],
            ];
        }

        return [
            [
                'key' => 'available_sections',
                'label' => 'بخش‌های در دسترس',
                'value' => count($this->navigationFor($user)),
                'hint' => 'بخش‌های مجاز برای این نقش',
            ],
        ];
    }

    private function navigationFor(User $user): array
    {
        return match ($user->role) {
            User::ROLE_STUDENT_REPRESENTATIVE => ['students', 'representative'],
            User::ROLE_PURCHASER => ['students', 'purchaser'],
            User::ROLE_GUARD => ['students'],
            default => [],
        };
    }
}
