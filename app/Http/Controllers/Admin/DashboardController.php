<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\DormRoom;
use App\Models\DormStudent;
use App\Models\LibraryMember;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $usersByStatus = User::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $rooms = DormRoom::query()
            ->withCount(['activeStudents as occupied_beds'])
            ->get();
        $totalBeds = (int) $rooms->sum('capacity');
        $occupiedBeds = (int) $rooms->sum('occupied_beds');
        $freeBeds = max(0, $totalBeds - $occupiedBeds);
        $occupancyRate = $totalBeds > 0 ? round(($occupiedBeds / $totalBeds) * 100) : 0;

        $studentStatuses = DormStudent::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeLoans = BookLoan::query()->whereIn('status', ['borrowed', 'late'])->count();
        $overdueLoans = BookLoan::query()
            ->with(['member', 'book'])
            ->whereIn('status', ['borrowed', 'late'])
            ->whereDate('due_at', '<', now()->toDateString())
            ->latest('due_at')
            ->limit(5)
            ->get();

        $monthlyRegistrationStudents = DormStudent::query()
            ->whereIn('registration_payment_status', ['paid', 'partial'])
            ->whereNotIn('status', ['waiting', 'on_hold', 'rejected'])
            ->where(function ($query) use ($monthStart, $monthEnd) {
                $query
                    ->whereBetween('registration_paid_at', [$monthStart, $monthEnd])
                    ->orWhere(function ($query) use ($monthStart, $monthEnd) {
                        $query
                            ->whereNull('registration_paid_at')
                            ->whereBetween('created_at', [$monthStart, $monthEnd]);
                    });
            })
            ->get();

        $monthlyGuaranteeDeposits = (int) $monthlyRegistrationStudents->sum(fn ($student) => $student->guarantee_deposit_amount ?? 1000);
        $monthlyDormRegistrationFees = (int) $monthlyRegistrationStudents->sum(fn ($student) => $student->dorm_expense_fee_amount ?? 1000);
        $monthlyDormCardFees = (int) $monthlyRegistrationStudents->sum(fn ($student) => $student->registration_card_fee_amount ?? 50);
        $monthlyRegistrationIncome = $monthlyDormRegistrationFees + $monthlyDormCardFees;

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => (int) $usersByStatus->get(User::STATUS_ACTIVE, 0),
            'pendingUsers' => (int) $usersByStatus->get(User::STATUS_PENDING, 0),
            'totalRooms' => $rooms->count(),
            'totalBeds' => $totalBeds,
            'occupiedBeds' => $occupiedBeds,
            'freeBeds' => $freeBeds,
            'occupancyRate' => $occupancyRate,
            'activeStudents' => (int) $studentStatuses->get('active', 0),
            'waitingStudents' => (int) $studentStatuses->get('waiting', 0),
            'onHoldStudents' => (int) $studentStatuses->get('on_hold', 0),
            'libraryMembers' => LibraryMember::query()->where('status', 'active')->count(),
            'bookTitles' => Book::count(),
            'availableBooks' => (int) Book::sum('available_copies'),
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'monthlyRegistrationCount' => $monthlyRegistrationStudents->count(),
            'monthlyGuaranteeDeposits' => $monthlyGuaranteeDeposits,
            'monthlyDormRegistrationFees' => $monthlyDormRegistrationFees,
            'monthlyDormCardFees' => $monthlyDormCardFees,
            'monthlyRegistrationIncome' => $monthlyRegistrationIncome,
            'recentStudents' => DormStudent::query()
                ->latest()
                ->limit(5)
                ->get(),
            'waitingApplicants' => DormStudent::query()
                ->whereIn('status', ['waiting', 'on_hold'])
                ->latest('application_date')
                ->latest()
                ->limit(5)
                ->get(),
            'crowdedRooms' => $rooms
                ->filter(fn ($room) => $room->capacity > 0 && ($room->occupied_beds / $room->capacity) >= .8)
                ->sortByDesc('occupied_beds')
                ->take(5)
                ->values(),
            'recentUsers' => User::query()
                ->latest()
                ->limit(5)
                ->get(),
            'roleLabels' => User::roleOptions(),
            'statusLabels' => User::statusOptions(),
        ]);
    }
}
