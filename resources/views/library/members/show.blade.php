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
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">{{ $member->full_name }}</h3>
                            <p class="mb-0 text-white-50" data-i18n="libraryMemberProfileDescription">Membership, card, payments, and book-loan history are shown in one member profile.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            @if ($canWriteLibrary)
                                <a class="btn btn-outline-light btn-rounded mr-2" href="{{ route('library.members.edit', $member) }}" data-i18n="editMember">Edit member</a>
                            @endif
                            <a class="btn btn-outline-light btn-rounded" href="{{ route('library.index') }}" data-i18n="back">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body student-profile-card">
                    @if ($member->profile_photo_path)
                        <img class="student-profile-photo" src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
                    @else
                        <div class="student-profile-photo student-profile-photo-empty">{{ strtoupper(substr($member->full_name ?: 'M', 0, 1)) }}</div>
                    @endif
                    <h4 class="mb-1">{{ $member->full_name }}</h4>
                    <p class="text-muted mb-3">{{ $member->member_code ?: 'N/A' }}</p>
                    <div class="student-profile-actions">
                        @if ($canWriteLibrary)
                            <a class="btn btn-primary btn-sm" href="{{ route('library.members.edit', $member) }}" data-i18n="edit">Edit</a>
                        @endif
                        @if ($latestCard)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}" data-i18n="printCard">Print card</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0" data-i18n="{{ $statusMeta['key'] }}">{{ $statusMeta['label'] }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">S</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="membershipStatus">Membership status</h6><p class="text-muted mb-0">{{ $member->member_code ?: 'N/A' }}</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $member->membership_expires_at?->format('Y-m-d') ?: 'N/A' }}</h3></div><div class="col-3"><div class="icon icon-box-warning"><span class="metric-icon">C</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="membershipExpiry">Membership expiry</h6><p class="text-muted mb-0" data-i18n="{{ $member->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $member->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $activeLoanCount }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">L</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="activeLoans">Active loans</h6><p class="text-muted mb-0" data-i18n="booksWithMember">Books with member</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($fineTotal) }}</h3></div><div class="col-3"><div class="icon icon-box-danger"><span class="metric-icon">F</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="fines">Fines</h6><p class="text-muted mb-0">AFN</p></div></div>
        </div>
    </div>

    @unless ($canWriteLibrary)
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body user-access-note">
                        <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>i</span></div></div>
                        <p class="text-muted mb-0" data-i18n="libraryViewOnlyNotice">View-only mode is active. Admin users can review the library database, while create, edit, loan, return, and card actions are reserved for the Librarian account.</p>
                    </div>
                </div>
            </div>
        </div>
    @endunless

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="memberDetails">Member details</h4>
                    <p class="card-description" data-i18n="memberDetailsDescription">Identity, education, contact, and registration information.</p>
                    <div class="student-detail-grid">
                        <div><span data-i18n="fatherName">Father name</span><strong>{{ $member->father_name }}</strong></div>
                        <div><span data-i18n="phoneNumber">Phone number</span><strong>{{ $member->phone }}</strong></div>
                        <div>
                            <span data-i18n="emailAddress">Email address</span>
                            <strong>
                                @if ($member->email)
                                    {{ $member->email }}
                                @else
                                    <span data-i18n="noEmail">No email</span>
                                @endif
                            </strong>
                        </div>
                        <div><span data-i18n="idTazkira">ID / Tazkira</span><strong>{{ $member->tazkira_number ?: 'N/A' }}</strong></div>
                        <div><span data-i18n="educationPlace">Education place</span><strong>{{ $member->education_place ?: 'N/A' }}</strong></div>
                        <div><span data-i18n="departmentGrade">Department / grade</span><strong>{{ $member->department_or_grade ?: 'N/A' }}</strong></div>
                        <div><span data-i18n="address">Address</span><strong>{{ $member->address ?: 'N/A' }}</strong></div>
                        <div>
                            <span data-i18n="registeredBy">Registered by</span>
                            <strong>
                                @if ($member->registeredBy)
                                    {{ $member->registeredBy->name }}
                                @else
                                    <span data-i18n="Unknown">Unknown</span>
                                @endif
                            </strong>
                        </div>
                        <div><span data-i18n="joinedAt">Joined at</span><strong>{{ $member->joined_at?->format('Y-m-d') ?: 'N/A' }}</strong></div>
                        <div><span data-i18n="notes">Notes</span><strong>{{ $member->notes ?: 'N/A' }}</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <div>
                            <h4 class="card-title mb-1" data-i18n="cardsAndPayments">Cards and payments</h4>
                            <p class="text-muted mb-0" data-i18n="cardsAndPaymentsDescription">Library card status and monthly fee tracking.</p>
                        </div>
                        @if ($canWriteLibrary)
                            <form method="POST" action="{{ route('library.members.card.issue', $member) }}" class="mt-3 mt-md-0">
                                @csrf
                                <button class="btn btn-primary" type="submit" data-i18n="issueNewCard">Issue new card</button>
                            </form>
                        @endif
                    </div>

                    <div class="preview-list">
                        <div class="preview-item border-bottom">
                            <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>F</span></div></div>
                            <div class="preview-item-content">
                                <p class="preview-subject mb-1" data-i18n="monthlyFee">Monthly fee</p>
                                <p class="text-muted mb-0">{{ number_format((int) $member->membership_fee) }} AFN</p>
                            </div>
                        </div>
                        <div class="preview-item border-bottom">
                            <div class="preview-thumbnail"><div class="preview-icon bg-warning"><span>D</span></div></div>
                            <div class="preview-item-content">
                                <p class="preview-subject mb-1" data-i18n="nextDue">Next due</p>
                                <p class="text-muted mb-0">{{ $member->next_payment_due_at?->format('Y-m-d') ?: 'N/A' }}</p>
                            </div>
                        </div>

                        @forelse ($member->membershipCards as $card)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>C</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $card->card_number }}</p>
                                    <p class="text-muted mb-0">{{ $card->issued_at?->format('Y-m-d') }} <span data-i18n="to">to</span> {{ $card->expires_at?->format('Y-m-d') }} · <span data-i18n="{{ $card->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $card->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</span></p>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $card) }}" data-i18n="printCard">Print card</a>
                            </div>
                        @empty
                            <p class="text-muted mb-0" data-i18n="noLibraryCard">No library card has been issued for this member.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="loanHistory">Book loan history</h4>
                    <p class="card-description">{{ $returnedLoanCount }} <span data-i18n="returnedLoans">returned loans</span> · {{ $activeLoanCount }} <span data-i18n="activeLoans">active loans</span></p>
                    <div class="preview-list">
                        @forelse ($member->loans->sortByDesc('borrowed_at') as $loan)
                            @php($loanStatus = $loanStatusMeta[$loan->status] ?? ['key' => 'statusUnknown', 'label' => $loan->status])
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon {{ $loan->status === 'returned' ? 'bg-success' : 'bg-warning' }}"><span>L</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">
                                        @if ($loan->book)
                                            {{ $loan->book->title }}
                                        @else
                                            <span data-i18n="deletedBook">Deleted book</span>
                                        @endif
                                    </p>
                                    <p class="text-muted mb-0">
                                        <span data-i18n="loanCode">Loan code</span>: {{ $loan->loan_code ?: 'N/A' }} ·
                                        <span data-i18n="recordedBy">Recorded by</span>:
                                        @if ($loan->recordedBy)
                                            {{ $loan->recordedBy->name }}
                                        @else
                                            <span data-i18n="Unknown">Unknown</span>
                                        @endif
                                    </p>
                                    <p class="text-muted mb-0">{{ $loan->borrowed_at?->format('Y-m-d') }} <span data-i18n="to">to</span> {{ $loan->due_at?->format('Y-m-d') ?? 'N/A' }} · <span data-i18n="{{ $loanStatus['key'] }}">{{ $loanStatus['label'] }}</span></p>
                                    @if ($canWriteLibrary && $loan->status !== 'returned')
                                        <form method="POST" action="{{ route('library.loans.return', $loan) }}" class="library-return-form mt-3">
                                            @csrf
                                            @method('PUT')
                                            <input class="form-control" name="returned_at" type="date" value="{{ now()->format('Y-m-d') }}" required>
                                            <input class="form-control" name="fine_amount" type="number" min="0" value="0" data-i18n-placeholder="fineAmount" placeholder="Fine amount">
                                            <input class="form-control" name="condition_in" data-i18n-placeholder="conditionIn" placeholder="Condition in">
                                            <button class="btn btn-primary" type="submit" data-i18n="markReturned">Mark returned</button>
                                        </form>
                                    @endif
                                </div>
                                <div class="shortcut-action pt-2 pt-sm-0">
                                    @if ((int) $loan->fine_amount > 0)
                                        <span class="badge badge-outline-warning">{{ number_format((int) $loan->fine_amount) }} AFN</span>
                                    @endif
                                    @if ($canWriteLibrary)
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.loans.edit', $loan) }}" data-i18n="edit">Edit</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0" data-i18n="noMemberLoans">No loans have been registered for this member.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
