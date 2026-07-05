@extends('admin.layout')

@section('title', 'ویرایش امانت - ادمین فانوس')

@section('content')
    @php
        $loanStatusMeta = [
            'borrowed' => ['key' => 'loanBorrowed', 'label' => 'امانت داده‌شده'],
            'returned' => ['key' => 'loanReturned', 'label' => 'برگشت شده'],
            'late' => ['key' => 'loanLate', 'label' => 'ناوقت'],
            'lost' => ['key' => 'loanLost', 'label' => 'گم‌شده'],
        ];
        $selectedStatus = old('status', $loan->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker" data-i18n="editLoan">ویرایش امانت</span>
            <h1>{{ $loan->member?->full_name ?: __('Unknown member') }}</h1>
            <p>{{ $loan->book?->title ?: __('Unknown book') }}</p>
        </div>

        <div class="student-command-actions">
            <a class="btn btn-outline-light" href="{{ route('library.index') }}" data-i18n="back">برگشت</a>
            @if ($loan->member)
                <a class="btn btn-primary" href="{{ route('library.members.show', $loan->member) }}" data-i18n="memberProfile">پروفایل عضو</a>
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
                            <h2 data-i18n="loanDetails">جزئیات امانت</h2>
                            <p data-i18n="editLoanDescription">تاریخ برگشت، وضعیت برگشت، جریمه و یادداشت‌های حالت کتاب را به‌روزرسانی کنید.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group"><label data-i18n="loanCode">کد امانت</label><input class="form-control @error('loan_code') is-invalid @enderror" name="loan_code" value="{{ old('loan_code', $loan->loan_code) }}">@error('loan_code') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group"><label data-i18n="member">عضو</label><input class="form-control" value="{{ $loan->member?->full_name }}" disabled></div>
                        <div class="form-group"><label data-i18n="book">کتاب</label><input class="form-control" value="{{ $loan->book?->title }}" disabled></div>
                        <div class="form-group"><label>کد نسخه</label><input class="form-control" value="{{ $loan->copy?->copy_code ?: 'ثبت نشده' }}" disabled></div>
                        <div class="form-group"><label data-i18n="borrowedAt">تاریخ امانت</label><input class="form-control @error('borrowed_at') is-invalid @enderror" name="borrowed_at" type="date" value="{{ old('borrowed_at', $loan->borrowed_at?->format('Y-m-d')) }}" required>@error('borrowed_at') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group"><label data-i18n="dueAt">موعد برگشت</label><input class="form-control @error('due_at') is-invalid @enderror" name="due_at" type="date" value="{{ old('due_at', $loan->due_at?->format('Y-m-d')) }}">@error('due_at') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group"><label data-i18n="conditionOut">حالت هنگام خروج</label><input class="form-control" name="condition_out" value="{{ old('condition_out', $loan->condition_out) }}"></div>
                        <div class="form-group"><label data-i18n="fineAmount">مبلغ جریمه</label><input class="form-control @error('fine_amount') is-invalid @enderror" name="fine_amount" type="number" min="0" value="{{ old('fine_amount', $loan->fine_amount) }}">@error('fine_amount') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group">
                            <label data-i18n="status">وضعیت</label>
                            <select class="form-control @error('status') is-invalid @enderror" name="status">
                                @foreach ($loanStatusMeta as $value => $meta)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group full"><label data-i18n="notes">یادداشت</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $loan->notes) }}</textarea></div>
                    </div>
                </section>
            </main>

            <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2 data-i18n="loanStatus">وضعیت امانت</h2>
                            <p>{{ $loan->borrowed_at?->format('Y-m-d') }} <span data-i18n="to">تا</span> {{ $loan->due_at?->format('Y-m-d') ?? 'ثبت نشده' }}</p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary" data-i18n="{{ $loanStatusMeta[$selectedStatus]['key'] ?? 'statusUnknown' }}">{{ $loanStatusMeta[$selectedStatus]['label'] ?? $selectedStatus }}</span>
                        <strong>{{ $loan->book?->title ?: __('Unknown book') }}</strong>
                        <p>{{ $loan->member?->full_name ?: __('Unknown member') }}</p>
                    </div>

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit" data-i18n="saveChanges">ذخیره تغییرات</button>
                        <a class="btn btn-dark" href="{{ $loan->member ? route('library.members.show', $loan->member) : route('library.index') }}" data-i18n="cancel">لغو</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
@endsection
