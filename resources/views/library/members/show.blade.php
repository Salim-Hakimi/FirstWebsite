@extends('admin.layout')

@section('title', 'Library Member Profile - Fanous Admin')

@section('content')
    @php
        $latestCard = $member->membershipCards->first();
        $memberStatusMeta = [
            'active' => ['key' => 'statusActive', 'label' => 'Active'],
            'suspended' => ['key' => 'statusSuspended', 'label' => 'Suspended'],
            'left' => ['key' => 'statusLeft', 'label' => 'Left'],
        ];
        $loanStatusMeta = [
            'borrowed' => ['key' => 'loanBorrowed', 'label' => 'Borrowed'],
            'returned' => ['key' => 'loanReturned', 'label' => 'Returned'],
            'lost' => ['key' => 'loanLost', 'label' => 'Lost'],
            'late' => ['key' => 'loanLate', 'label' => 'Late'],
        ];
        $statusMeta = $memberStatusMeta[$member->status] ?? ['key' => 'statusUnknown', 'label' => $member->status];
        $monthlyFine = $monthlyFeeFine ?? max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
        $monthlyBalance = $monthlyFeeBalance ?? $member->monthlyFeeBalance();
        $reminderText = $monthlyFeeReminderText ?? '';
        $whatsappDigits = preg_replace('/\D+/', '', $member->phone ?? '');
        if (str_starts_with($whatsappDigits, '0')) {
            $whatsappDigits = '93'.substr($whatsappDigits, 1);
        }
    @endphp

    <section class="student-profile-hero">
        <div class="student-profile-identity">
            @if ($member->profile_photo_path)
                <img class="student-profile-photo" src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
            @else
                <div class="student-profile-photo student-profile-photo-empty">{{ strtoupper(substr($member->full_name ?: 'M', 0, 1)) }}</div>
            @endif

            <div>
                <span class="badge badge-outline-primary" data-i18n="{{ $statusMeta['key'] }}">{{ $statusMeta['label'] }}</span>
                <h1>{{ $member->full_name }}</h1>
                <p>{{ $member->member_code ?: 'N/A' }} · {{ $member->education_place ?: 'Education not recorded' }}</p>
                <div class="student-profile-actions">
                    @if ($canWriteLibrary)
                        <a class="btn btn-primary btn-sm" href="{{ route('library.members.edit', $member) }}" data-i18n="editMember">Edit member</a>
                    @endif
                    @if ($latestCard)
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}" data-i18n="printCard">Print card</a>
                    @endif
                    <a class="btn btn-dark btn-sm" href="{{ route('library.index') }}" data-i18n="back">Back</a>
                </div>
            </div>
        </div>

        <div class="student-profile-snapshot">
            <span><strong>{{ $member->membership_expires_at?->format('Y-m-d') ?: 'N/A' }}</strong><span data-i18n="membershipExpiry">Membership expiry</span></span>
            <span><strong data-i18n="{{ $member->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $member->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</strong><span data-i18n="payment">Payment</span></span>
            <span><strong>{{ $activeLoanCount }}</strong><span data-i18n="activeLoans">Active loans</span></span>
            <span><strong>{{ number_format($monthlyBalance) }}</strong><span>Fee balance</span></span>
        </div>
    </section>

    @unless ($canWriteLibrary)
        <section class="student-workspace-panel">
            <div class="user-access-note">
                <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>i</span></div></div>
                <p class="text-muted mb-0" data-i18n="libraryViewOnlyNotice">View-only mode is active. Admin users can review the library database, while create, edit, loan, return, and card actions are reserved for the Librarian account.</p>
            </div>
        </section>
    @endunless

    <section class="student-profile-layout">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label" data-i18n="memberDetails">Member details</span>
                    <h2>{{ $member->full_name }}</h2>
                    <p data-i18n="memberDetailsDescription">Identity, education, contact, and registration information.</p>
                </div>
            </div>

            <div class="student-detail-grid">
                <div><span data-i18n="fatherName">Father name</span><strong>{{ $member->father_name }}</strong></div>
                <div><span data-i18n="phoneNumber">Phone number</span><strong>{{ $member->phone }}</strong></div>
                <div><span data-i18n="emailAddress">Email address</span><strong>{{ $member->email ?: __('No email') }}</strong></div>
                <div><span data-i18n="idTazkira">ID / Tazkira</span><strong>{{ $member->tazkira_number ?: 'N/A' }}</strong></div>
                <div><span data-i18n="educationPlace">Education place</span><strong>{{ $member->education_place ?: 'N/A' }}</strong></div>
                <div><span data-i18n="departmentGrade">Department / grade</span><strong>{{ $member->department_or_grade ?: 'N/A' }}</strong></div>
                <div><span data-i18n="address">Address</span><strong>{{ $member->address ?: 'N/A' }}</strong></div>
                <div><span data-i18n="joinedAt">Joined at</span><strong>{{ $member->joined_at?->format('Y-m-d') ?: 'N/A' }}</strong></div>
                <div><span data-i18n="registeredBy">Registered by</span><strong>{{ $member->registeredBy?->name ?: __('Unknown') }}</strong></div>
                <div><span data-i18n="notes">Notes</span><strong>{{ $member->notes ?: 'N/A' }}</strong></div>
            </div>
        </div>

        <aside class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label" data-i18n="cardsAndPayments">Cards and payments</span>
                    <h2 data-i18n="libraryCardStatus">Library card status</h2>
                    <p data-i18n="cardsAndPaymentsDescription">Library card status and monthly fee tracking.</p>
                </div>
                @if ($canWriteLibrary)
                    <form method="POST" action="{{ route('library.members.card.issue', $member) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm" type="submit" data-i18n="issueNewCard">Issue new card</button>
                    </form>
                @endif
            </div>

            <div class="student-timeline-list">
                @if ($canWriteLibrary)
                    <form method="POST" action="{{ route('library.members.monthly-payment.store', $member) }}" class="student-timeline-item">
                        @csrf
                        <span class="student-timeline-icon">P</span>
                        <div>
                            <strong>Record monthly payment</strong>
                            <p>Collect {{ number_format($monthlyBalance) }} AFN, clear the monthly fine, and move the next due date one month forward.</p>
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit">Pay & receipt</button>
                    </form>
                @endif

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">F</span>
                    <div>
                        <strong data-i18n="monthlyFee">Monthly fee</strong>
                        <p>{{ number_format((int) $member->membership_fee) }} AFN</p>
                    </div>
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">D</span>
                    <div>
                        <strong data-i18n="nextDue">Next due</strong>
                        <p>{{ $member->next_payment_due_at?->format('Y-m-d') ?: 'N/A' }}</p>
                    </div>
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">20</span>
                    <div>
                        <strong>Daily late fine</strong>
                        <p>{{ number_format((int) ($member->monthly_fee_daily_fine ?? 20)) }} AFN per day after due date - current fine {{ number_format($monthlyFine) }} AFN</p>
                    </div>
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">W</span>
                    <div>
                        <strong>WhatsApp reminder</strong>
                        <p>{{ $reminderText }}</p>
                    </div>
                    @if ($whatsappDigits)
                        <a class="btn btn-outline-secondary btn-sm" href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode($reminderText) }}" target="_blank" rel="noopener">Send</a>
                    @endif
                </div>

                @forelse ($member->membershipCards as $card)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">C</span>
                        <div>
                            <strong>{{ $card->card_number }}</strong>
                            <p>{{ $card->issued_at?->format('Y-m-d') }} <span data-i18n="to">to</span> {{ $card->expires_at?->format('Y-m-d') }} · <span data-i18n="{{ $card->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $card->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</span></p>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $card) }}" data-i18n="printCard">Print card</a>
                    </div>
                @empty
                    <div class="student-directory-empty" data-i18n="noLibraryCard">No library card has been issued for this member.</div>
                @endforelse
            </div>
        </aside>
    </section>

    <section class="student-workspace-panel">
        <div class="student-panel-head">
            <div>
                <span class="student-panel-label" data-i18n="loanHistory">Book loan history</span>
                <h2 data-i18n="recentLoans">Recent loans</h2>
                <p>{{ $returnedLoanCount }} <span data-i18n="returnedLoans">returned loans</span> · {{ $activeLoanCount }} <span data-i18n="activeLoans">active loans</span></p>
            </div>
        </div>

        <div class="student-timeline-list">
            @forelse ($member->loans->sortByDesc('borrowed_at') as $loan)
                @php
                    $loanStatus = $loanStatusMeta[$loan->status] ?? ['key' => 'statusUnknown', 'label' => $loan->status];
                    $isLateLoan = in_array($loan->status, ['borrowed', 'late'], true) && $loan->due_at && $loan->due_at->isPast();
                @endphp
                <div class="student-timeline-item library-loan-row">
                    <span class="student-timeline-icon">{{ $isLateLoan ? '!' : 'L' }}</span>
                    <div>
                        <strong>
                            @if ($loan->book)
                                {{ $loan->book->title }}
                            @else
                                <span data-i18n="deletedBook">Deleted book</span>
                            @endif
                        </strong>
                        <p>
                            <span data-i18n="loanCode">Loan code</span>: {{ $loan->loan_code ?: 'N/A' }} ·
                            Copy: {{ $loan->copy?->copy_code ?: 'N/A' }} ·
                            {{ $loan->borrowed_at?->format('Y-m-d') }} <span data-i18n="to">to</span> {{ $loan->due_at?->format('Y-m-d') ?? 'N/A' }} ·
                            <span data-i18n="{{ $loanStatus['key'] }}">{{ $isLateLoan ? 'Late' : $loanStatus['label'] }}</span>
                        </p>

                        @if ($canWriteLibrary && $loan->status !== 'returned')
                            <form method="POST" action="{{ route('library.loans.return', $loan) }}" class="library-return-form mt-3">
                                @csrf
                                @method('PUT')
                                <input class="form-control" name="returned_at" type="date" value="{{ now()->format('Y-m-d') }}" required>
                                <input class="form-control" name="fine_amount" type="number" min="0" value="0" data-i18n-placeholder="fineAmount" placeholder="Fine amount">
                                <select class="form-control" name="return_status">
                                    <option value="available">Good</option>
                                    <option value="damaged">Damaged</option>
                                    <option value="lost">Lost</option>
                                </select>
                                <input class="form-control" name="condition_in" data-i18n-placeholder="conditionIn" placeholder="Condition in">
                                <button class="btn btn-primary" type="submit" data-i18n="markReturned">Mark returned</button>
                            </form>
                        @endif
                    </div>

                    <div class="student-profile-actions">
                        @if ((int) $loan->fine_amount > 0)
                            <span class="badge badge-outline-warning">{{ number_format((int) $loan->fine_amount) }} AFN</span>
                        @endif
                        @if ($canWriteLibrary)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.loans.edit', $loan) }}" data-i18n="edit">Edit</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="student-directory-empty" data-i18n="noMemberLoans">No loans have been registered for this member.</div>
            @endforelse
        </div>
    </section>
@endsection
