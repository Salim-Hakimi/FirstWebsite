@extends('admin.layout')

@section('title', 'پروفایل عضو کتابخانه - ادمین فانوس')

@section('content')
    @php
        use App\Support\Locale;

        $latestCard = $member->membershipCards->first();
        $memberStatusMeta = [
            'active' => ['key' => 'statusActive', 'label' => 'فعال'],
            'suspended' => ['key' => 'statusSuspended', 'label' => 'مسدود'],
            'left' => ['key' => 'statusLeft', 'label' => 'خارج شده'],
        ];
        $loanStatusMeta = [
            'borrowed' => ['key' => 'loanBorrowed', 'label' => 'در امانت'],
            'returned' => ['key' => 'loanReturned', 'label' => 'برگشت شده'],
            'lost' => ['key' => 'loanLost', 'label' => 'گم شده'],
            'late' => ['key' => 'loanLate', 'label' => 'ناوقت'],
        ];
        $statusMeta = $memberStatusMeta[$member->status] ?? ['key' => 'statusUnknown', 'label' => $member->status];
        $monthlyFine = $monthlyFeeFine ?? max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
        $monthlyBalance = $monthlyFeeBalance ?? $member->monthlyFeeBalance();
        $reminderText = $monthlyFeeReminderText ?? '';
        $whatsappDigits = preg_replace('/\D+/', '', $member->phone ?? '');
        if (str_starts_with($whatsappDigits, '0')) {
            $whatsappDigits = '93'.substr($whatsappDigits, 1);
        }
        $monthlyBillRows = collect($monthlyBillRows ?? []);
        $openBillRow = $monthlyBillRows->firstWhere('is_open', true) ?? $monthlyBillRows->first();
        $hasOpenMonthBill = (bool) ($openBillRow['paid'] ?? false);
        $openMonthLabel = $openBillRow['month_label'] ?? Locale::month($openBillingMonth ?? today());
        $openMonthKey = $openBillRow['month_key'] ?? ($openBillingMonth ?? today())->format('Y-m');
    @endphp

    <section class="student-profile-hero">
        <div class="student-profile-identity">
            @if ($member->profile_photo_path)
                <img class="student-profile-photo" src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
            @else
                <div class="student-profile-photo student-profile-photo-empty">{{ mb_substr($member->full_name ?: 'ع', 0, 1) }}</div>
            @endif

            <div>
                <span class="badge badge-outline-primary" data-i18n="{{ $statusMeta['key'] }}">{{ $statusMeta['label'] }}</span>
                <h1>{{ $member->full_name }}</h1>
                <p>{{ $member->member_code ?: 'ثبت نشده' }} · {{ $member->education_place ?: 'محل تحصیل ثبت نشده' }}</p>
                <div class="student-profile-actions">
                    @if ($canWriteLibrary)
                        <a class="btn btn-primary btn-sm" href="{{ route('library.members.edit', $member) }}" data-fanous-page-modal data-modal-title="ویرایش عضو کتابخانه">ویرایش عضو</a>
                    @endif
                    @if ($latestCard)
                        @if ($latestCard->card_printed && $latestCard->expires_at?->isFuture())
                            <span class="btn btn-outline-secondary btn-sm disabled">کارت قبلاً چاپ شده</span>
                        @else
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}">چاپ کارت</a>
                        @endif
                    @endif
                    <a class="btn btn-dark btn-sm" href="{{ route('library.index') }}">برگشت</a>
                </div>
            </div>
        </div>

        <div class="student-profile-snapshot">
            <span><strong>{{ $member->membership_expires_at ? Locale::date($member->membership_expires_at) : 'ثبت نشده' }}</strong><span>اعتبار عضویت</span></span>
            <span><strong>{{ Locale::number($activeLoanCount) }}</strong><span>امانت‌های فعال</span></span>
            <span><strong>{{ $member->last_paid_at ? Locale::date($member->last_paid_at) : 'ثبت نشده' }}</strong><span>پرداخت اخیر</span></span>
            <span><strong>{{ $member->next_payment_due_at ? Locale::date($member->next_payment_due_at) : 'ثبت نشده' }}</strong><span>موعد پرداخت بعدی</span></span>
        </div>
    </section>

    @unless ($canWriteLibrary)
        <section class="student-workspace-panel">
            <div class="user-access-note">
                <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>i</span></div></div>
                <p class="text-muted mb-0">حالت مشاهده فعال است. مدیر می‌تواند معلومات کتابخانه را ببیند، اما ثبت، ویرایش، امانت، برگشت و کارت مخصوص حساب کتابدار است.</p>
            </div>
        </section>
    @endunless

    <section class="student-profile-layout">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">جزئیات عضو</span>
                    <h2>{{ $member->full_name }}</h2>
                    <p>معلومات هویتی، تحصیلی، تماس و ثبت.</p>
                </div>
            </div>

            <div class="student-detail-grid">
                <div><span>نام پدر</span><strong>{{ $member->father_name ?: 'ثبت نشده' }}</strong></div>
                <div><span>واتساپ</span><strong>{{ $member->phone ?: 'ثبت نشده' }}</strong></div>
                <div><span>ایمیل</span><strong>{{ $member->email ?: 'ایمیل ثبت نشده' }}</strong></div>
                <div><span>تذکره / ID</span><strong>{{ $member->tazkira_number ?: 'ثبت نشده' }}</strong></div>
                <div><span>محل تحصیل</span><strong>{{ $member->education_place ?: 'ثبت نشده' }}</strong></div>
                <div><span>رشته / صنف</span><strong>{{ $member->department_or_grade ?: 'ثبت نشده' }}</strong></div>
                <div><span>آدرس</span><strong>{{ $member->address ?: 'ثبت نشده' }}</strong></div>
                <div><span>تاریخ ثبت</span><strong>{{ $member->joined_at ? Locale::date($member->joined_at) : 'ثبت نشده' }}</strong></div>
                <div><span>تاریخ خروج</span><strong>{{ $member->left_at ? Locale::date($member->left_at) : 'ثبت نشده' }}</strong></div>
                <div><span>ثبت‌کننده</span><strong>{{ $member->registeredBy?->name ?: 'نامشخص' }}</strong></div>
                <div><span>یادداشت</span><strong>{{ $member->notes ?: 'ثبت نشده' }}</strong></div>
            </div>
        </div>

        <aside class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">پرداخت و کارت</span>
                    <h2>بیل ماهانه و کارت شش‌ماهه</h2>
                    <p>بیل برای پرداخت ماهانه است؛ کارت فقط سند هویت و اعتبار عضویت شش‌ماهه می‌باشد.</p>
                </div>
                @if ($canWriteLibrary)
                    <form method="POST" action="{{ route('library.members.card.issue', $member) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm" type="submit">صدور کارت تازه</button>
                    </form>
                @endif
            </div>

            <div class="student-timeline-list">
                @if ($canWriteLibrary)
                    <form method="POST" action="{{ route('library.members.monthly-payment.store', $member) }}" class="student-timeline-item">
                        @csrf
                        <input type="hidden" name="billing_month" value="{{ $openMonthKey }}">
                        <span class="student-timeline-icon">پ</span>
                        <div>
                            <strong>پرداخت {{ $openMonthLabel }}</strong>
                            <p>دریافت {{ Locale::money((int) ($openBillRow['amount'] ?? $monthlyBalance)) }}، صدور رسید کوچک و انتقال موعد بعدی به ماه بعد.</p>
                        </div>
                        <div class="student-timeline-actions">
                            @if ($hasOpenMonthBill)
                                <a class="btn btn-primary btn-sm" href="{{ route('library.members.monthly-payment.receipt', ['member' => $member, 'billing_month' => $openMonthKey]) }}">چاپ بیل {{ $openMonthLabel }}</a>
                            @else
                                <button class="btn btn-primary btn-sm" type="submit">ثبت پرداخت {{ $openMonthLabel }}</button>
                            @endif
                        </div>
                    </form>
                @endif

                <div class="student-timeline-item">
                        <span class="student-timeline-icon">ف</span>
                        <div>
                            <strong>مبلغ قابل پرداخت</strong>
                            <p>{{ Locale::money($monthlyBalance) }} شامل فیس ماهانه و جریمه دیرکرد</p>
                        </div>
                </div>

                <div class="student-timeline-item">
                        <span class="student-timeline-icon">م</span>
                        <div>
                            <strong>موعد بیل بعدی</strong>
                            <p>{{ $member->next_payment_due_at ? Locale::date($member->next_payment_due_at) : 'ثبت نشده' }}</p>
                        </div>
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">ب</span>
                    <div>
                        <strong>دفتر بیل‌های ماهانه</strong>
                        <p>هر ماه جدا ثبت می‌شود و از همین‌جا قابل چاپ است.</p>
                        <div class="student-detail-grid mt-3">
                            @forelse ($monthlyBillRows as $billRow)
                                <div>
                                    <span>{{ $billRow['month_label'] }}</span>
                                    <strong>{{ Locale::money((int) $billRow['amount']) }}</strong>
                                    <small class="d-block mt-1">{{ $billRow['status_label'] }}</small>
                                    @if ($billRow['receipt_url'])
                                        <a class="btn btn-outline-secondary btn-sm mt-2" href="{{ $billRow['receipt_url'] }}">چاپ بیل</a>
                                    @endif
                                </div>
                            @empty
                                <div><span>بیل ماهانه</span><strong>هنوز بیلی ساخته نشده است</strong></div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">ج</span>
                    <div>
                        <strong>جریمه روزانه دیرکرد</strong>
                        <p>{{ Locale::money((int) ($member->monthly_fee_daily_fine ?? 20)) }} در روز بعد از موعد - جریمه فعلی {{ Locale::money($monthlyFine) }}</p>
                    </div>
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">و</span>
                    <div>
                        <strong>یادآوری واتساپ</strong>
                        <p>{{ $reminderText }}</p>
                    </div>
                    @if ($whatsappDigits)
                        <a class="btn btn-outline-secondary btn-sm" href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode($reminderText) }}" target="_blank" rel="noopener">ارسال</a>
                    @endif
                </div>

                @forelse ($member->membershipCards as $card)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">ک</span>
                        <div>
                            <strong>{{ $card->card_number }}</strong>
                            <p>{{ $card->issued_at ? Locale::date($card->issued_at) : 'ثبت نشده' }} تا {{ $card->expires_at ? Locale::date($card->expires_at) : 'ثبت نشده' }} · کارت هویت شش‌ماهه</p>
                        </div>
                        @if ($card->card_printed && $card->expires_at?->isFuture())
                            <span class="btn btn-outline-secondary btn-sm disabled">چاپ شده</span>
                        @else
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $card) }}">چاپ کارت</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">برای این عضو هنوز کارت کتابخانه صادر نشده است.</div>
                @endforelse
            </div>
        </aside>
    </section>

    <section class="student-workspace-panel">
        <div class="student-panel-head">
            <div>
                <span class="student-panel-label">تاریخچه امانت کتاب</span>
                <h2>امانت‌های اخیر</h2>
                <p>{{ Locale::number($returnedLoanCount) }} امانت برگشت‌شده · {{ Locale::number($activeLoanCount) }} امانت فعال</p>
            </div>
        </div>

        <div class="student-timeline-list">
            @forelse ($member->loans->sortByDesc('borrowed_at') as $loan)
                @php
                    $loanStatus = $loanStatusMeta[$loan->status] ?? ['key' => 'statusUnknown', 'label' => $loan->status];
                    $isLateLoan = in_array($loan->status, ['borrowed', 'late'], true) && $loan->due_at && $loan->due_at->isPast();
                @endphp
                <div class="student-timeline-item library-loan-row">
                    <span class="student-timeline-icon">{{ $isLateLoan ? '!' : 'ا' }}</span>
                    <div>
                        <strong>
                            @if ($loan->book)
                                {{ $loan->book->title }}
                            @else
                                <span>کتاب حذف شده</span>
                            @endif
                        </strong>
                        <p>
                            کد امانت: {{ $loan->loan_code ?: 'ثبت نشده' }} ·
                            نسخه: {{ $loan->copy?->copy_code ?: 'ثبت نشده' }} ·
                            {{ $loan->borrowed_at ? Locale::date($loan->borrowed_at) : 'ثبت نشده' }} تا {{ $loan->due_at ? Locale::date($loan->due_at) : 'ثبت نشده' }} ·
                            {{ $isLateLoan ? 'ناوقت' : $loanStatus['label'] }}
                        </p>

                        @if ($canWriteLibrary && in_array($loan->status, ['borrowed', 'late'], true))
                            <form method="POST" action="{{ route('library.loans.return', $loan) }}" class="library-return-form mt-3">
                                @csrf
                                @method('PUT')
                                <input class="form-control @error('returned_at') is-invalid @enderror" name="returned_at" type="date" value="{{ old('returned_at', now()->format('Y-m-d')) }}" required>
                                @error('returned_at') <span class="text-danger small">{{ $message }}</span> @enderror
                                <input class="form-control @error('fine_amount') is-invalid @enderror" name="fine_amount" type="number" min="0" value="{{ old('fine_amount', 0) }}" placeholder="مبلغ جریمه">
                                @error('fine_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                                <select class="form-control" name="return_status">
                                    <option value="available">سالم / قابل امانت</option>
                                    <option value="damaged">خراب</option>
                                    <option value="lost">گم‌شده</option>
                                </select>
                                <input class="form-control" name="condition_in" placeholder="وضعیت هنگام برگشت">
                                <button class="btn btn-primary" type="submit" data-i18n="markReturned">ثبت برگشت</button>
                            </form>
                        @endif
                    </div>

                    <div class="student-profile-actions">
                        @if ((int) $loan->fine_amount > 0)
                            <span class="badge badge-outline-warning">{{ Locale::money((int) $loan->fine_amount) }}</span>
                        @endif
                        @if ($canWriteLibrary)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.loans.edit', $loan) }}" data-fanous-page-modal data-modal-title="ویرایش امانت کتاب">ویرایش</a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="student-directory-empty">برای این عضو هنوز امانتی ثبت نشده است.</div>
            @endforelse
        </div>
    </section>
@endsection
