@extends('admin.layout')

@section('title', 'Edit Loan - Fanous Admin')

@section('content')
    @php
        $loanStatusMeta = [
            'borrowed' => ['key' => 'loanBorrowed', 'label' => 'Borrowed'],
            'returned' => ['key' => 'loanReturned', 'label' => 'Returned'],
            'late' => ['key' => 'loanLate', 'label' => 'Late'],
            'lost' => ['key' => 'loanLost', 'label' => 'Lost'],
        ];
        $selectedStatus = old('status', $loan->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker" data-i18n="editLoan">Edit loan</span>
            <h1>{{ $loan->member?->full_name ?: __('Unknown member') }}</h1>
            <p>{{ $loan->book?->title ?: __('Unknown book') }}</p>
        </div>

        <div class="student-command-actions">
            <a class="btn btn-outline-light" href="{{ route('library.index') }}" data-i18n="back">Back</a>
            @if ($loan->member)
                <a class="btn btn-primary" href="{{ route('library.members.show', $loan->member) }}" data-i18n="memberProfile">Member profile</a>
            @endif
        </div>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('library.loans.update', $loan) }}">
        @csrf
        @method('PUT')

        <div class="student-form-layout">
            <main class="student-form-main">
                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">01</span>
                        <div>
                            <h2 data-i18n="loanDetails">Loan details</h2>
                            <p data-i18n="editLoanDescription">Update due date, return state, fine, and condition notes.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group"><label data-i18n="loanCode">Loan code</label><input class="form-control" name="loan_code" value="{{ old('loan_code', $loan->loan_code) }}"></div>
                        <div class="form-group"><label data-i18n="member">Member</label><input class="form-control" value="{{ $loan->member?->full_name }}" disabled></div>
                        <div class="form-group"><label data-i18n="book">Book</label><input class="form-control" value="{{ $loan->book?->title }}" disabled></div>
                        <div class="form-group"><label>Copy code</label><input class="form-control" value="{{ $loan->copy?->copy_code ?: 'N/A' }}" disabled></div>
                        <div class="form-group"><label data-i18n="borrowedAt">Borrowed at</label><input class="form-control" name="borrowed_at" type="date" value="{{ old('borrowed_at', $loan->borrowed_at?->format('Y-m-d')) }}" required></div>
                        <div class="form-group"><label data-i18n="dueAt">Due at</label><input class="form-control" name="due_at" type="date" value="{{ old('due_at', $loan->due_at?->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label data-i18n="conditionOut">Condition out</label><input class="form-control" name="condition_out" value="{{ old('condition_out', $loan->condition_out) }}"></div>
                        <div class="form-group"><label data-i18n="fineAmount">Fine amount</label><input class="form-control" name="fine_amount" type="number" min="0" value="{{ old('fine_amount', $loan->fine_amount) }}"></div>
                        <div class="form-group">
                            <label data-i18n="status">Status</label>
                            <select class="form-control" name="status">
                                @foreach ($loanStatusMeta as $value => $meta)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $loan->notes) }}</textarea></div>
                    </div>
                </section>
            </main>

            <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2 data-i18n="loanStatus">Loan status</h2>
                            <p>{{ $loan->borrowed_at?->format('Y-m-d') }} <span data-i18n="to">to</span> {{ $loan->due_at?->format('Y-m-d') ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary" data-i18n="{{ $loanStatusMeta[$selectedStatus]['key'] ?? 'statusUnknown' }}">{{ $loanStatusMeta[$selectedStatus]['label'] ?? $selectedStatus }}</span>
                        <strong>{{ $loan->book?->title ?: __('Unknown book') }}</strong>
                        <p>{{ $loan->member?->full_name ?: __('Unknown member') }}</p>
                    </div>

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit" data-i18n="saveChanges">Save changes</button>
                        <a class="btn btn-dark" href="{{ $loan->member ? route('library.members.show', $loan->member) : route('library.index') }}" data-i18n="cancel">Cancel</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
@endsection
