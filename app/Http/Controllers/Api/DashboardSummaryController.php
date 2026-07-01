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
                'label' => 'Active students',
                'value' => DormStudent::where('status', 'active')->count(),
                'hint' => 'Current active dorm students',
            ],
            [
                'key' => 'free_beds',
                'label' => 'Free beds',
                'value' => $freeBeds,
                'hint' => $totalBeds.' total beds, '.$occupiedBeds.' occupied',
            ],
            [
                'key' => 'library_members',
                'label' => 'Library members',
                'value' => LibraryMember::where('status', 'active')->count(),
                'hint' => Book::count().' book titles registered',
            ],
            [
                'key' => 'active_loans',
                'label' => 'Active loans',
                'value' => BookLoan::whereIn('status', ['borrowed', 'late'])->count(),
                'hint' => 'Borrowed or late books',
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
                    'label' => 'Active members',
                    'value' => LibraryMember::where('status', 'active')->count(),
                    'hint' => 'Members with active status',
                ],
                [
                    'key' => 'book_titles',
                    'label' => 'Book titles',
                    'value' => Book::count(),
                    'hint' => (int) Book::sum('available_copies').' available copies',
                ],
                [
                    'key' => 'active_loans',
                    'label' => 'Active loans',
                    'value' => BookLoan::whereIn('status', ['borrowed', 'late'])->count(),
                    'hint' => 'Borrowed or late books',
                ],
                [
                    'key' => 'today_income',
                    'label' => 'Today income',
                    'value' => (int) (clone $libraryFinance)->where('type', 'income')->whereDate('transaction_date', today())->sum('amount'),
                    'hint' => 'Library finance records',
                ],
            ];
        }

        return [
            [
                'key' => 'available_sections',
                'label' => 'Available sections',
                'value' => count($this->navigationFor($user)),
                'hint' => 'Sections allowed for this role',
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
