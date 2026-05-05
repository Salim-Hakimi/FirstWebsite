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
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1" data-i18n="editLoan">Edit loan</h3>
                            <p class="mb-0 text-white-50">{{ $loan->member?->full_name }} - {{ $loan->book?->title }}</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a class="btn btn-outline-light btn-rounded" href="{{ route('library.index') }}" data-i18n="back">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="loanDetails">Loan details</h4>
                    <form method="POST" action="{{ route('library.loans.update', $loan) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4 form-group"><label data-i18n="loanCode">Loan code</label><input class="form-control" name="loan_code" value="{{ old('loan_code', $loan->loan_code) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="member">Member</label><input class="form-control" value="{{ $loan->member?->full_name }}" disabled></div>
                            <div class="col-md-4 form-group"><label data-i18n="book">Book</label><input class="form-control" value="{{ $loan->book?->title }}" disabled></div>
                            <div class="col-md-4 form-group"><label data-i18n="borrowedAt">Borrowed at</label><input class="form-control" name="borrowed_at" type="date" value="{{ old('borrowed_at', $loan->borrowed_at?->format('Y-m-d')) }}" required></div>
                            <div class="col-md-4 form-group"><label data-i18n="dueAt">Due at</label><input class="form-control" name="due_at" type="date" value="{{ old('due_at', $loan->due_at?->format('Y-m-d')) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="conditionOut">Condition out</label><input class="form-control" name="condition_out" value="{{ old('condition_out', $loan->condition_out) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="fineAmount">Fine amount</label><input class="form-control" name="fine_amount" type="number" min="0" value="{{ old('fine_amount', $loan->fine_amount) }}"></div>
                            <div class="col-md-4 form-group">
                                <label data-i18n="status">Status</label>
                                <select class="form-control" name="status">
                                    @foreach ($loanStatusMeta as $value => $meta)
                                        <option value="{{ $value }}" @selected(old('status', $loan->status) === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 form-group"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $loan->notes) }}</textarea></div>
                            <div class="col-12">
                                <button class="btn btn-primary mr-2" type="submit" data-i18n="saveChanges">Save changes</button>
                                <a class="btn btn-dark" href="{{ route('library.index') }}" data-i18n="cancel">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
