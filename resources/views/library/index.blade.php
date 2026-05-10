@extends('admin.layout')

@section('title', 'Library - Fanous Admin')

@section('content')
    @php
        $activeMemberCount = $activeMemberCount ?? $members->where('status', 'active')->count();
        $bookTitleCount = $bookTitleCount ?? $books->count();
        $activeLoanCount = $activeLoanCount ?? $loans->whereIn('status', ['borrowed', 'late'])->count();
        $availableCopyCount = $availableCopyCount ?? $books->sum('available_copies');
        $overdueLoans = $overdueLoans ?? collect();
        $followUpCount = $expiringMembers->count() + $expiredCards->count() + $overdueLoans->count();
        $hasMemberFilters = filled($filters['q'] ?? null) || filled($filters['status'] ?? null);

        $memberStatusMeta = [
            'active' => ['key' => 'statusActive', 'label' => 'Active'],
            'suspended' => ['key' => 'statusSuspended', 'label' => 'Suspended'],
            'left' => ['key' => 'statusLeft', 'label' => 'Left'],
        ];
        $bookStatusMeta = [
            'available' => ['key' => 'bookAvailable', 'label' => 'Available'],
            'damaged' => ['key' => 'bookDamaged', 'label' => 'Damaged'],
            'lost' => ['key' => 'bookLost', 'label' => 'Lost'],
            'archived' => ['key' => 'bookArchived', 'label' => 'Archived'],
        ];
        $loanStatusMeta = [
            'borrowed' => ['key' => 'loanBorrowed', 'label' => 'Borrowed'],
            'returned' => ['key' => 'loanReturned', 'label' => 'Returned'],
            'lost' => ['key' => 'loanLost', 'label' => 'Lost'],
            'late' => ['key' => 'loanLate', 'label' => 'Late'],
        ];
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1" data-i18n="libraryManagement">Library Management</h3>
                            <p class="mb-0 text-white-50" data-i18n="libraryDescription">Manage members, books, cards, loans, returns, and payment follow-up from one clean database panel.</p>
                        </div>
                        @if ($canWriteLibrary)
                            <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                                <button class="btn btn-outline-light btn-rounded" type="button" data-library-panel-trigger="new-library-member" aria-controls="new-library-member" aria-expanded="false"><span aria-hidden="true">+</span> <span data-i18n="registerMember">Register member</span></button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $activeMemberCount }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">M</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="activeMembers">Active members</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $bookTitleCount }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">B</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="bookTitles">Book titles</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $activeLoanCount }}</h3></div><div class="col-3"><div class="icon icon-box-warning"><span class="metric-icon">L</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="activeLoans">Active loans</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $followUpCount }}</h3></div><div class="col-3"><div class="icon icon-box-danger"><span class="metric-icon">F</span></div></div></div><h6 class="text-muted font-weight-normal" data-i18n="needsFollowUp">Needs follow-up</h6></div></div>
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

    @if ($canWriteLibrary)
        <div class="row">
            <div class="col-lg-5 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" data-i18n="librarianWorkbench">Librarian workbench</h4>
                        <p class="card-description" data-i18n="librarianWorkbenchDescription">Fast paths for the daily library desk flow.</p>
                        <div class="library-action-grid">
                            <button class="library-action-tile" type="button" data-library-panel-trigger="new-library-member" aria-controls="new-library-member" aria-expanded="false">
                                <span class="preview-icon bg-primary"><span>M</span></span>
                                <strong data-i18n="registerMember">Register member</strong>
                            </button>
                            <button class="library-action-tile" type="button" data-library-panel-trigger="new-library-book" aria-controls="new-library-book" aria-expanded="false">
                                <span class="preview-icon bg-success"><span>B</span></span>
                                <strong data-i18n="registerBook">Register book</strong>
                            </button>
                            <button class="library-action-tile" type="button" data-library-panel-trigger="new-library-loan" aria-controls="new-library-loan" aria-expanded="false">
                                <span class="preview-icon bg-warning"><span>L</span></span>
                                <strong data-i18n="recordLoan">Record loan</strong>
                            </button>
                            <button class="library-action-tile" type="button" data-library-panel-trigger="library-members-panel" aria-controls="library-members-panel" aria-expanded="false">
                                <span class="preview-icon bg-info"><span>V</span></span>
                                <strong data-i18n="viewMembers">View members</strong>
                            </button>
                            <button class="library-action-tile" type="button" data-library-panel-trigger="return-library-copy" aria-controls="return-library-copy" aria-expanded="false">
                                <span class="preview-icon bg-danger"><span>R</span></span>
                                <strong data-i18n="markReturned">Mark returned</strong>
                            </button>
                            <a class="library-action-tile" href="{{ route('library.fee-reminders.index') }}">
                                <span class="preview-icon bg-warning"><span>F</span></span>
                                <strong>Fee reminders</strong>
                            </a>
                            <a class="library-action-tile" href="{{ route('library.inventory.report') }}">
                                <span class="preview-icon bg-secondary"><span>I</span></span>
                                <strong>Inventory report</strong>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2">
                            <div>
                                <h4 class="card-title mb-1" data-i18n="needsFollowUp">Needs follow-up</h4>
                                <p class="text-muted mb-0" data-i18n="libraryFollowUpDescription">Overdue loans and unpaid memberships that need attention.</p>
                            </div>
                            <span class="badge badge-outline-warning mt-3 mt-md-0">{{ $followUpCount }}</span>
                        </div>

                        <div class="preview-list">
                            @forelse ($overdueLoans as $loan)
                                <div class="preview-item border-bottom">
                                    <div class="preview-thumbnail"><div class="preview-icon bg-danger"><span>L</span></div></div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1">
                                            @if ($loan->member)
                                                {{ $loan->member->full_name }}
                                            @else
                                                <span data-i18n="unknownMember">Unknown member</span>
                                            @endif
                                            -
                                            @if ($loan->book)
                                                {{ $loan->book->title }}
                                            @else
                                                <span data-i18n="unknownBook">Unknown book</span>
                                            @endif
                                        </p>
                                        <p class="text-muted mb-0"><span data-i18n="dueAt">Due at</span>: {{ $loan->due_at?->format('Y-m-d') ?: 'N/A' }}</p>
                                    </div>
                                    @if ($loan->member)
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.members.show', $loan->member) }}" data-i18n="profile">Profile</a>
                                    @endif
                                </div>
                            @empty
                                <div class="preview-item border-bottom">
                                    <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>OK</span></div></div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1" data-i18n="noOverdueLoans">No overdue loans</p>
                                        <p class="text-muted mb-0" data-i18n="returnQueueClear">The return queue is clear for today.</p>
                                    </div>
                                </div>
                            @endforelse

                            @foreach ($expiringMembers->take(3) as $member)
                                @php
                                    $feeFine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
                                    $feeBalance = (int) $member->membership_fee + $feeFine;
                                    $isOverdueFee = $member->next_payment_due_at && $member->next_payment_due_at->isPast();
                                @endphp
                                <div class="preview-item border-bottom">
                                    <div class="preview-thumbnail"><div class="preview-icon bg-warning"><span>F</span></div></div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1">{{ $member->full_name }}</p>
                                        <p class="text-muted mb-0">
                                            <span data-i18n="nextDue">Next due</span>: {{ $member->next_payment_due_at?->format('Y-m-d') ?: 'N/A' }}
                                            - {{ $isOverdueFee ? 'Overdue' : 'Reminder' }}
                                            - {{ number_format($feeBalance) }} AFN
                                        </p>
                                    </div>
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.members.show', $member) }}" data-i18n="profile">Profile</a>
                                </div>
                            @endforeach

                            @foreach ($expiredCards->take(3) as $card)
                                <div class="preview-item border-bottom">
                                    <div class="preview-thumbnail"><div class="preview-icon bg-warning"><span>C</span></div></div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject mb-1">
                                            @if ($card->holder_name)
                                                {{ $card->holder_name }}
                                            @else
                                                <span data-i18n="unknownMember">Unknown member</span>
                                            @endif
                                        </p>
                                        <p class="text-muted mb-0"><span data-i18n="expiry">Expiry</span>: {{ $card->expires_at?->format('Y-m-d') ?: 'N/A' }} - {{ $card->card_number }}</p>
                                    </div>
                                    @if ($card->cardable)
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.members.show', $card->cardable) }}" data-i18n="profile">Profile</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($canWriteLibrary)
        <div class="row" id="library-action-forms">
            <div class="col-12 grid-margin stretch-card library-form-empty" data-library-panel-empty>
                <div class="card">
                    <div class="card-body user-access-note">
                        <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>i</span></div></div>
                        <p class="text-muted mb-0" data-i18n="libraryPanelHint">Choose one of the cards above to open member registration, book registration, loan recording, or member review here.</p>
                    </div>
                </div>
            </div>

            <div class="col-12 grid-margin stretch-card library-form-panel" id="new-library-member" data-library-panel>
                <div class="card">
                    <div class="card-body">
                        <div class="library-form-head">
                            <div>
                                <h4 class="card-title" data-i18n="registerLibraryMember">Register library member</h4>
                                <p class="card-description" data-i18n="registerLibraryMemberDescription">Create a member profile and optionally print a monthly library card.</p>
                            </div>
                            <button class="btn btn-dark btn-sm" type="button" data-library-panel-close data-i18n="close">Close</button>
                        </div>
                        <form method="POST" action="{{ route('library.members.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group"><label data-i18n="memberCode">Member code</label><input class="form-control" name="member_code" data-i18n-placeholder="autoIfEmpty" placeholder="Auto if empty"></div>
                                <div class="col-md-6 form-group"><label data-i18n="fullName">Full name</label><input class="form-control" name="full_name" required></div>
                                <div class="col-md-6 form-group"><label data-i18n="fatherName">Father name</label><input class="form-control" name="father_name" required></div>
                                <div class="col-md-6 form-group"><label data-i18n="phoneNumber">Phone number</label><input class="form-control" name="phone" required></div>
                                <div class="col-md-6 form-group"><label data-i18n="emailAddress">Email address</label><input class="form-control" name="email" type="email"></div>
                                <div class="col-md-6 form-group"><label data-i18n="profilePhoto">Profile photo</label><input class="form-control" name="profile_photo" type="file" accept="image/*"></div>
                                <div class="col-md-6 form-group"><label data-i18n="idTazkira">ID / Tazkira</label><input class="form-control" name="tazkira_number"></div>
                                <div class="col-md-6 form-group"><label data-i18n="educationPlace">Education place</label><input class="form-control" name="education_place"></div>
                                <div class="col-md-6 form-group"><label data-i18n="departmentGrade">Department / grade</label><input class="form-control" name="department_or_grade"></div>
                                <div class="col-md-6 form-group"><label data-i18n="monthlyFee">Monthly fee</label><input class="form-control" name="membership_fee" type="number" min="0" value="0"></div>
                                <div class="col-md-6 form-group">
                                    <label data-i18n="paymentStatus">Payment status</label>
                                    <select class="form-control" name="payment_status">
                                        <option value="unpaid" data-i18n="unpaid">Unpaid</option>
                                        <option value="paid" data-i18n="paid">Paid</option>
                                    </select>
                                </div>
                                <div class="col-md-6 form-group"><label data-i18n="joinedAt">Joined at</label><input class="form-control" name="joined_at" type="date" value="{{ now()->format('Y-m-d') }}"></div>
                                <div class="col-md-6 form-group">
                                    <label data-i18n="status">Status</label>
                                    <select class="form-control" name="status">
                                        @foreach ($memberStatusMeta as $value => $meta)
                                            <option value="{{ $value }}" data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 form-group"><label data-i18n="address">Address</label><input class="form-control" name="address"></div>
                                <div class="col-12 form-group"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
                                <div class="col-12">
                                    <button class="btn btn-primary mr-2" type="submit" data-i18n="saveMember">Save member</button>
                                    <button class="btn btn-outline-secondary" name="issue_card" value="1" type="submit" data-i18n="saveAndPrintCard">Save and print card</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 grid-margin stretch-card library-form-panel" id="new-library-book" data-library-panel>
                <div class="card">
                    <div class="card-body">
                        <div class="library-form-head">
                            <div>
                                <h4 class="card-title" data-i18n="registerBook">Register book</h4>
                                <p class="card-description" data-i18n="registerBookDescription">Record book identity, shelf, copies, and current status.</p>
                            </div>
                            <button class="btn btn-dark btn-sm" type="button" data-library-panel-close data-i18n="close">Close</button>
                        </div>
                        <form method="POST" action="{{ route('library.books.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group"><label>ISBN</label><input class="form-control" name="isbn"></div>
                                <div class="col-md-6 form-group"><label data-i18n="barcode">Barcode</label><input class="form-control" name="barcode" data-i18n-placeholder="autoIfEmpty" placeholder="Auto if empty"></div>
                                <div class="col-12 form-group"><label data-i18n="bookTitle">Book title</label><input class="form-control" name="title" required></div>
                                <div class="col-md-6 form-group"><label data-i18n="author">Author</label><input class="form-control" name="author"></div>
                                <div class="col-md-6 form-group"><label data-i18n="publisher">Publisher</label><input class="form-control" name="publisher"></div>
                                <div class="col-md-4 form-group"><label data-i18n="language">Language</label><input class="form-control" name="language"></div>
                                <div class="col-md-4 form-group"><label data-i18n="edition">Edition</label><input class="form-control" name="edition"></div>
                                <div class="col-md-4 form-group"><label data-i18n="publishedYear">Published year</label><input class="form-control" name="published_year" type="number" min="1000" max="{{ now()->year }}"></div>
                                <div class="col-md-4 form-group"><label data-i18n="pages">Pages</label><input class="form-control" name="pages" type="number" min="1"></div>
                                <div class="col-md-4 form-group"><label data-i18n="category">Category</label><input class="form-control" name="category"></div>
                                <div class="col-md-4 form-group"><label data-i18n="shelfCode">Shelf code</label><input class="form-control" name="shelf_code"></div>
                                <div class="col-md-6 form-group"><label data-i18n="totalCopies">Total copies</label><input class="form-control" name="total_copies" type="number" min="1" value="1" required></div>
                                <div class="col-md-6 form-group">
                                    <label data-i18n="status">Status</label>
                                    <select class="form-control" name="status">
                                        @foreach ($bookStatusMeta as $value => $meta)
                                            <option value="{{ $value }}" data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 form-group"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="3"></textarea></div>
                                <div class="col-12"><button class="btn btn-primary" type="submit" data-i18n="saveBook">Save book</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 grid-margin stretch-card library-form-panel" id="new-library-loan" data-library-panel>
                <div class="card">
                    <div class="card-body">
                        <div class="library-form-head">
                            <div>
                                <h4 class="card-title" data-i18n="recordLoan">Record loan</h4>
                                <p class="card-description" data-i18n="recordLoanDescription">Choose an active member and an available book; the system reduces available copies automatically.</p>
                            </div>
                            <button class="btn btn-dark btn-sm" type="button" data-library-panel-close data-i18n="close">Close</button>
                        </div>
                        <form method="POST" action="{{ route('library.loans.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-3 form-group"><label data-i18n="loanCode">Loan code</label><input class="form-control" name="loan_code" data-i18n-placeholder="autoIfEmpty" placeholder="Auto if empty"></div>
                                <div class="col-md-3 form-group">
                                    <label data-i18n="member">Member</label>
                                    <select class="form-control" name="library_member_id" required>
                                        <option value="" data-i18n="selectMember">Select member</option>
                                        @foreach ($activeMembers as $member)
                                            <option value="{{ $member->id }}">{{ $member->full_name }} - {{ $member->member_code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label data-i18n="book">Book</label>
                                    <select class="form-control" name="book_id" required>
                                        <option value="" data-i18n="selectBook">Select book</option>
                                        @foreach ($availableBooks as $book)
                                            <option value="{{ $book->id }}">{{ $book->title }} - {{ $book->available_copies }} {{ __('available') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label>Copy barcode / code</label>
                                    <input class="form-control" name="copy_code" list="available-copy-codes" placeholder="Scan or enter copy code" required>
                                    <datalist id="available-copy-codes">
                                        @foreach ($availableBooks as $book)
                                            @foreach ($book->availableCopies as $copy)
                                                <option value="{{ $copy->copy_code }}">{{ $book->title }} - {{ $copy->shelf_code ?: 'No shelf' }}</option>
                                            @endforeach
                                        @endforeach
                                    </datalist>
                                </div>
                                <div class="col-md-3 form-group"><label data-i18n="borrowedAt">Borrowed at</label><input class="form-control" name="borrowed_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="col-md-3 form-group"><label data-i18n="dueAt">Due at</label><input class="form-control" name="due_at" type="date" value="{{ now()->addDays(7)->format('Y-m-d') }}"></div>
                                <div class="col-md-3 form-group"><label data-i18n="conditionOut">Condition out</label><input class="form-control" name="condition_out"></div>
                                <div class="col-md-6 form-group"><label data-i18n="notes">Notes</label><input class="form-control" name="notes"></div>
                                <div class="col-12"><button class="btn btn-primary" type="submit" data-i18n="saveLoan">Save loan</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 grid-margin stretch-card library-form-panel" id="return-library-copy" data-library-panel>
                <div class="card">
                    <div class="card-body">
                        <div class="library-form-head">
                            <div>
                                <h4 class="card-title">Return by barcode</h4>
                                <p class="card-description">Scan a copy label to find the active loan and mark the book as returned.</p>
                            </div>
                            <button class="btn btn-dark btn-sm" type="button" data-library-panel-close data-i18n="close">Close</button>
                        </div>
                        <form method="POST" action="{{ route('library.loans.return-by-copy') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label>Copy barcode / code</label>
                                    <input class="form-control @error('copy_code') is-invalid @enderror" name="copy_code" value="{{ old('copy_code') }}" placeholder="Scan returned copy" required autofocus>
                                    @error('copy_code') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="col-md-3 form-group"><label data-i18n="returnedAt">Returned at</label><input class="form-control" name="returned_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="col-md-2 form-group"><label data-i18n="fineAmount">Fine</label><input class="form-control" name="fine_amount" type="number" min="0" value="0"></div>
                                <div class="col-md-3 form-group">
                                    <label>Return status</label>
                                    <select class="form-control" name="return_status">
                                        <option value="available">Good / available</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="lost">Lost</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group"><label data-i18n="conditionIn">Condition in</label><input class="form-control" name="condition_in" placeholder="Good / damaged"></div>
                                <div class="col-12"><button class="btn btn-primary" type="submit">Return copy</button></div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12 grid-margin stretch-card {{ $canWriteLibrary ? 'library-form-panel' : '' }}" id="library-members-panel" @if ($canWriteLibrary) data-library-panel @endif>
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                        <div>
                            <h4 class="card-title mb-1" data-i18n="libraryMembers">Library members</h4>
                            <p class="text-muted mb-0" data-i18n="libraryMembersDescription">Each member has a profile for cards, payment status, and loan history.</p>
                        </div>
                        @if ($canWriteLibrary)
                            <button class="btn btn-dark btn-sm mt-3 mt-lg-0" type="button" data-library-panel-close data-i18n="close">Close</button>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('library.index') }}" class="library-filter-row mb-4">
                        <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" data-i18n-placeholder="searchLibraryMembers" placeholder="Search by name, phone, ID, code, or education">
                        <select class="form-control" name="status">
                            <option value="" data-i18n="allStatuses">All statuses</option>
                            @foreach ($memberStatusMeta as $value => $meta)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary" type="submit" data-i18n="search">Search</button>
                        <a class="btn btn-outline-secondary" href="{{ route('library.members.export', request()->only(['q', 'status'])) }}">CSV</a>
                        <a class="btn btn-dark" href="{{ route('library.index') }}" data-i18n="clear">Clear</a>
                    </form>

                    <div class="library-member-grid mb-4">
                        @forelse ($members as $member)
                            @php
                                $card = $member->membershipCards->first();
                                $statusMeta = $memberStatusMeta[$member->status] ?? ['key' => 'statusUnknown', 'label' => $member->status];
                            @endphp
                            <article class="library-member-card">
                                <div class="library-member-card-head">
                                        @if ($member->profile_photo_path)
                                            <img class="user-table-avatar user-table-avatar-img" src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
                                        @else
                                            <div class="user-table-avatar">{{ strtoupper(substr($member->full_name, 0, 1)) }}</div>
                                        @endif
                                    <div>
                                        <h5>{{ $member->full_name }}</h5>
                                        <span>{{ $member->member_code ?: 'N/A' }}</span>
                                    </div>
                                </div>
                                <div class="library-member-meta">
                                    <span><strong data-i18n="father">Father</strong>{{ $member->father_name ?: 'N/A' }}</span>
                                    <span><strong data-i18n="idTazkira">ID / Tazkira</strong>{{ $member->tazkira_number ?: 'N/A' }}</span>
                                    <span><strong data-i18n="phoneNumber">Phone number</strong>{{ $member->phone }}</span>
                                    <span><strong data-i18n="expiry">Expiry</strong>{{ $member->membership_expires_at?->format('Y-m-d') ?? ($card?->expires_at?->format('Y-m-d') ?? 'N/A') }}</span>
                                    <span><strong data-i18n="payment">Payment</strong><em data-i18n="{{ $member->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $member->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</em></span>
                                    <span><strong data-i18n="nextDue">Next due</strong>{{ $member->next_payment_due_at?->format('Y-m-d') ?? 'N/A' }}</span>
                                    <span><strong>Fee balance</strong>{{ number_format($member->monthlyFeeBalance()) }} AFN</span>
                                    <span><strong data-i18n="status">Status</strong><em data-i18n="{{ $statusMeta['key'] }}">{{ $statusMeta['label'] }}</em></span>
                                </div>
                                <div class="library-member-actions">
                                    <a class="btn btn-primary btn-sm" href="{{ route('library.members.show', $member) }}" data-i18n="profile">Profile</a>
                                    @if ($canWriteLibrary)
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.members.edit', $member) }}" data-i18n="edit">Edit</a>
                                    @endif
                                    @if ($card)
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $card) }}" data-i18n="printCard">Print card</a>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="library-member-empty" data-i18n="noLibraryMembersFound">No library members were found.</div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="bookInventory">Book inventory</h4>
                    <p class="card-description"><span data-i18n="availableCopies">Available copies</span>: {{ $availableCopyCount }}</p>
                    <div class="preview-list">
                        @forelse ($books as $book)
                            @php
                                $statusMeta = $bookStatusMeta[$book->status] ?? ['key' => 'statusUnknown', 'label' => $book->status];
                            @endphp
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>B</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $book->title }}</p>
                                    <p class="text-muted mb-0">
                                        @if ($book->author)
                                            {{ $book->author }}
                                        @else
                                            <span data-i18n="unknownAuthor">Unknown author</span>
                                        @endif
                                        · <span data-i18n="shelfCode">Shelf code</span>: {{ $book->shelf_code ?: 'N/A' }}
                                    </p>
                                    <p class="text-muted mb-0">{{ $book->available_copies }}/{{ $book->total_copies }} <span data-i18n="available">available</span> - {{ $book->copies_count ?? 0 }} physical copies - <span data-i18n="{{ $statusMeta['key'] }}">{{ $statusMeta['label'] }}</span></p>
                                </div>
                                @if ($canWriteLibrary)
                                    <div class="student-profile-actions">
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.books.edit', $book) }}" data-i18n="edit">Edit</a>
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.books.copy-labels', $book) }}">Labels</a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0" data-i18n="noBooksFound">No books have been registered yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card" id="recent-library-loans">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="recentLoans">Recent loans</h4>
                    <div class="preview-list">
                        @forelse ($loans as $loan)
                            @php
                                $statusMeta = $loanStatusMeta[$loan->status] ?? ['key' => 'statusUnknown', 'label' => $loan->status];
                                $isLateLoan = in_array($loan->status, ['borrowed', 'late'], true) && $loan->due_at && $loan->due_at->isPast();
                            @endphp
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon {{ $loan->status === 'returned' ? 'bg-success' : ($isLateLoan ? 'bg-danger' : 'bg-warning') }}"><span>L</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $loan->member?->full_name }} - {{ $loan->book?->title }}</p>
                                    <p class="text-muted mb-0">{{ $loan->borrowed_at?->format('Y-m-d') }} <span data-i18n="to">to</span> {{ $loan->due_at?->format('Y-m-d') ?? 'N/A' }} - Copy {{ $loan->copy?->copy_code ?: 'N/A' }} - <span data-i18n="{{ $statusMeta['key'] }}">{{ $isLateLoan ? 'Late' : $statusMeta['label'] }}</span></p>
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
                                <div class="shortcut-action pt-2 pt-sm-0">
                                    @if ($loan->member)
                                        <a class="btn btn-outline-secondary btn-sm mb-2" href="{{ route('library.members.show', $loan->member) }}" data-i18n="memberProfile">Member profile</a>
                                    @endif
                                    @if ($canWriteLibrary)
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.loans.edit', $loan) }}" data-i18n="edit">Edit</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0" data-i18n="noLoansFound">No loans have been registered yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canWriteLibrary)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const panels = Array.from(document.querySelectorAll('[data-library-panel]'));
                const triggers = Array.from(document.querySelectorAll('[data-library-panel-trigger]'));
                const emptyState = document.querySelector('[data-library-panel-empty]');
                const formArea = document.getElementById('library-action-forms');
                const defaultPanel = @json($hasMemberFilters ? 'library-members-panel' : null);

                function setPanel(panelId, shouldScroll) {
                    const activePanel = panels.find((panel) => panel.id === panelId);

                    panels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel === activePanel);
                    });

                    triggers.forEach((trigger) => {
                        const isActive = trigger.getAttribute('data-library-panel-trigger') === panelId;
                        trigger.classList.toggle('is-active', isActive);
                        trigger.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                    });

                    if (emptyState) {
                        emptyState.classList.toggle('is-hidden', Boolean(activePanel));
                    }

                    if (activePanel && shouldScroll) {
                        formArea?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }

                triggers.forEach((trigger) => {
                    trigger.addEventListener('click', function () {
                        setPanel(trigger.getAttribute('data-library-panel-trigger'), true);
                    });
                });

                document.querySelectorAll('[data-library-panel-close]').forEach((button) => {
                    button.addEventListener('click', function () {
                        panels.forEach((panel) => panel.classList.remove('is-active'));
                        triggers.forEach((trigger) => {
                            trigger.classList.remove('is-active');
                            trigger.setAttribute('aria-expanded', 'false');
                        });
                        emptyState?.classList.remove('is-hidden');
                    });
                });

                if (window.location.hash && document.querySelector(window.location.hash + '[data-library-panel]')) {
                    setPanel(window.location.hash.slice(1), false);
                } else if (defaultPanel) {
                    setPanel(defaultPanel, false);
                }
            });
        </script>
    @endif
@endsection
