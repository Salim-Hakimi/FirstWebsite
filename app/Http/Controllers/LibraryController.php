<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\LibraryMember;
use App\Models\MembershipCard;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['active', 'suspended', 'left'])],
        ]);

        $members = LibraryMember::query()
            ->with(['membershipCards' => fn ($query) => $query->where('scope', 'library')->latest('expires_at')])
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
            })
            ->latest()
            ->get();

        $activeLoanStatuses = ['borrowed', 'late'];
        $followUpMembers = LibraryMember::query()
            ->whereDate('next_payment_due_at', '<=', today())
            ->where('payment_status', '!=', 'paid')
            ->orderBy('next_payment_due_at')
            ->get();
        $expiredCards = MembershipCard::query()
            ->with('cardable')
            ->where('scope', 'library')
            ->whereDate('expires_at', '<=', today())
            ->where('payment_status', '!=', 'paid')
            ->latest('expires_at')
            ->get();

        return view('library.index', [
            'members' => $members,
            'filters' => $filters,
            'memberStatusLabels' => $this->memberStatusLabels(),
            'books' => Book::query()->latest()->limit(30)->get(),
            'loans' => BookLoan::query()->with(['member', 'book'])->latest()->limit(40)->get(),
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
            'availableBooks' => Book::where('available_copies', '>', 0)->orderBy('title')->get(),
            'expiringMembers' => $followUpMembers,
            'expiredCards' => $expiredCards,
        ]);
    }

    public function showMember(LibraryMember $member): View
    {
        $member->load([
            'registeredBy',
            'membershipCards' => fn ($query) => $query->where('scope', 'library')->latest('expires_at'),
            'loans.book',
            'loans.recordedBy',
        ]);

        return view('library.members.show', [
            'member' => $member,
            'memberStatusLabels' => $this->memberStatusLabels(),
            'loanStatusLabels' => $this->loanStatusLabels(),
            'canWriteLibrary' => $this->canWriteLibrary(),
            'activeLoanCount' => $member->loans->whereIn('status', ['borrowed', 'late'])->count(),
            'returnedLoanCount' => $member->loans->where('status', 'returned')->count(),
            'fineTotal' => (int) $member->loans->sum('fine_amount'),
        ]);
    }

    public function storeMember(Request $request): RedirectResponse
    {
        $validated = $this->validateMember($request);
        $joinedAt = $validated['joined_at'] ?? now()->toDateString();
        $paymentStatus = $validated['payment_status'] ?? 'unpaid';
        $validated['profile_photo_path'] = $this->storeProfilePhoto($request);
        unset($validated['issue_card'], $validated['profile_photo'], $validated['remove_profile_photo']);

        $member = LibraryMember::create(array_merge($validated, [
            'registered_by' => $request->user()->id,
            'member_code' => ($validated['member_code'] ?? null) ?: $this->nextCode('LIB-M'),
            'joined_at' => $joinedAt,
            'membership_expires_at' => Carbon::parse($joinedAt)->addMonth(),
            'last_paid_at' => $paymentStatus === 'paid' ? $joinedAt : null,
            'next_payment_due_at' => Carbon::parse($joinedAt)->addMonth(),
        ]));

        if ($request->boolean('issue_card')) {
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
        unset($validated['issue_card'], $validated['profile_photo'], $validated['remove_profile_photo']);

        $member->update($validated);

        return redirect()->route('library.members.show', $member)->with('status', 'مشخصات عضو کتاب‌خانه ویرایش شد.');
    }

    public function issueMemberCard(Request $request, LibraryMember $member): RedirectResponse
    {
        $card = $this->issueLibraryCard($member, $request);

        return redirect()->route('membership-cards.print', $card);
    }

    public function storeBook(Request $request): RedirectResponse
    {
        $validated = $this->validateBook($request);

        Book::create(array_merge($validated, [
            'registered_by' => $request->user()->id,
            'available_copies' => $validated['total_copies'],
            'barcode' => $validated['barcode'] ?: $this->nextCode('BOOK'),
        ]));

        return back()->with('status', 'کتاب جدید ثبت شد.');
    }

    public function editBook(Book $book): View
    {
        return view('library.books.form', [
            'book' => $book,
        ]);
    }

    public function updateBook(Request $request, Book $book): RedirectResponse
    {
        $validated = $this->validateBook($request, $book);
        $copyDifference = (int) $validated['total_copies'] - (int) $book->total_copies;
        $validated['available_copies'] = max(0, (int) $book->available_copies + $copyDifference);

        $book->update($validated);

        return redirect()->route('library.index')->with('status', 'کتاب ویرایش شد.');
    }

    public function storeLoan(Request $request): RedirectResponse
    {
        $validated = $this->validateLoan($request);

        DB::transaction(function () use ($request, $validated): void {
            $book = Book::lockForUpdate()->findOrFail($validated['book_id']);

            if ($book->available_copies < 1) {
                abort(422, 'این کتاب فعلاً نسخه قابل امانت ندارد.');
            }

            $book->decrement('available_copies');

            BookLoan::create(array_merge($validated, [
                'recorded_by' => $request->user()->id,
                'loan_code' => $validated['loan_code'] ?: $this->nextCode('LOAN'),
                'status' => 'borrowed',
            ]));
        });

        return back()->with('status', 'امانت کتاب ثبت شد.');
    }

    public function editLoan(BookLoan $loan): View
    {
        return view('library.loans.form', [
            'loan' => $loan->load(['member', 'book']),
            'activeMembers' => LibraryMember::where('status', 'active')->orderBy('full_name')->get(),
            'books' => Book::orderBy('title')->get(),
        ]);
    }

    public function updateLoan(Request $request, BookLoan $loan): RedirectResponse
    {
        $validated = $this->validateLoan($request, $loan);
        unset($validated['book_id'], $validated['library_member_id']);

        $loan->update($validated);

        return redirect()->route('library.members.show', $loan->member)->with('status', 'امانت کتاب ویرایش شد.');
    }

    public function returnLoan(Request $request, BookLoan $loan): RedirectResponse
    {
        $validated = $request->validate([
            'returned_at' => ['required', 'date'],
            'fine_amount' => ['nullable', 'integer', 'min:0'],
            'condition_in' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($loan, $validated): void {
            if ($loan->status === 'returned') {
                return;
            }

            $loan->update([
                'returned_at' => $validated['returned_at'],
                'condition_in' => $validated['condition_in'] ?? null,
                'fine_amount' => $validated['fine_amount'] ?? 0,
                'status' => 'returned',
            ]);

            $loan->book()->increment('available_copies');
        });

        return back()->with('status', 'برگشت کتاب ثبت شد.');
    }

    private function validateMember(Request $request, ?LibraryMember $member = null): array
    {
        return $request->validate([
            'member_code' => ['nullable', 'string', 'max:60', Rule::unique('library_members', 'member_code')->ignore($member)],
            'full_name' => ['required', 'string', 'max:120'],
            'father_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'tazkira_number' => ['nullable', 'string', 'max:80'],
            'education_place' => ['nullable', 'string', 'max:160'],
            'department_or_grade' => ['nullable', 'string', 'max:160'],
            'address' => ['nullable', 'string', 'max:220'],
            'membership_fee' => ['nullable', 'integer', 'min:0'],
            'payment_status' => ['nullable', Rule::in(['paid', 'unpaid'])],
            'joined_at' => ['nullable', 'date'],
            'membership_expires_at' => ['nullable', 'date'],
            'next_payment_due_at' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['active', 'suspended', 'left'])],
            'notes' => ['nullable', 'string', 'max:700'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'remove_profile_photo' => ['nullable', 'boolean'],
            'issue_card' => ['nullable', 'boolean'],
        ]);
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
        return $request->validate([
            'isbn' => ['nullable', 'string', 'max:40'],
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
    }

    private function validateLoan(Request $request, ?BookLoan $loan = null): array
    {
        return $request->validate([
            'loan_code' => ['nullable', 'string', 'max:60', Rule::unique('book_loans', 'loan_code')->ignore($loan)],
            'library_member_id' => [$loan ? 'nullable' : 'required', 'exists:library_members,id'],
            'book_id' => [$loan ? 'nullable' : 'required', 'exists:books,id'],
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
            'expires_at' => $issuedAt->copy()->addMonth(),
            'fee_amount' => $member->membership_fee,
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'paid' ? $issuedAt : null,
            'created_by' => $request->user()->id,
        ]);

        $member->update([
            'membership_expires_at' => $card->expires_at,
            'next_payment_due_at' => $card->expires_at,
            'last_paid_at' => $paymentStatus === 'paid' ? $issuedAt : $member->last_paid_at,
        ]);

        return $card;
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
