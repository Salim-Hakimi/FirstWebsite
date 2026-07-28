<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BookLoan;
use App\Models\FinanceAuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use App\Models\LibraryMember;
use App\Models\MembershipCard;
use App\Models\User;
use App\Support\Audit;
use App\Support\Locale;
use App\Support\SecurityRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $this->syncOverdueLoans();

        $filters = $this->validateMemberFilters($request);
        $financeFilters = $this->validateLibraryFinanceFilters($request);
        $librarySection = $request->route('library_section')
            ?? ($request->routeIs('library.finance.*') ? 'finance' : 'overview');

        $members = $this->libraryMembersQuery($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $activeLoanStatuses = ['borrowed', 'late'];
        $followUpMembers = LibraryMember::query()
            ->whereDate('next_payment_due_at', '<=', today()->addDays(3))
            ->orderBy('next_payment_due_at')
            ->get()
            ->each(fn (LibraryMember $member) => $this->syncMonthlyFine($member));
        $expiredCards = MembershipCard::query()
            ->with('cardable')
            ->where('scope', 'library')
            ->whereDate('expires_at', '<=', today())
            ->where('payment_status', '!=', 'paid')
            ->latest('expires_at')
            ->get();
        $financeQuery = $this->libraryFinanceTransactions()
            ->with(['category', 'recordedBy']);
        $this->applyLibraryFinanceFilters($financeQuery, $financeFilters);

        $categorySummaryQuery = $this->libraryFinanceTransactions()
            ->with('category')
            ->whereNotNull('finance_category_id');
        $this->applyLibraryFinanceFilters($categorySummaryQuery, array_merge($financeFilters, ['finance_category' => null]));

        return view('library.index', [
            'librarySection' => $librarySection,
            'members' => $members,
            'filters' => $filters,
            'libraryFinanceFilters' => $financeFilters,
            'memberStatusLabels' => $this->memberStatusLabels(),
            'books' => Book::query()->withCount('copies')->latest()->limit(30)->get(),
            'loans' => BookLoan::query()->with(['member', 'book', 'copy'])->latest()->limit(40)->get(),
            'activeMemberCount' => LibraryMember::where('status', 'active')->count(),
            'bookTitleCount' => Book::count(),
            'availableCopyCount' => (int) Book::sum('available_copies'),
            'activeLoanCount' => BookLoan::whereIn('status', $activeLoanStatuses)->count(),
            'overdueLoans' => BookLoan::query()
                ->with(['member', 'book'])
                ->whereIn('status', $activeLoanStatuses)
                ->whereDate('due_at', '<', today())
                ->orderBy('due_at')
                ->limit(6)
                ->get(),
            'canWriteLibrary' => $this->canWriteLibrary(),
            'activeMembers' => LibraryMember::where('status', 'active')->orderBy('full_name')->get(),
            'availableBooks' => Book::with('availableCopies')->where('available_copies', '>', 0)->orderBy('title')->get(),
            'expiringMembers' => $followUpMembers,
            'expiredCards' => $expiredCards,
            'libraryIncomeTotal' => (int) $this->libraryFinanceTransactions()->where('type', 'income')->sum('amount'),
            'libraryExpenseTotal' => (int) $this->libraryFinanceTransactions()->where('type', 'expense')->sum('amount'),
            'libraryTodayIncome' => (int) $this->libraryFinanceTransactions()->where('type', 'income')->whereDate('transaction_date', today())->sum('amount'),
            'libraryMonthIncome' => (int) $this->libraryFinanceTransactions()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount'),
            'libraryMonthExpense' => (int) $this->libraryFinanceTransactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                ->sum('amount'),
            'libraryFinanceRecords' => $financeQuery
                ->latest('transaction_date')
                ->latest()
                ->limit(80)
                ->get(),
            'libraryFinanceCategorySummaries' => $categorySummaryQuery
                ->selectRaw('finance_category_id, type, SUM(amount) as total_amount, COUNT(*) as records_count')
                ->groupBy('finance_category_id', 'type')
                ->orderByDesc('total_amount')
                ->limit(8)
                ->get(),
            'libraryFinanceCategoryOptions' => $this->libraryFinanceCategoryOptions(),
            'libraryFinancePeriods' => $this->libraryFinancePeriodReports(),
            'libraryPaymentMethods' => $this->libraryPaymentMethods(),
            'libraryFinanceCategories' => $this->libraryFinanceCategoryLabels(),
        ]);
    }

    public function membersExport(Request $request): StreamedResponse
    {
        $filters = $this->validateMemberFilters($request);
        $filename = 'library-members-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Member code',
                'Full name',
                'Father name',
                'Phone',
                'Email',
                'Tazkira',
                'Education place',
                'Department or grade',
                'Monthly fee AFN',
                'Daily fine AFN',
                'Current fine AFN',
                'Fee balance AFN',
                'Payment status',
                'Last paid at',
                'Next payment due at',
                'Membership expires at',
                'Status',
                'Last reminder at',
            ]);

            $this->libraryMembersQuery($filters)
                ->orderBy('full_name')
                ->chunk(200, function ($members) use ($handle): void {
                    foreach ($members as $member) {
                        $fine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());

                        fputcsv($handle, [
                            $member->member_code,
                            $member->full_name,
                            $member->father_name,
                            $member->phone,
                            $member->email,
                            $member->tazkira_number,
                            $member->education_place,
                            $member->department_or_grade,
                            (int) $member->membership_fee,
                            (int) $member->monthly_fee_daily_fine,
                            $fine,
                            (int) $member->membership_fee + $fine,
                            $member->payment_status,
                            $member->last_paid_at?->format('Y-m-d'),
                            $member->next_payment_due_at?->format('Y-m-d'),
                            $member->membership_expires_at?->format('Y-m-d'),
                            $member->status,
                            $member->last_fee_reminder_at?->format('Y-m-d'),
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function financeExport(Request $request): StreamedResponse
    {
        $filters = $this->validateLibraryFinanceFilters($request);
        $filename = 'library-finance-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Type',
                'Category',
                'Person or source',
                'Receipt number',
                'Payment method',
                'Amount AFN',
                'Description',
                'Recorded by',
            ]);

            $query = $this->libraryFinanceTransactions()
                ->with(['category', 'recordedBy'])
                ->orderByDesc('transaction_date')
                ->orderByDesc('id');
            $this->applyLibraryFinanceFilters($query, $filters);

            $query->chunk(200, function ($records) use ($handle): void {
                foreach ($records as $record) {
                    fputcsv($handle, [
                        $record->transaction_date?->format('Y-m-d'),
                        $record->type,
                        $record->category?->name,
                        $record->source_or_payee ?: $record->payer_name ?: $record->payee_name,
                        $record->receipt_number,
                        $record->payment_method,
                        (int) $record->amount,
                        $record->description ?: $record->notes,
                        $record->recordedBy?->name,
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function financeReceipt(FinanceTransaction $transaction): View
    {
        abort_unless($this->isLibraryFinanceTransaction($transaction), 404);

        $transaction->load(['category', 'recordedBy', 'attachments']);

        return view('admin.finance.receipt', [
            'transaction' => $transaction,
            'paymentMethods' => $this->libraryPaymentMethods(),
            'statusLabels' => $this->financeStatusLabels(),
            'backRoute' => 'library.index',
            'receiptTitle' => 'کتابخانه فانوس',
            'receiptSubtitle' => 'رسید مالی کتابخانه',
        ]);
    }

    public function showMember(LibraryMember $member): View
    {
        $this->syncOverdueLoans($member);

        $member->load([
            'registeredBy',
            'membershipCards' => fn ($query) => $query->where('scope', 'library')->latest('expires_at'),
            'loans.book',
            'loans.copy',
            'loans.recordedBy',
        ]);
        $this->syncMonthlyFine($member);
        $openBillingMonth = $this->openLibraryBillingMonth($member);

        return view('library.members.show', [
            'member' => $member,
            'memberStatusLabels' => $this->memberStatusLabels(),
            'loanStatusLabels' => $this->loanStatusLabels(),
            'canWriteLibrary' => $this->canWriteLibrary(),
            'activeLoanCount' => $member->loans->whereIn('status', ['borrowed', 'late'])->count(),
            'returnedLoanCount' => $member->loans->where('status', 'returned')->count(),
            'fineTotal' => (int) $member->loans->sum('fine_amount'),
            'monthlyFeeFine' => max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine()),
            'monthlyFeeBalance' => $member->monthlyFeeBalance(),
            'monthlyFeeReminderText' => $this->monthlyFeeReminderText($member),
            'openBillingMonth' => $openBillingMonth,
            'monthlyBillRows' => $this->libraryMonthlyBillRows($member, $openBillingMonth),
        ]);
    }

    public function inventoryReport(Request $request): View
    {
        $filters = $this->validateInventoryFilters($request);

        $statusCounts = BookCopy::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $copies = $this->inventoryCopiesQuery($filters)
            ->orderBy('status')
            ->orderBy('shelf_code')
            ->orderBy('copy_code')
            ->get();

        $inventoryBooks = $copies
            ->groupBy('book_id')
            ->map(function ($bookCopies) {
                $book = $bookCopies->first()->book;

                return [
                    'book' => $book,
                    'matching_copies' => $bookCopies->count(),
                    'available' => $bookCopies->where('status', 'available')->count(),
                    'on_loan' => $bookCopies->where('status', 'on_loan')->count(),
                    'damaged' => $bookCopies->where('status', 'damaged')->count(),
                    'lost' => $bookCopies->where('status', 'lost')->count(),
                    'archived' => $bookCopies->where('status', 'archived')->count(),
                    'shelves' => $bookCopies->pluck('shelf_code')->filter()->unique()->values(),
                    'value' => (int) $bookCopies->sum('purchase_price'),
                ];
            })
            ->sortBy(fn ($row) => $row['book']?->title ?? '')
            ->values();

        $problemCopies = BookCopy::query()
            ->with('book')
            ->whereIn('status', ['damaged', 'lost'])
            ->latest()
            ->limit(12)
            ->get();

        $emptyBooks = Book::query()
            ->where('available_copies', '<', 1)
            ->orderBy('title')
            ->limit(12)
            ->get();

        return view('library.inventory.report', [
            'filters' => $filters,
            'copies' => $copies,
            'inventoryBooks' => $inventoryBooks,
            'statusCounts' => $statusCounts,
            'problemCopies' => $problemCopies,
            'emptyBooks' => $emptyBooks,
            'canWriteLibrary' => $this->canWriteLibrary(),
            'totalCopies' => BookCopy::count(),
            'lostValue' => (int) BookCopy::where('status', 'lost')->sum('purchase_price'),
            'damagedValue' => (int) BookCopy::where('status', 'damaged')->sum('purchase_price'),
        ]);
    }

    public function inventoryExport(Request $request): StreamedResponse
    {
        $filters = $this->validateInventoryFilters($request);
        $filename = 'library-inventory-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($filters): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Copy code',
                'Barcode',
                'Title',
                'Author',
                'ISBN',
                'Category',
                'Shelf',
                'Status',
                'Condition',
                'Purchase price AFN',
                'Notes',
            ]);

            $this->inventoryCopiesQuery($filters)
                ->orderBy('status')
                ->orderBy('shelf_code')
                ->orderBy('copy_code')
                ->chunk(200, function ($copies) use ($handle): void {
                    foreach ($copies as $copy) {
                        fputcsv($handle, [
                            $copy->copy_code,
                            $copy->barcode,
                            $copy->book?->title,
                            $copy->book?->author,
                            $copy->book?->isbn,
                            $copy->book?->category,
                            $copy->shelf_code,
                            $copy->status,
                            $copy->condition,
                            (int) $copy->purchase_price,
                            $copy->notes,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function feeReminders(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['due_soon', 'overdue', 'all'])],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
        $status = $filters['status'] ?? 'due_soon';

        $members = LibraryMember::query()
            ->with(['membershipCards' => fn ($query) => $query->where('scope', 'library')->latest('expires_at')])
            ->where('status', 'active')
            ->when($status === 'due_soon', fn ($query) => $query->whereDate('next_payment_due_at', '<=', today()->addDays(3)))
            ->when($status === 'overdue', fn ($query) => $query->whereDate('next_payment_due_at', '<', today()))
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('next_payment_due_at')
            ->get()
            ->each(fn (LibraryMember $member) => $this->syncMonthlyFine($member));

        return view('library.fee-reminders.index', [
            'members' => $members,
            'filters' => $filters,
            'status' => $status,
            'canWriteLibrary' => $this->canWriteLibrary(),
            'dueSoonCount' => LibraryMember::query()
                ->where('status', 'active')
                ->whereDate('next_payment_due_at', '<=', today()->addDays(3))
                ->count(),
            'overdueCount' => LibraryMember::query()
                ->where('status', 'active')
                ->whereDate('next_payment_due_at', '<', today())
                ->count(),
        ]);
    }

    public function markFeeReminderSent(LibraryMember $member): RedirectResponse
    {
        $member->forceFill([
            'last_fee_reminder_at' => today(),
        ])->save();

        return back()->with('status', 'یادآوری فیس برای '.$member->full_name.' به عنوان ارسال‌شده ثبت شد.');
    }

    public function storeFinanceRecord(Request $request): RedirectResponse
    {
        $labels = $this->libraryFinanceCategoryLabels();
        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'category_key' => ['required', Rule::in(array_keys($labels))],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(array_keys($this->libraryPaymentMethods()))],
            'source_or_payee' => ['nullable', 'string', 'max:180'],
            'receipt_number' => ['nullable', 'string', 'max:80', Rule::unique('finance_transactions', 'receipt_number')],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category = $labels[$validated['category_key']];

        if ($category['type'] !== $validated['type']) {
            return back()
                ->withErrors(['category_key' => 'دسته‌بندی انتخاب‌شده با نوع ثبت مالی همخوانی ندارد.'])
                ->withInput();
        }

        $transaction = $this->recordLibraryFinance(
            $validated['type'],
            $category['label'],
            (int) $validated['amount'],
            $validated['source_or_payee'] ?? 'کتابخانه فانوس',
            $validated['description'] ?? $category['label'],
            $request,
            $validated['transaction_date'],
            $validated['payment_method'],
            $validated['receipt_number'] ?? null
        );

        return back()->with('status', 'ثبت مالی کتابخانه ذخیره شد: '.$transaction->receipt_number);
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $validated = $this->validateMember($request);
        $issueCard = $request->boolean('issue_card');

        $joinedAt = $validated['joined_at'] ?? now()->toDateString();
        $paymentStatus = $validated['payment_status'] ?? 'unpaid';
        $validated['profile_photo_path'] = $this->storeProfilePhoto($request);
        $validated['monthly_fee_daily_fine'] = $validated['monthly_fee_daily_fine'] ?? 20;
        $validated['monthly_fee_fine_amount'] = $paymentStatus === 'paid' ? 0 : ($validated['monthly_fee_fine_amount'] ?? 0);
        unset($validated['issue_card'], $validated['profile_photo'], $validated['remove_profile_photo']);

        $member = LibraryMember::create(array_merge($validated, [
            'registered_by' => $request->user()->id,
            'member_code' => ($validated['member_code'] ?? null) ?: $this->nextCode('LIB-M'),
            'joined_at' => $joinedAt,
            'membership_expires_at' => Carbon::parse($joinedAt)->addMonths(6),
            'last_paid_at' => $paymentStatus === 'paid' ? $joinedAt : null,
            'next_payment_due_at' => Carbon::parse($joinedAt)->addMonth(),
        ]));
        Audit::record('library_member_created', $member, [], $member->only(['member_code', 'full_name', 'phone', 'status', 'payment_status']), $request);

        if ($issueCard && $paymentStatus === 'paid' && (int) $member->membership_fee > 0 && ! $this->hasLibraryMonthlyPayment($member, $joinedAt)) {
            $billingMonth = Carbon::parse($joinedAt)->startOfMonth();
            $this->recordLibraryFinance(
                'income',
                'فیس ماهانه کتابخانه',
                (int) $member->membership_fee,
                $member->full_name,
                'پرداخت ماهانه عضو کتابخانه: '.$member->member_code.' - '.Locale::month($billingMonth),
                $request,
                $joinedAt,
                'cash',
                $this->libraryMonthlyReceiptNumber($member, $billingMonth),
                $billingMonth
            );
        }

        if ($issueCard) {
            $card = $this->issueLibraryCard($member, $request);

            return redirect()->route('membership-cards.print', $card);
        }

        return back()->with('status', 'عضو کتاب‌خانه ثبت شد.');
    }

    public function editMember(LibraryMember $member): View
    {
        $libraryCard = $member->membershipCards()
            ->where('scope', 'library')
            ->latest('expires_at')
            ->first();

        return view('library.members.form', [
            'member' => $member,
            'libraryCard' => $libraryCard,
            'memberStatusLabels' => $this->memberStatusLabels(),
        ]);
    }

    public function updateMember(Request $request, LibraryMember $member): RedirectResponse
    {
        $validated = $this->validateMember($request, $member);
        $validated['profile_photo_path'] = $this->syncProfilePhoto($request, $member);
        $this->normalizeMemberPayment($validated, $member);
        unset($validated['issue_card'], $validated['profile_photo'], $validated['remove_profile_photo']);

        $oldValues = $member->only(['member_code', 'full_name', 'phone', 'status', 'payment_status', 'membership_fee', 'profile_photo_path']);
        $member->update($validated);
        Audit::record('library_member_updated', $member, $oldValues, $member->fresh()->only(['member_code', 'full_name', 'phone', 'status', 'payment_status', 'membership_fee', 'profile_photo_path']), $request);

        return redirect()->route('library.members.show', $member)->with('status', 'مشخصات عضو کتاب‌خانه ویرایش شد.');
    }

    public function issueMemberCard(Request $request, LibraryMember $member): RedirectResponse
    {
        $activeCard = $this->activeLibraryCard($member);

        if ($activeCard && ! $request->boolean('replace_card')) {
            if (! $activeCard->card_printed) {
                return redirect()
                    ->route('membership-cards.print', $activeCard)
                    ->with('status', 'برای این عضو کارت فعال وجود دارد؛ همان کارت برای چاپ آماده شد.');
            }

            return redirect()
                ->route('library.members.show', $member)
                ->with('error', 'این عضو تا '.$activeCard->expires_at?->format('Y/m/d').' کارت فعال دارد. در این مدت کارت جدید چاپ نمی‌شود؛ فقط بیل ماهانه صادر کنید.');
        }

        if ($request->boolean('replace_card') && ! $request->user()->canAccessAdmin()) {
            return redirect()
                ->route('library.members.show', $member)
                ->with('error', 'چاپ کارت بدل فقط توسط مدیر سیستم انجام می‌شود.');
        }

        $card = $this->issueLibraryCard($member, $request);

        return redirect()->route('membership-cards.print', $card);
    }

    public function recordMonthlyPayment(Request $request, LibraryMember $member): RedirectResponse
    {
        $validated = $request->validate([
            'billing_month' => ['nullable', 'date_format:Y-m'],
        ]);
        $this->syncMonthlyFine($member);

        $billingMonth = $this->parseLibraryBillingMonth($validated['billing_month'] ?? null)
            ?? $this->openLibraryBillingMonth($member);
        $billingDueDate = $this->libraryBillingDueDate($member, $billingMonth);
        $paidAt = today();
        $feeAmount = (int) $member->membership_fee;
        $fineAmount = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
        $receiptNumber = $this->libraryMonthlyReceiptNumber($member, $billingMonth);
        $billingLabel = Locale::month($billingMonth);

        if ($existingPayment = $this->libraryMonthlyPaymentTransaction($member, $billingMonth)) {
            session([
                'library_monthly_receipt' => [
                    'member_id' => $member->id,
                    'billing_month' => $billingMonth->format('Y-m'),
                    'billing_label' => $billingLabel,
                    'paid_at' => $existingPayment->transaction_date?->toDateString() ?? $paidAt->toDateString(),
                    'fee_amount' => min($feeAmount, (int) $existingPayment->amount),
                    'fine_amount' => max(0, (int) $existingPayment->amount - $feeAmount),
                    'total_amount' => (int) $existingPayment->amount,
                    'recorded_by' => $existingPayment->recordedBy?->name ?: $request->user()->name,
                    'receipt_number' => $existingPayment->receipt_number ?: $receiptNumber,
                ],
            ]);

            return redirect()
                ->route('library.members.monthly-payment.receipt', ['member' => $member, 'billing_month' => $billingMonth->format('Y-m')])
                ->with('status', 'بیل '.$billingLabel.' برای این عضو قبلاً ثبت شده است؛ ثبت تکراری ساخته نشد.');
        }

        $member->update([
            'payment_status' => 'paid',
            'last_paid_at' => $paidAt,
            'next_payment_due_at' => $billingDueDate->copy()->addMonth(),
            'monthly_fee_fine_amount' => 0,
        ]);

        session([
            'library_monthly_receipt' => [
                'member_id' => $member->id,
                'billing_month' => $billingMonth->format('Y-m'),
                'billing_label' => $billingLabel,
                'paid_at' => $paidAt->toDateString(),
                'fee_amount' => $feeAmount,
                'fine_amount' => $fineAmount,
                'total_amount' => $feeAmount + $fineAmount,
                'recorded_by' => $request->user()->name,
                'receipt_number' => $receiptNumber,
            ],
        ]);

        $transaction = $this->recordLibraryFinance(
            'income',
            'فیس ماهانه کتابخانه',
            $feeAmount + $fineAmount,
            $member->full_name,
            'پرداخت ماهانه عضو کتابخانه: '.$member->member_code.' - '.$billingLabel,
            $request,
            $paidAt->toDateString(),
            'cash',
            $receiptNumber,
            $billingMonth
        );

        session([
            'library_monthly_receipt.transaction_id' => $transaction->id,
            'library_monthly_receipt.receipt_number' => $transaction->receipt_number,
        ]);

        return redirect()
            ->route('library.members.monthly-payment.receipt', ['member' => $member, 'billing_month' => $billingMonth->format('Y-m')])
            ->with('status', 'پرداخت '.$billingLabel.' ثبت شد و رسید آماده چاپ است.');
    }

    public function monthlyPaymentReceipt(Request $request, LibraryMember $member): View
    {
        $receipt = session('library_monthly_receipt');
        $billingMonth = $this->parseLibraryBillingMonth($request->query('billing_month'))
            ?? $this->parseLibraryBillingMonth($receipt['billing_month'] ?? null)
            ?? $this->openLibraryBillingMonth($member);
        $monthlyPayment = $this->libraryMonthlyPaymentTransaction($member, $billingMonth);

        if (! $monthlyPayment) {
            abort(404, 'برای این ماه پرداخت ثبت‌شده وجود ندارد. اول فیس ماهانه را ثبت کنید، بعد رسید چاپ می‌شود.');
        }

        if (($receipt['member_id'] ?? null) !== $member->id || ($receipt['billing_month'] ?? null) !== $billingMonth->format('Y-m')) {
            $feeAmount = min((int) $member->membership_fee, (int) $monthlyPayment->amount);
            $receipt = [
                'member_id' => $member->id,
                'billing_month' => $billingMonth->format('Y-m'),
                'billing_label' => Locale::month($billingMonth),
                'paid_at' => $monthlyPayment->transaction_date?->toDateString() ?? today()->toDateString(),
                'fee_amount' => $feeAmount,
                'fine_amount' => max(0, (int) $monthlyPayment->amount - $feeAmount),
                'total_amount' => (int) $monthlyPayment->amount,
                'recorded_by' => $monthlyPayment->recordedBy?->name ?: auth()->user()?->name,
                'receipt_number' => $monthlyPayment->receipt_number,
            ];
        }

        return view('library.receipts.monthly-payment', [
            'member' => $member,
            'receipt' => $receipt,
            'receiptNumber' => $monthlyPayment->receipt_number ?: $this->libraryMonthlyReceiptNumber($member, $billingMonth),
            'billingMonth' => $billingMonth,
            'billingLabel' => $receipt['billing_label'] ?? Locale::month($billingMonth),
        ]);
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $validated = $this->validateBook($request);
        $this->ensureBookIsNotDuplicate($validated);

        $book = Book::create(array_merge($validated, [
            'registered_by' => $request->user()->id,
            'available_copies' => $validated['total_copies'],
            'barcode' => $validated['barcode'] ?: $this->nextCode('BOOK'),
        ]));
        $this->syncBookCopies($book);

        return back()->with('status', 'کتاب جدید ثبت شد.');
    }

    public function editBook(Book $book): View
    {
        return view('library.books.form', [
            'book' => $book,
        ]);
    }

    public function copyLabels(Book $book): View
    {
        $book->load(['copies' => fn ($query) => $query->orderBy('copy_code')]);

        return view('library.books.copy-labels', [
            'book' => $book,
            'labels' => $book->copies->map(fn (BookCopy $copy) => [
                'copy' => $copy,
                'barcodeSvg' => $this->code39Svg($copy->barcode ?: $copy->copy_code),
            ]),
        ]);
    }

    public function updateBook(Request $request, Book $book): RedirectResponse
    {
        $validated = $this->validateBook($request, $book);
        $this->ensureBookIsNotDuplicate($validated, $book);

        $activeLoanCount = $book->loans()->whereIn('status', ['borrowed', 'late'])->count();
        if ((int) $validated['total_copies'] < $activeLoanCount) {
            throw ValidationException::withMessages([
                'total_copies' => 'تعداد نسخه‌ها نمی‌تواند کمتر از امانت‌های فعال همین کتاب باشد.',
            ]);
        }

        $copyDifference = (int) $validated['total_copies'] - (int) $book->total_copies;
        $validated['available_copies'] = max(0, (int) $book->available_copies + $copyDifference);

        $book->update($validated);
        $this->syncBookCopies($book->refresh());

        return redirect()->route('library.index')->with('status', 'کتاب ویرایش شد.');
    }

    public function updateBookCopy(Request $request, BookCopy $copy): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['available', 'on_loan', 'damaged', 'lost', 'archived'])],
            'condition' => ['nullable', 'string', 'max:120'],
            'shelf_code' => ['nullable', 'string', 'max:80'],
            'purchase_price' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:700'],
        ]);

        $validated['purchase_price'] = $validated['purchase_price'] ?? 0;

        $hasActiveLoan = $copy->loans()
            ->whereIn('status', ['borrowed', 'late'])
            ->exists();

        if ($hasActiveLoan && $validated['status'] !== 'on_loan') {
            return back()->withErrors(['status' => 'این نسخه امانت فعال دارد؛ اول برگشت آن را ثبت کنید.']);
        }

        if (! $hasActiveLoan && $validated['status'] === 'on_loan') {
            return back()->withErrors(['status' => 'وضعیت امانت را از فرم ثبت امانت کتاب ثبت کنید.']);
        }

        $copy->update($validated);
        $this->syncBookAvailability($copy->book);

        return back()->with('status', 'وضعیت نسخه کتاب به‌روزرسانی شد: '.$copy->copy_code);
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $validated = $this->validateLoan($request);

        DB::transaction(function () use ($request, $validated): void {
            $copy = BookCopy::query()
                ->where(function ($query) use ($validated) {
                    $query
                        ->where('copy_code', $validated['copy_code'])
                        ->orWhere('barcode', $validated['copy_code']);
                })
                ->where('status', 'available')
                ->with('book')
                ->lockForUpdate()
                ->first();

            if (! $copy) {
                throw ValidationException::withMessages([
                    'copy_code' => 'این نسخه کتاب فعلاً برای امانت در دسترس نیست.',
                ]);
            }

            $book = Book::lockForUpdate()->findOrFail($copy->book_id);

            if ((int) $book->id !== (int) $validated['book_id']) {
                throw ValidationException::withMessages([
                    'copy_code' => 'کد نسخه با کتاب انتخاب‌شده مطابقت ندارد.',
                ]);
            }

            if ($book->available_copies < 1) {
                throw ValidationException::withMessages([
                    'book_id' => 'این کتاب فعلاً نسخه قابل امانت ندارد.',
                ]);
            }

            $activeDuplicateLoan = BookLoan::query()
                ->where('book_copy_id', $copy->id)
                ->whereIn('status', ['borrowed', 'late'])
                ->exists();

            if ($activeDuplicateLoan) {
                throw ValidationException::withMessages([
                    'copy_code' => 'این نسخه قبلاً امانت داده شده و هنوز برگشت نشده است.',
                ]);
            }

            $book->decrement('available_copies');
            $copy->update(['status' => 'on_loan']);

            BookLoan::create(array_merge($validated, [
                'recorded_by' => $request->user()->id,
                'book_copy_id' => $copy->id,
                'active_book_copy_id' => $copy->id,
                'book_id' => $book->id,
                'loan_code' => ($validated['loan_code'] ?? null) ?: $this->nextCode('LOAN'),
                'status' => 'borrowed',
            ]));
        });
        Audit::record('library_loan_created', null, [], ['copy_code' => $validated['copy_code'], 'library_member_id' => $validated['library_member_id'], 'book_id' => $validated['book_id']], $request);

        return back()->with('status', 'امانت کتاب ثبت شد.');
    }

    public function editLoan(BookLoan $loan): View
    {
        $this->syncOverdueLoans();

        return view('library.loans.form', [
            'loan' => $loan->load(['member', 'book', 'copy']),
            'activeMembers' => LibraryMember::where('status', 'active')->orderBy('full_name')->get(),
            'books' => Book::orderBy('title')->get(),
        ]);
    }

    public function updateLoan(Request $request, BookLoan $loan): RedirectResponse
    {
        $validated = $this->validateLoan($request, $loan);
        unset($validated['book_id'], $validated['library_member_id']);

        if (in_array($validated['status'] ?? $loan->status, ['returned', 'lost'], true)) {
            throw ValidationException::withMessages([
                'status' => 'برای برگشت یا ثبت گم‌شدن کتاب، از فرم برگشت کتاب استفاده کنید.',
            ]);
        }

        if (in_array($validated['status'] ?? $loan->status, ['borrowed', 'late'], true) && $loan->book_copy_id) {
            $validated['active_book_copy_id'] = $loan->book_copy_id;
        }

        $oldValues = $loan->only(['loan_code', 'borrowed_at', 'due_at', 'status', 'fine_amount']);
        $loan->update($validated);
        Audit::record('library_loan_updated', $loan, $oldValues, $loan->fresh()->only(['loan_code', 'borrowed_at', 'due_at', 'status', 'fine_amount']), $request);

        return redirect()->route('library.members.show', $loan->member)->with('status', 'امانت کتاب ویرایش شد.');
    }

    public function returnLoan(Request $request, BookLoan $loan): RedirectResponse
    {
        $validated = $request->validate([
            'returned_at' => ['required', 'date'],
            'fine_amount' => ['nullable', 'integer', 'min:0'],
            'condition_in' => ['nullable', 'string', 'max:120'],
            'return_status' => ['nullable', Rule::in(['available', 'damaged', 'lost'])],
        ]);

        if ($loan->borrowed_at && Carbon::parse($validated['returned_at'])->lt($loan->borrowed_at)) {
            throw ValidationException::withMessages([
                'returned_at' => 'تاریخ برگشت نمی‌تواند قبل از تاریخ امانت باشد.',
            ]);
        }

        $this->markLoanReturned($loan, $validated);
        Audit::record('library_loan_returned', $loan, [], ['returned_at' => $validated['returned_at'], 'fine_amount' => $validated['fine_amount'] ?? 0], $request);

        return back()->with('status', 'برگشت کتاب ثبت شد.');
    }

    public function returnLoanByCopy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'copy_code' => ['required', 'string', 'max:100'],
            'returned_at' => ['required', 'date'],
            'fine_amount' => ['nullable', 'integer', 'min:0'],
            'condition_in' => ['nullable', 'string', 'max:120'],
            'return_status' => ['nullable', Rule::in(['available', 'damaged', 'lost'])],
        ]);

        $copy = BookCopy::query()
            ->where(function ($query) use ($validated) {
                $query
                    ->where('copy_code', $validated['copy_code'])
                    ->orWhere('barcode', $validated['copy_code']);
            })
            ->first();

        if (! $copy) {
            return back()->withErrors(['copy_code' => 'برای این بارکد/کد نسخه، کتابی پیدا نشد.'])->withInput();
        }

        $loan = BookLoan::query()
            ->where('book_copy_id', $copy->id)
            ->whereIn('status', ['borrowed', 'late'])
            ->latest('borrowed_at')
            ->first();

        if (! $loan) {
            return back()->withErrors(['copy_code' => 'برای این نسخه امانت فعال پیدا نشد.'])->withInput();
        }

        if ($loan->borrowed_at && Carbon::parse($validated['returned_at'])->lt($loan->borrowed_at)) {
            return back()->withErrors(['returned_at' => 'تاریخ برگشت نمی‌تواند قبل از تاریخ امانت باشد.'])->withInput();
        }

        $this->markLoanReturned($loan, $validated);
        Audit::record('library_loan_returned_by_copy', $loan, [], ['copy_code' => $copy->copy_code, 'returned_at' => $validated['returned_at'], 'fine_amount' => $validated['fine_amount'] ?? 0], $request);

        return back()->with('status', 'برگشت کتاب با بارکد ثبت شد: '.$copy->copy_code);
    }

    private function validateInventoryFilters(Request $request): array
    {
        return $request->validate([
            'status' => ['nullable', Rule::in(['available', 'on_loan', 'damaged', 'lost', 'archived'])],
            'shelf' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:120'],
            'q' => ['nullable', 'string', 'max:120'],
        ]);
    }

    private function inventoryCopiesQuery(array $filters)
    {
        return BookCopy::query()
            ->with('book')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['shelf'] ?? null, fn ($query, $shelf) => $query->where('shelf_code', 'like', "%{$shelf}%"))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->whereHas('book', fn ($bookQuery) => $bookQuery->where('category', 'like', "%{$category}%")))
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('copy_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhereHas('book', function ($bookQuery) use ($search) {
                            $bookQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('author', 'like', "%{$search}%")
                                ->orWhere('isbn', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function validateMemberFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'suspended', 'left'])],
        ]);
    }

    private function libraryMembersQuery(array $filters)
    {
        return LibraryMember::query()
            ->with(['membershipCards' => fn ($query) => $query->where('scope', 'library')->latest('expires_at')])
            ->withCount(['loans as active_loans_count' => fn ($query) => $query->whereIn('status', ['borrowed', 'late'])])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tazkira_number', 'like', "%{$search}%")
                        ->orWhere('member_code', 'like', "%{$search}%")
                        ->orWhere('education_place', 'like', "%{$search}%");
                });
            });
    }

    private function validateMember(Request $request, ?LibraryMember $member = null): array
    {
        $validated = $request->validate([
            'member_code' => ['nullable', 'string', 'max:60', Rule::unique('library_members', 'member_code')->ignore($member)],
            'full_name' => ['required', 'string', 'max:120'],
            'father_name' => ['required', 'string', 'max:120'],
            'phone' => [...SecurityRules::phone(), Rule::unique('library_members', 'phone')->ignore($member)],
            'email' => ['nullable', 'email', 'max:120', Rule::unique('library_members', 'email')->ignore($member)],
            'tazkira_number' => ['nullable', 'string', 'max:80', Rule::unique('library_members', 'tazkira_number')->ignore($member)],
            'education_place' => ['nullable', 'string', 'max:160'],
            'department_or_grade' => ['nullable', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:220'],
            'membership_fee' => ['nullable', 'integer', 'min:0'],
            'monthly_fee_daily_fine' => ['nullable', 'integer', 'min:0'],
            'monthly_fee_fine_amount' => ['nullable', 'integer', 'min:0'],
            'payment_status' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'joined_at' => ['nullable', 'date'],
            'left_at' => [Rule::requiredIf(fn () => $request->input('status') === 'left'), 'nullable', 'date', 'after_or_equal:joined_at'],
            'membership_expires_at' => ['nullable', 'date'],
            'next_payment_due_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['active', 'suspended', 'left'])],
            'notes' => ['nullable', 'string', 'max:700'],
            'profile_photo' => SecurityRules::profileImage(),
            'remove_profile_photo' => ['nullable', 'boolean'],
            'issue_card' => ['nullable', 'boolean'],
        ]);

        if (($validated['status'] ?? $member?->status) !== 'left') {
            $validated['left_at'] = null;
        }

        return $validated;
    }

    private function validateLibraryFinanceFilters(Request $request): array
    {
        return $request->validate([
            'finance_q' => ['nullable', 'string', 'max:120'],
            'finance_type' => ['nullable', Rule::in(['income', 'expense'])],
            'finance_category' => ['nullable', 'integer', 'exists:finance_categories,id'],
            'finance_payment_method' => ['nullable', Rule::in(array_keys($this->libraryPaymentMethods()))],
            'finance_date_from' => ['nullable', 'date'],
            'finance_date_to' => ['nullable', 'date', 'after_or_equal:finance_date_from'],
        ]);
    }

    private function applyLibraryFinanceFilters($query, array $filters): void
    {
        $query
            ->when($filters['finance_type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['finance_category'] ?? null, fn ($query, $category) => $query->where('finance_category_id', $category))
            ->when($filters['finance_payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($filters['finance_date_from'] ?? null, fn ($query, $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['finance_date_to'] ?? null, fn ($query, $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['finance_q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('payer_name', 'like', "%{$search}%")
                        ->orWhere('payee_name', 'like', "%{$search}%")
                        ->orWhere('source_or_payee', 'like', "%{$search}%")
                        ->orWhere('transaction_number', 'like', "%{$search}%")
                        ->orWhere('receipt_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                });
            });
    }

    private function libraryFinancePeriodReports(): array
    {
        $periods = [
            'daily' => [
                'label' => 'روزانه',
                'caption' => 'امروز',
                'start' => today()->toDateString(),
                'end' => today()->toDateString(),
            ],
            'weekly' => [
                'label' => 'هفته‌وار',
                'caption' => 'هفته جاری',
                'start' => now()->startOfWeek()->toDateString(),
                'end' => now()->endOfWeek()->toDateString(),
            ],
            'monthly' => [
                'label' => 'ماهانه',
                'caption' => 'ماه جاری',
                'start' => now()->startOfMonth()->toDateString(),
                'end' => now()->endOfMonth()->toDateString(),
            ],
            'yearly' => [
                'label' => 'سالانه',
                'caption' => 'سال جاری',
                'start' => now()->startOfYear()->toDateString(),
                'end' => now()->endOfYear()->toDateString(),
            ],
        ];

        foreach ($periods as $key => $period) {
            $income = (int) $this->libraryFinanceTransactions()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$period['start'], $period['end']])
                ->sum('amount');
            $expense = (int) $this->libraryFinanceTransactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$period['start'], $period['end']])
                ->sum('amount');

            $periods[$key]['income'] = $income;
            $periods[$key]['expense'] = $expense;
            $periods[$key]['balance'] = $income - $expense;
        }

        return $periods;
    }

    private function storeProfilePhoto(Request $request): ?string
    {
        return $request->file('profile_photo')?->store('profile-photos/library-members', 'public');
    }

    private function syncProfilePhoto(Request $request, LibraryMember $member): ?string
    {
        if ($request->boolean('remove_profile_photo')) {
            if ($member->profile_photo_path) {
                Storage::disk('public')->delete($member->profile_photo_path);
            }

            return null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($member->profile_photo_path) {
                Storage::disk('public')->delete($member->profile_photo_path);
            }

            return $this->storeProfilePhoto($request);
        }

        return $member->profile_photo_path;
    }

    private function validateBook(Request $request, ?Book $book = null): array
    {
        $validated = $request->validate([
            'isbn' => ['nullable', 'string', 'max:40', Rule::unique('books', 'isbn')->ignore($book)],
            'title' => ['required', 'string', 'max:180'],
            'author' => ['nullable', 'string', 'max:160'],
            'publisher' => ['nullable', 'string', 'max:160'],
            'language' => ['nullable', 'string', 'max:80'],
            'edition' => ['nullable', 'string', 'max:80'],
            'published_year' => ['nullable', 'integer', 'min:1000', 'max:'.now()->year],
            'pages' => ['nullable', 'integer', 'min:1', 'max:20000'],
            'category' => ['nullable', 'string', 'max:120'],
            'shelf_code' => ['nullable', 'string', 'max:80'],
            'barcode' => ['nullable', 'string', 'max:80', Rule::unique('books', 'barcode')->ignore($book)],
            'total_copies' => ['required', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(['available', 'damaged', 'lost', 'archived'])],
            'notes' => ['nullable', 'string', 'max:700'],
        ]);

        $validated['isbn'] = filled($validated['isbn'] ?? null) ? trim((string) $validated['isbn']) : null;
        $validated['barcode'] = filled($validated['barcode'] ?? null) ? trim((string) $validated['barcode']) : null;
        if (Schema::hasColumn('books', 'identity_key')) {
            $validated['identity_key'] = $this->bookIdentityKey($validated);
        }

        return $validated;
    }

    private function ensureBookIsNotDuplicate(array $validated, ?Book $book = null): void
    {
        $query = Book::query()->when($book, fn ($query) => $query->whereKeyNot($book->id));

        if (Schema::hasColumn('books', 'identity_key')) {
            $query->where('identity_key', $this->bookIdentityKey($validated));
        } else {
            $query->where('title', trim((string) ($validated['title'] ?? '')));

            foreach (['author', 'publisher', 'edition'] as $field) {
                $value = trim((string) ($validated[$field] ?? ''));
                $query->where(function ($query) use ($field, $value): void {
                    $value === ''
                        ? $query->whereNull($field)->orWhere($field, '')
                        : $query->where($field, $value);
                });
            }
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'title' => 'کتابی با همین عنوان، نویسنده، ناشر و چاپ قبلاً ثبت شده است. اگر نسخه جدید است، تعداد نسخه‌ها را در همان کتاب افزایش دهید.',
            ]);
        }
    }

    private function bookIdentityKey(array $book): string
    {
        $parts = [
            $book['title'] ?? '',
            $book['author'] ?? '',
            $book['publisher'] ?? '',
            $book['edition'] ?? '',
        ];

        $normalized = array_map(function ($value): string {
            $value = mb_strtolower(trim((string) $value));

            return preg_replace('/\s+/u', ' ', $value) ?: '';
        }, $parts);

        return sha1(implode('|', $normalized));
    }

    private function validateLoan(Request $request, ?BookLoan $loan = null): array
    {
        return $request->validate([
            'loan_code' => ['nullable', 'string', 'max:60', Rule::unique('book_loans', 'loan_code')->ignore($loan)],
            'library_member_id' => [$loan ? 'nullable' : 'required', 'exists:library_members,id'],
            'book_id' => [$loan ? 'nullable' : 'required', 'exists:books,id'],
            'copy_code' => [$loan ? 'nullable' : 'required', 'string', 'max:100'],
            'borrowed_at' => ['required', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:borrowed_at'],
            'condition_out' => ['nullable', 'string', 'max:120'],
            'fine_amount' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::in(['borrowed', 'returned', 'lost', 'late'])],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
    }

    private function issueLibraryCard(LibraryMember $member, Request $request): MembershipCard
    {
        $issuedAt = now();
        $paymentStatus = $request->input('payment_status', 'unpaid');

        $card = $member->membershipCards()->create([
            'scope' => 'library',
            'card_number' => $this->nextCode('LIB-CARD'),
            'holder_name' => $member->full_name,
            'father_name' => $member->father_name,
            'issued_at' => $issuedAt,
            'expires_at' => $issuedAt->copy()->addMonths(6),
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'paid' ? $issuedAt : null,
            'created_by' => $request->user()->id,
        ]);

        $member->update([
            'membership_expires_at' => $card->expires_at,
            'next_payment_due_at' => $paymentStatus === 'paid'
                ? ($member->next_payment_due_at && $member->next_payment_due_at->isFuture() ? $member->next_payment_due_at : $issuedAt->copy()->addMonth())
                : $member->next_payment_due_at,
            'last_paid_at' => $paymentStatus === 'paid' ? $issuedAt : $member->last_paid_at,
            'monthly_fee_fine_amount' => $paymentStatus === 'paid' ? 0 : $member->monthly_fee_fine_amount,
        ]);
        Audit::record('library_card_issued', $card, [], $card->only(['card_number', 'holder_name', 'payment_status', 'expires_at']), $request);

        return $card;
    }

    private function syncBookCopies(Book $book): void
    {
        $existingCount = $book->copies()->count();
        $targetCount = (int) $book->total_copies;

        if ($existingCount < $targetCount) {
            for ($index = $existingCount + 1; $index <= $targetCount; $index++) {
                $code = $this->nextCopyCode($book, $index);

                $book->copies()->create([
                    'copy_code' => $code,
                    'barcode' => $code,
                    'shelf_code' => $book->shelf_code,
                    'status' => $book->status === 'available' ? 'available' : $book->status,
                ]);
            }
        }

        if ($existingCount > $targetCount) {
            $book->copies()
                ->where('status', 'available')
                ->latest()
                ->limit($existingCount - $targetCount)
                ->update(['status' => 'archived']);
        }

        $book->updateQuietly([
            'available_copies' => $book->copies()->where('status', 'available')->count(),
        ]);
    }

    private function syncBookAvailability(Book $book): void
    {
        $book->updateQuietly([
            'available_copies' => $book->copies()->where('status', 'available')->count(),
        ]);
    }

    private function syncOverdueLoans(?LibraryMember $member = null): void
    {
        BookLoan::query()
            ->when($member, fn ($query) => $query->where('library_member_id', $member->id))
            ->where('status', 'borrowed')
            ->whereNotNull('due_at')
            ->whereDate('due_at', '<', today())
            ->update(['status' => 'late']);
    }

    private function markLoanReturned(BookLoan $loan, array $validated): void
    {
        DB::transaction(function () use ($loan, $validated): void {
            $loan->refresh();

            if (! in_array($loan->status, ['borrowed', 'late'], true)) {
                throw ValidationException::withMessages([
                    'copy_code' => 'برگشت این امانت قبلاً ثبت شده یا وضعیت آن قابل برگشت نیست.',
                ]);
            }

            $returnStatus = $validated['return_status'] ?? 'available';
            $loanStatus = $returnStatus === 'lost' ? 'lost' : 'returned';

            $loan->update([
                'returned_at' => $validated['returned_at'],
                'condition_in' => $validated['condition_in'] ?? null,
                'fine_amount' => $validated['fine_amount'] ?? 0,
                'status' => $loanStatus,
                'active_book_copy_id' => null,
            ]);

            if ($loan->copy) {
                $loan->copy->update([
                    'status' => $returnStatus,
                    'condition' => $validated['condition_in'] ?? $loan->copy->condition,
                ]);

                $this->syncBookAvailability($loan->copy->book);

                return;
            }

            if ($returnStatus === 'available') {
                $loan->book()->increment('available_copies');
            }
        });
    }

    private function nextCopyCode(Book $book, int $index): string
    {
        $base = $book->barcode ?: 'BOOK-'.$book->id;

        return $base.'-C'.str_pad((string) $index, 3, '0', STR_PAD_LEFT);
    }

    private function code39Svg(string $value): string
    {
        $patterns = [
            '0' => 'nnnwwnwnn', '1' => 'wnnwnnnnw', '2' => 'nnwwnnnnw', '3' => 'wnwwnnnnn',
            '4' => 'nnnwwnnnw', '5' => 'wnnwwnnnn', '6' => 'nnwwwnnnn', '7' => 'nnnwnnwnw',
            '8' => 'wnnwnnwnn', '9' => 'nnwwnnwnn', 'A' => 'wnnnnwnnw', 'B' => 'nnwnnwnnw',
            'C' => 'wnwnnwnnn', 'D' => 'nnnnwwnnw', 'E' => 'wnnnwwnnn', 'F' => 'nnwnwwnnn',
            'G' => 'nnnnnwwnw', 'H' => 'wnnnnwwnn', 'I' => 'nnwnnwwnn', 'J' => 'nnnnwwwnn',
            'K' => 'wnnnnnnww', 'L' => 'nnwnnnnww', 'M' => 'wnwnnnnwn', 'N' => 'nnnnwnnww',
            'O' => 'wnnnwnnwn', 'P' => 'nnwnwnnwn', 'Q' => 'nnnnnnwww', 'R' => 'wnnnnnwwn',
            'S' => 'nnwnnnwwn', 'T' => 'nnnnwnwwn', 'U' => 'wwnnnnnnw', 'V' => 'nwwnnnnnw',
            'W' => 'wwwnnnnnn', 'X' => 'nwnnwnnnw', 'Y' => 'wwnnwnnnn', 'Z' => 'nwwnwnnnn',
            '-' => 'nwnnnnwnw', '.' => 'wwnnnnwnn', ' ' => 'nwwnnnwnn', '$' => 'nwnwnwnnn',
            '/' => 'nwnwnnnwn', '+' => 'nwnnnwnwn', '%' => 'nnnwnwnwn', '*' => 'nwnnwnwnn',
        ];

        $encoded = '*'.preg_replace('/[^0-9A-Z\-. $\/+%]/', '-', strtoupper($value)).'*';
        $narrow = 2;
        $wide = 5;
        $height = 68;
        $x = 10;
        $bars = '';

        foreach (str_split($encoded) as $char) {
            foreach (str_split($patterns[$char] ?? $patterns['-']) as $index => $widthCode) {
                $width = $widthCode === 'w' ? $wide : $narrow;

                if ($index % 2 === 0) {
                    $bars .= '<rect x="'.$x.'" y="8" width="'.$width.'" height="'.$height.'" fill="#111827"/>';
                }

                $x += $width;
            }

            $x += $narrow;
        }

        $svgWidth = $x + 10;

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$svgWidth.' 86" preserveAspectRatio="none">'.$bars.'</svg>';
    }

    private function syncMonthlyFine(LibraryMember $member): void
    {
        $calculatedFine = $member->calculatedMonthlyFine();

        if ($calculatedFine > (int) $member->monthly_fee_fine_amount) {
            $member->forceFill(['monthly_fee_fine_amount' => $calculatedFine])->save();
        }
    }

    private function parseLibraryBillingMonth(mixed $value): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}$/', $value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfMonth();
        } catch (\Throwable) {
            return null;
        }
    }

    private function openLibraryBillingMonth(LibraryMember $member): Carbon
    {
        $source = $member->next_payment_due_at
            ?: ($member->last_paid_at?->copy()->addMonth())
            ?: $member->joined_at
            ?: today();

        return Carbon::parse($source)->startOfMonth();
    }

    private function libraryBillingDueDate(LibraryMember $member, Carbon $billingMonth): Carbon
    {
        if ($member->next_payment_due_at && $member->next_payment_due_at->isSameMonth($billingMonth)) {
            return $member->next_payment_due_at->copy();
        }

        $day = (int) ($member->joined_at?->day ?? 1);
        $dueDate = $billingMonth->copy();

        return $dueDate->day(min($day, $dueDate->daysInMonth));
    }

    private function libraryMonthlyBillRows(LibraryMember $member, Carbon $openBillingMonth): array
    {
        $start = Carbon::parse($member->joined_at ?: $member->last_paid_at ?: today())->startOfMonth();
        $end = today()->startOfMonth()->max($openBillingMonth->copy());
        $oldestVisible = $end->copy()->subMonths(11);

        if ($start->lt($oldestVisible)) {
            $start = $oldestVisible;
        }

        if ($start->gt($end)) {
            $start = $end->copy();
        }

        $payments = $this->libraryMonthlyPaymentTransactions($member);
        $rows = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $monthKey = $cursor->format('Y-m');
            $transaction = $payments[$monthKey] ?? null;
            $isOpen = $monthKey === $openBillingMonth->format('Y-m');
            $isOverdue = ! $transaction && (
                $cursor->lt(today()->startOfMonth())
                || ($isOpen && $member->next_payment_due_at && $member->next_payment_due_at->lte(today()))
            );

            $rows[] = [
                'month_key' => $monthKey,
                'month_label' => Locale::month($cursor),
                'paid' => (bool) $transaction,
                'is_open' => $isOpen,
                'status_label' => $transaction ? 'پرداخت شده' : ($isOverdue ? 'ناوقت' : 'در انتظار'),
                'status_tone' => $transaction ? 'success' : ($isOverdue ? 'danger' : 'warning'),
                'amount' => $transaction ? (int) $transaction->amount : ($isOpen ? $member->monthlyFeeBalance() : (int) $member->membership_fee),
                'paid_at' => $transaction?->transaction_date,
                'receipt_number' => $transaction?->receipt_number ?: $this->libraryMonthlyReceiptNumber($member, $cursor),
                'receipt_url' => $transaction ? route('library.members.monthly-payment.receipt', ['member' => $member, 'billing_month' => $monthKey]) : null,
            ];

            $cursor->addMonth();
        }

        return array_reverse($rows);
    }

    private function libraryMonthlyPaymentTransactions(LibraryMember $member): array
    {
        $transactions = $this->libraryFinanceTransactions()
            ->with(['category', 'recordedBy'])
            ->where('type', 'income')
            ->where('status', 'paid')
            ->where(function ($query) use ($member): void {
                $query
                    ->where('receipt_number', 'like', 'LIB-MONTHLY-'.$member->id.'-%')
                    ->orWhere('description', 'like', '%'.$member->member_code.'%');
            })
            ->latest('transaction_date')
            ->get();

        $payments = [];

        foreach ($transactions as $transaction) {
            $key = null;

            if (is_string($transaction->receipt_number) && preg_match('/^LIB-MONTHLY-'.$member->id.'-(\d{4}-\d{2})$/', $transaction->receipt_number, $matches)) {
                $key = $matches[1];
            } elseif ($transaction->payment_year && $transaction->payment_month) {
                $key = sprintf('%04d-%02d', (int) $transaction->payment_year, (int) $transaction->payment_month);
            } elseif ($transaction->transaction_date) {
                $key = $transaction->transaction_date->format('Y-m');
            }

            if ($key && ! isset($payments[$key])) {
                $payments[$key] = $transaction;
            }
        }

        return $payments;
    }

    private function recordLibraryFinance(
        string $type,
        string $categoryLabel,
        int $amount,
        string $person,
        string $description,
        Request $request,
        ?string $date = null,
        string $paymentMethod = 'cash',
        ?string $receiptNumber = null,
        Carbon|string|null $billingMonth = null
    ): FinanceTransaction {
        $transactionDate = Carbon::parse($date ?: today());
        $paymentPeriod = $billingMonth
            ? ($billingMonth instanceof Carbon ? $billingMonth : Carbon::parse($billingMonth))->copy()->startOfMonth()
            : $transactionDate;

        $category = FinanceCategory::firstOrCreate(
            ['name' => 'کتابخانه - '.$categoryLabel, 'type' => $type],
            ['color' => $type === 'income' ? '#0ea5a4' : '#ef4444', 'is_active' => true]
        );

        $transaction = FinanceTransaction::create([
            'transaction_number' => $this->nextLibraryFinanceNumber($type),
            'type' => $type,
            'finance_category_id' => $category->id,
            'expected_amount' => $amount,
            'amount' => $amount,
            'transaction_date' => $transactionDate->toDateString(),
            'source_or_payee' => $person,
            'payer_name' => $type === 'income' ? $person : null,
            'payee_name' => $type === 'expense' ? $person : null,
            'receipt_number' => $receiptNumber ?: $this->nextLibraryFinanceNumber($type),
            'payment_method' => $paymentMethod,
            'status' => 'paid',
            'description' => $description,
            'notes' => 'ثبت مالی کتابخانه',
            'attachment_required' => false,
            'payment_month' => $paymentPeriod->month,
            'payment_year' => $paymentPeriod->year,
            'recorded_by' => $request->user()->id,
        ]);

        FinanceAuditLog::create([
            'finance_transaction_id' => $transaction->id,
            'action' => 'created',
            'new_values' => $transaction->fresh()->toArray(),
            'performed_by' => $request->user()->id,
        ]);

        return $transaction;
    }

    private function libraryFinanceTransactions()
    {
        return FinanceTransaction::query()
            ->whereHas('category', fn ($query) => $query->where('name', 'like', 'کتابخانه -%'));
    }

    private function activeLibraryCard(LibraryMember $member): ?MembershipCard
    {
        return $member->membershipCards()
            ->where('scope', 'library')
            ->whereDate('expires_at', '>=', today())
            ->latest('expires_at')
            ->first();
    }

    private function libraryMonthlyPaymentTransaction(LibraryMember $member, Carbon|string $date): ?FinanceTransaction
    {
        $paidAt = $date instanceof Carbon ? $date : Carbon::parse($date);
        $receiptNumber = $this->libraryMonthlyReceiptNumber($member, $paidAt);

        return $this->libraryFinanceTransactions()
            ->where('type', 'income')
            ->where('status', 'paid')
            ->where(function ($query) use ($member, $paidAt, $receiptNumber): void {
                $query
                    ->where('receipt_number', $receiptNumber)
                    ->orWhere(function ($query) use ($member, $paidAt): void {
                        $query
                            ->where('description', 'like', '%'.$member->member_code.'%')
                            ->where(function ($query) use ($paidAt): void {
                                $query
                                    ->where(function ($query) use ($paidAt): void {
                                        $query->where('payment_month', $paidAt->month)
                                            ->where('payment_year', $paidAt->year);
                                    })
                                    ->orWhereBetween('transaction_date', [
                                        $paidAt->copy()->startOfMonth()->toDateString(),
                                        $paidAt->copy()->endOfMonth()->toDateString(),
                                    ]);
                            });
                    });
            })
            ->latest('transaction_date')
            ->first();
    }

    private function libraryMonthlyReceiptNumber(LibraryMember $member, Carbon|string $date): string
    {
        $paidAt = $date instanceof Carbon ? $date : Carbon::parse($date);

        return 'LIB-MONTHLY-'.$member->id.'-'.$paidAt->format('Y-m');
    }

    private function libraryFinanceCategoryOptions()
    {
        return FinanceCategory::query()
            ->where('is_active', true)
            ->where('name', 'like', 'کتابخانه -%')
            ->orderBy('type')
            ->orderBy('name')
            ->get();
    }

    private function isLibraryFinanceTransaction(FinanceTransaction $transaction): bool
    {
        $transaction->loadMissing('category');

        return $transaction->category
            && str_starts_with($transaction->category->name, 'کتابخانه -');
    }

    private function hasLibraryMonthlyPayment(LibraryMember $member, Carbon|string $date): bool
    {
        return (bool) $this->libraryMonthlyPaymentTransaction($member, $date);
    }

    private function libraryFinanceCategoryLabels(): array
    {
        return [
            'monthly_fee' => ['type' => 'income', 'label' => 'فیس ماهانه کتابخانه'],
            'donation' => ['type' => 'income', 'label' => 'کمک برای کتابخانه'],
            'other_income' => ['type' => 'income', 'label' => 'درآمد متفرقه کتابخانه'],
            'book_purchase' => ['type' => 'expense', 'label' => 'خرید کتاب و وسایل'],
            'repair' => ['type' => 'expense', 'label' => 'ترمیم و نگهداری کتابخانه'],
            'staff_payment' => ['type' => 'expense', 'label' => 'معاش یا حق‌الزحمه کتابخانه'],
            'other_expense' => ['type' => 'expense', 'label' => 'مصرف متفرقه کتابخانه'],
        ];
    }

    private function libraryPaymentMethods(): array
    {
        return [
            'cash' => 'نقد',
            'bank' => 'بانک',
            'hawala' => 'حواله',
            'card' => 'کارت',
            'other' => 'سایر',
        ];
    }

    private function financeStatusLabels(): array
    {
        return [
            'paid' => 'پرداخت شده',
            'partial' => 'نیمه پرداخت',
            'pending' => 'بدهکار',
        ];
    }

    private function nextLibraryFinanceNumber(string $type): string
    {
        $prefix = $type === 'expense' ? 'LIB-EXP' : 'LIB-INC';
        $count = FinanceTransaction::withTrashed()->whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function normalizeMemberPayment(array &$validated, LibraryMember $member): void
    {
        $validated['monthly_fee_daily_fine'] = $validated['monthly_fee_daily_fine'] ?? $member->monthly_fee_daily_fine ?? 20;
        $validated['monthly_fee_fine_amount'] = $validated['monthly_fee_fine_amount'] ?? $member->monthly_fee_fine_amount ?? 0;

        if (($validated['payment_status'] ?? $member->payment_status) === 'paid') {
            $paidAt = now();
            $validated['last_paid_at'] = $paidAt->toDateString();
            $validated['next_payment_due_at'] = $paidAt->copy()->addMonth()->toDateString();
            $validated['monthly_fee_fine_amount'] = 0;
        }
    }

    private function monthlyFeeReminderText(LibraryMember $member): string
    {
        $dueDate = $member->next_payment_due_at?->format('Y-m-d') ?: 'N/A';
        $fine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
        $balance = (int) $member->membership_fee + $fine;

        if ($member->next_payment_due_at && $member->next_payment_due_at->isFuture()) {
            return "سلام {$member->full_name} عزیز، فیس ماهانه کتاب‌خانه شما تا تاریخ {$dueDate} باید پرداخت شود. در صورت تاخیر، روزانه {$member->monthly_fee_daily_fine} افغانی جریمه می‌شود.";
        }

        return "سلام {$member->full_name} عزیز، تاریخ پرداخت فیس ماهانه کتاب‌خانه شما گذشته است. مبلغ قابل پرداخت فعلی {$balance} افغانی است که شامل {$fine} افغانی جریمه می‌باشد.";
    }

    private function legacyMonthlyFeeReminderText(LibraryMember $member): string
    {
        $dueDate = $member->next_payment_due_at?->format('Y-m-d') ?: 'N/A';
        $fine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
        $balance = (int) $member->membership_fee + $fine;

        if ($member->next_payment_due_at && $member->next_payment_due_at->isFuture()) {
            return "سلام {$member->full_name} عزیز، فیس ماهانه کتابخانه شما تا تاریخ {$dueDate} باید پرداخت شود. در صورت تأخیر، روزانه {$member->monthly_fee_daily_fine} افغانی جریمه می‌شود.";
        }

        return "سلام {$member->full_name} عزیز، تاریخ پرداخت فیس ماهانه کتابخانه شما گذشته است. مبلغ قابل پرداخت فعلی {$balance} افغانی است که شامل {$fine} افغانی جریمه می‌باشد.";
    }

    private function nextCode(string $prefix): string
    {
        return $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) (MembershipCard::count() + Book::count() + BookLoan::count() + LibraryMember::count() + 1), 5, '0', STR_PAD_LEFT);
    }

    private function memberStatusLabels(): array
    {
        return [
            'active' => 'فعال',
            'suspended' => 'تعلیق',
            'left' => 'خارج شده',
        ];
    }

    private function loanStatusLabels(): array
    {
        return [
            'borrowed' => 'امانت داده شده',
            'returned' => 'برگشت شده',
            'lost' => 'گم شده',
            'late' => 'ناوقت',
        ];
    }

    private function canWriteLibrary(): bool
    {
        $user = auth()->user();

        return $user->role === User::ROLE_LIBRARIAN;
    }
}
