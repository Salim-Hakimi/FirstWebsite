<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\FoodFinance;
use App\Models\LibraryMember;
use App\Models\MembershipCard;
use App\Models\StudentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class TransparencyController extends Controller
{
    public function __invoke(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'period' => ['nullable', 'string', 'max:80'],
        ]);

        if (! Schema::hasTable('food_finances') || ! Schema::hasTable('student_collections')) {
            return view('transparency', $this->emptyData($filters));
        }

        $foodQuery = FoodFinance::query()->with(['student.room', 'recordedBy']);
        $representativeQuery = StudentCollection::query()->with(['student.room', 'recordedBy']);
        $this->applyFoodFilters($foodQuery, $filters);
        $this->applyRepresentativeFilters($representativeQuery, $filters);

        $foodRecords = $foodQuery->latest('recorded_at')->latest()->get();
        $representativeRecords = $representativeQuery->latest('collected_at')->latest()->get();

        $foodCollected = (int) $foodRecords->whereIn('type', $this->foodIncomeTypes())->sum('amount');
        $foodExpenses = (int) $foodRecords->where('type', 'expense')->sum('amount');
        $representativeIncome = (int) $representativeRecords->whereIn('type', $this->representativeIncomeTypes())->sum('amount');
        $representativeExpenses = (int) $representativeRecords->where('type', 'expense')->sum('amount');
        $libraryRevenue = $this->libraryRevenue($filters);

        return view('transparency', [
            'filters' => $filters,
            'foodCollected' => $foodCollected,
            'foodExpenses' => $foodExpenses,
            'foodBalance' => $foodCollected - $foodExpenses,
            'foodTypeLabels' => $this->foodTypeLabels(),
            'foodBreakdown' => $foodRecords->groupBy('type')->map(fn ($items) => (int) $items->sum('amount')),
            'representativeIncome' => $representativeIncome,
            'representativeExpenses' => $representativeExpenses,
            'representativeBalance' => $representativeIncome - $representativeExpenses,
            'representativeTotals' => $representativeRecords->groupBy('type')->map(fn ($items) => (int) $items->sum('amount')),
            'representativeTypeLabels' => $this->representativeTypeLabels(),
            'libraryRevenue' => $libraryRevenue,
            'libraryStats' => $this->libraryStats($filters),
            'totalIncome' => $foodCollected + $representativeIncome + $libraryRevenue,
            'totalExpenses' => $foodExpenses + $representativeExpenses,
            'overallBalance' => ($foodCollected + $representativeIncome + $libraryRevenue) - ($foodExpenses + $representativeExpenses),
            'ledgerRows' => $this->ledgerRows($foodRecords, $representativeRecords),
            'monthlyRows' => $this->monthlyRows($foodRecords, $representativeRecords),
        ]);
    }

    private function applyFoodFilters($query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('recorded_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('recorded_at', '<=', $date))
            ->when($filters['period'] ?? null, fn ($query, $period) => $query->where('period', 'like', "%{$period}%"));
    }

    private function applyRepresentativeFilters($query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('collected_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('collected_at', '<=', $date))
            ->when($filters['period'] ?? null, fn ($query, $period) => $query->where('period', 'like', "%{$period}%"));
    }

    private function libraryRevenue(array $filters): int
    {
        if (! Schema::hasTable('membership_cards')) {
            return 0;
        }

        return (int) MembershipCard::query()
            ->where('scope', 'library')
            ->where('payment_status', 'paid')
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('paid_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('paid_at', '<=', $date))
            ->sum('fee_amount');
    }

    private function libraryStats(array $filters): array
    {
        if (! Schema::hasTable('library_members') || ! Schema::hasTable('books') || ! Schema::hasTable('book_loans')) {
            return [
                'active_members' => 0,
                'books' => 0,
                'available_copies' => 0,
                'active_loans' => 0,
                'late_loans' => 0,
                'returned_loans' => 0,
            ];
        }

        $loanQuery = BookLoan::query();
        $loanQuery
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('borrowed_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('borrowed_at', '<=', $date));

        return [
            'active_members' => LibraryMember::where('status', 'active')->count(),
            'books' => Book::count(),
            'available_copies' => (int) Book::sum('available_copies'),
            'active_loans' => (clone $loanQuery)->whereIn('status', ['borrowed', 'late'])->count(),
            'late_loans' => (clone $loanQuery)->where('status', 'late')->orWhere(function ($query) {
                $query->where('status', 'borrowed')->whereDate('due_at', '<', today());
            })->count(),
            'returned_loans' => (clone $loanQuery)->where('status', 'returned')->count(),
        ];
    }

    private function ledgerRows($foodRecords, $representativeRecords)
    {
        $foodRows = $foodRecords->map(function (FoodFinance $record) {
            return [
                'date' => $record->recorded_at,
                'section' => 'خرج‌آور',
                'type' => $this->foodTypeLabels()[$record->type] ?? $record->type,
                'description' => $record->description ?: ($record->vendor_or_source ?: 'بدون شرح'),
                'person' => $record->student?->full_name ?: 'مصرف عمومی',
                'direction' => $record->type === 'expense' ? 'expense' : 'income',
                'amount' => (int) $record->amount,
            ];
        });

        $representativeRows = $representativeRecords->map(function (StudentCollection $record) {
            return [
                'date' => $record->collected_at,
                'section' => 'نماینده',
                'type' => $this->representativeTypeLabels()[$record->type] ?? $record->type,
                'description' => $record->notes ?: 'بدون یادداشت',
                'person' => $record->student?->full_name ?: 'مصرف عمومی',
                'direction' => $record->type === 'expense' ? 'expense' : 'income',
                'amount' => (int) $record->amount,
            ];
        });

        return $foodRows
            ->concat($representativeRows)
            ->sortByDesc(fn ($row) => optional($row['date'])->timestamp ?? 0)
            ->take(40)
            ->values();
    }

    private function monthlyRows($foodRecords, $representativeRecords)
    {
        return $this->ledgerRows($foodRecords, $representativeRecords)
            ->groupBy(fn ($row) => $row['date']?->format('Y-m') ?: 'بدون تاریخ')
            ->map(function ($items, $month) {
                $income = (int) $items->where('direction', 'income')->sum('amount');
                $expense = (int) $items->where('direction', 'expense')->sum('amount');

                return [
                    'month' => $month,
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense,
                    'count' => $items->count(),
                ];
            })
            ->values();
    }

    private function representativeTypeLabels(): array
    {
        return [
            'monthly_fee' => 'پول ماهانه',
            'electricity' => 'پول برق',
            'fine' => 'جریمه',
            'water' => 'پول آب',
            'expense' => 'مصرف نماینده',
        ];
    }

    private function foodTypeLabels(): array
    {
        return [
            'contribution' => 'جمع‌آوری پول غذا',
            'weekly_food' => 'پول هفته‌وار غذا',
            'monthly_fee' => 'پول ماهانه',
            'electricity' => 'پول برق',
            'water' => 'پول آب',
            'expense' => 'مصرف و خرید',
        ];
    }

    private function foodIncomeTypes(): array
    {
        return ['contribution', 'weekly_food', 'monthly_fee', 'electricity', 'water'];
    }

    private function representativeIncomeTypes(): array
    {
        return ['monthly_fee', 'electricity', 'fine', 'water'];
    }

    private function emptyData(array $filters): array
    {
        return [
            'filters' => $filters,
            'foodCollected' => 0,
            'foodExpenses' => 0,
            'foodBalance' => 0,
            'foodTypeLabels' => $this->foodTypeLabels(),
            'foodBreakdown' => collect(),
            'representativeIncome' => 0,
            'representativeExpenses' => 0,
            'representativeBalance' => 0,
            'representativeTotals' => collect(),
            'representativeTypeLabels' => $this->representativeTypeLabels(),
            'libraryRevenue' => 0,
            'libraryStats' => $this->libraryStats($filters),
            'totalIncome' => 0,
            'totalExpenses' => 0,
            'overallBalance' => 0,
            'ledgerRows' => collect(),
            'monthlyRows' => collect(),
        ];
    }
}
