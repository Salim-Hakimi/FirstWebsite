@extends('admin.layout')

@section('title', 'Library fee reminders - Fanous Admin')

@section('content')
    @php
        $statusOptions = [
            'due_soon' => 'Due soon',
            'overdue' => 'Overdue',
            'all' => 'All unpaid',
        ];
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Monthly fee reminders</h3>
                            <p class="mb-0 text-white-50">Follow up library students before their monthly fee expires and after late fines start.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a class="btn btn-outline-light btn-rounded" href="{{ route('library.index') }}">Library</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ $dueSoonCount }}</h3><h6 class="text-muted font-weight-normal">Need reminder within 3 days</h6></div></div>
        </div>
        <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ $overdueCount }}</h3><h6 class="text-muted font-weight-normal">Overdue monthly fees</h6></div></div>
        </div>
        <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">20 AFN</h3><h6 class="text-muted font-weight-normal">Default daily late fine</h6></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4">
                        <div>
                            <h4 class="card-title mb-1">Reminder queue</h4>
                            <p class="text-muted mb-0">Open WhatsApp, send the prepared message, then mark it as sent.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('library.fee-reminders.index') }}" class="library-filter-row mb-4">
                        <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, phone, father, or member code">
                        <select class="form-control" name="status">
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary" type="submit">Search</button>
                        <a class="btn btn-dark" href="{{ route('library.fee-reminders.index') }}">Clear</a>
                    </form>

                    <div class="library-member-grid">
                        @forelse ($members as $member)
                            @php
                                $fine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
                                $balance = (int) $member->membership_fee + $fine;
                                $isOverdue = $member->next_payment_due_at && $member->next_payment_due_at->isPast();
                                $dueDate = $member->next_payment_due_at?->format('Y-m-d') ?: 'N/A';
                                $message = $isOverdue
                                    ? "سلام {$member->full_name} عزیز، تاریخ پرداخت فیس ماهانه کتاب‌خانه شما گذشته است. مبلغ قابل پرداخت فعلی {$balance} افغانی است که شامل {$fine} افغانی جریمه می‌باشد."
                                    : "سلام {$member->full_name} عزیز، فیس ماهانه کتاب‌خانه شما تا تاریخ {$dueDate} باید پرداخت شود. در صورت تاخیر، روزانه {$member->monthly_fee_daily_fine} افغانی جریمه می‌شود.";
                                $whatsappDigits = preg_replace('/\D+/', '', $member->phone ?? '');
                                if (str_starts_with($whatsappDigits, '0')) {
                                    $whatsappDigits = '93'.substr($whatsappDigits, 1);
                                }
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
                                        <span>{{ $member->member_code ?: 'No code' }} · {{ $isOverdue ? 'Overdue' : 'Reminder' }}</span>
                                    </div>
                                </div>
                                <div class="library-member-meta">
                                    <span><strong>Phone</strong>{{ $member->phone }}</span>
                                    <span><strong>Next due</strong>{{ $dueDate }}</span>
                                    <span><strong>Monthly fee</strong>{{ number_format((int) $member->membership_fee) }} AFN</span>
                                    <span><strong>Fine</strong>{{ number_format($fine) }} AFN</span>
                                    <span><strong>Balance</strong>{{ number_format($balance) }} AFN</span>
                                    <span><strong>Last reminder</strong>{{ $member->last_fee_reminder_at?->format('Y-m-d') ?? 'Not sent' }}</span>
                                </div>
                                <div class="student-timeline-item mb-3">
                                    <strong>Prepared message</strong>
                                    <p class="mb-0">{{ $message }}</p>
                                </div>
                                <div class="library-member-actions">
                                    @if ($whatsappDigits)
                                        <a class="btn btn-success btn-sm" href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode($message) }}" target="_blank" rel="noopener">WhatsApp</a>
                                    @endif
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.members.show', $member) }}">Profile</a>
                                    @if ($canWriteLibrary)
                                        <form method="POST" action="{{ route('library.members.fee-reminder.store', $member) }}">
                                            @csrf
                                            <button class="btn btn-outline-primary btn-sm" type="submit">Mark sent</button>
                                        </form>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="library-member-empty">No members need a monthly fee reminder for this filter.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
