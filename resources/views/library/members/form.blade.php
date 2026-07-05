@extends('admin.layout')

@section('title', 'ویرایش عضو کتابخانه - ادمین فانوس')

@section('content')
    @php
        $latestLibraryCard = $libraryCard ?? null;
        $memberStatusMeta = [
            'active' => ['key' => 'statusActive', 'label' => 'فعال'],
            'suspended' => ['key' => 'statusSuspended', 'label' => 'مسدود'],
            'left' => ['key' => 'statusLeft', 'label' => 'خارج شده'],
        ];
        $selectedStatus = old('status', $member->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker" data-i18n="editMember">ویرایش عضو</span>
            <h1>{{ $member->full_name }}</h1>
            <p data-i18n="editMemberDescription">جزئیات عضو، وضعیت پرداخت، تاریخ‌های اعتبار و معلومات کارت کتابخانه را ویرایش کنید.</p>
        </div>

        <div class="student-command-actions">
            <a class="btn btn-outline-light" href="{{ route('library.members.show', $member) }}" data-i18n="back">برگشت</a>
            <a class="btn btn-primary" href="{{ route('library.index') }}" data-i18n="library">کتابخانه</a>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="student-form-layout">
        <main class="student-form-main">
            <form id="library-member-form" method="POST" action="{{ route('library.members.update', $member) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">01</span>
                        <div>
                            <h2 data-i18n="memberDetails">جزئیات عضو</h2>
                            <p data-i18n="memberDetailsDescription">معلومات هویتی، تحصیلی، تماس و ثبت.</p>
                        </div>
                    </div>

                    <div class="student-photo-uploader student-form-photo">
                        @if ($member->profile_photo_path)
                            <img src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
                        @else
                            <span>{{ mb_substr($member->full_name ?: 'ع', 0, 1) }}</span>
                        @endif
                        <div class="flex-grow-1">
                            <label data-i18n="profilePhoto">عکس پروفایل</label>
                            <input class="form-control" name="profile_photo" type="file" accept="image/*">
                            @if ($member->profile_photo_path)
                                <label class="student-checkbox-line mt-2">
                                    <input name="remove_profile_photo" type="checkbox" value="1">
                                    <span data-i18n="removeCurrentPhoto">حذف عکس فعلی</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group"><label data-i18n="memberCode">کد عضو</label><input class="form-control" name="member_code" value="{{ old('member_code', $member->member_code) }}"></div>
                        <div class="form-group"><label data-i18n="fullName">نام مکمل</label><input class="form-control" name="full_name" value="{{ old('full_name', $member->full_name) }}" required></div>
                        <div class="form-group"><label data-i18n="fatherName">نام پدر</label><input class="form-control" name="father_name" value="{{ old('father_name', $member->father_name) }}" required></div>
                        <div class="form-group"><label data-i18n="phoneNumber">واتساپ</label><input class="form-control" name="phone" value="{{ old('phone', $member->phone) }}" required></div>
                        <div class="form-group"><label data-i18n="emailAddress">ایمیل</label><input class="form-control" name="email" type="email" value="{{ old('email', $member->email) }}"></div>
                        <div class="form-group"><label data-i18n="idTazkira">آی‌دی / تذکره</label><input class="form-control" name="tazkira_number" value="{{ old('tazkira_number', $member->tazkira_number) }}"></div>
                        <div class="form-group"><label data-i18n="educationPlace">محل تحصیل</label><input class="form-control" name="education_place" value="{{ old('education_place', $member->education_place) }}"></div>
                        <div class="form-group"><label data-i18n="departmentGrade">دیپارتمنت / صنف</label><input class="form-control" name="department_or_grade" value="{{ old('department_or_grade', $member->department_or_grade) }}"></div>
                        <div class="form-group full"><label data-i18n="address">آدرس</label><input class="form-control" name="address" value="{{ old('address', $member->address) }}"></div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2 data-i18n="cardsAndPayments">کارت‌ها و پرداخت‌ها</h2>
                            <p data-i18n="cardsAndPaymentsDescription">وضعیت کارت کتابخانه و پیگیری فیس ماهانه.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group"><label data-i18n="monthlyFee">فیس ماهانه</label><input class="form-control" name="membership_fee" type="number" min="0" value="{{ old('membership_fee', $member->membership_fee) }}"></div>
                        <div class="form-group"><label>قیمت کارت</label><input class="form-control" name="card_fee_amount" type="number" min="0" value="{{ old('card_fee_amount', $member->card_fee_amount ?? 50) }}"></div>
                        <div class="form-group">
                            <label data-i18n="paymentStatus">وضعیت پرداخت</label>
                            <select class="form-control" name="payment_status">
                                <option value="unpaid" @selected(old('payment_status', $member->payment_status) === 'unpaid') data-i18n="unpaid">پرداخت نشده</option>
                                <option value="paid" @selected(old('payment_status', $member->payment_status) === 'paid') data-i18n="paid">پرداخت شده</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label data-i18n="status">وضعیت</label>
                            <select class="form-control" name="status">
                                @foreach ($memberStatusMeta as $value => $meta)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label data-i18n="joinedAt">تاریخ ثبت</label><input class="form-control" name="joined_at" type="date" value="{{ old('joined_at', $member->joined_at?->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label>تاریخ خروج</label><input class="form-control @error('left_at') is-invalid @enderror" name="left_at" type="date" value="{{ old('left_at', $member->left_at?->format('Y-m-d')) }}">@error('left_at') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group"><label data-i18n="membershipExpiry">اعتبار عضویت</label><input class="form-control" name="membership_expires_at" type="date" value="{{ old('membership_expires_at', $member->membership_expires_at?->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label data-i18n="nextDue">موعد بعدی</label><input class="form-control" name="next_payment_due_at" type="date" value="{{ old('next_payment_due_at', $member->next_payment_due_at?->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label>جریمه روزانه دیرکرد</label><input class="form-control" name="monthly_fee_daily_fine" type="number" min="0" value="{{ old('monthly_fee_daily_fine', $member->monthly_fee_daily_fine ?? 20) }}"></div>
                        <div class="form-group"><label>جریمه فعلی ماهانه</label><input class="form-control" name="monthly_fee_fine_amount" type="number" min="0" value="{{ old('monthly_fee_fine_amount', $member->monthly_fee_fine_amount ?? 0) }}"></div>
                        <div class="form-group full"><label data-i18n="notes">یادداشت</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $member->notes) }}</textarea></div>
                    </div>
                </section>
            </form>
        </main>

        <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">03</span>
                        <div>
                            <h2 data-i18n="currentCard">کارت فعلی</h2>
                            <p>{{ $latestLibraryCard?->expires_at?->format('Y-m-d') ?? 'ثبت نشده' }} - <span data-i18n="{{ $latestLibraryCard?->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $latestLibraryCard?->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}</span></p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary" data-i18n="{{ $memberStatusMeta[$selectedStatus]['key'] ?? 'statusUnknown' }}">{{ $memberStatusMeta[$selectedStatus]['label'] ?? $selectedStatus }}</span>
                        <strong>{{ $member->full_name }}</strong>
                        <p>{{ $member->member_code ?: 'ثبت نشده' }}</p>
                    </div>

                    <div class="student-card-summary">
                        <span class="student-timeline-icon">ک</span>
                        <div>
                            <strong>{{ $latestLibraryCard ? $latestLibraryCard->card_number : __('کارت کتابخانه ندارد') }}</strong>
                            <p>{{ $latestLibraryCard?->expires_at?->format('Y-m-d') ?? __('صدور کارت جدید') }}</p>
                        </div>
                    </div>

                    <form id="library-card-form" method="POST" action="{{ route('library.members.card.issue', $member) }}">
                        @csrf
                    </form>

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit" form="library-member-form" data-i18n="saveChanges">فقط ذخیره معلومات</button>
                        <button class="btn btn-outline-primary" type="submit" form="library-card-form" data-i18n="{{ $latestLibraryCard ? 'renewCard' : 'issueNewCard' }}">{{ $latestLibraryCard ? 'ذخیره و تمدید کارت' : 'ذخیره و صدور کارت جدید' }}</button>
                        @if ($latestLibraryCard)
                            <a class="btn btn-outline-secondary" href="{{ route('membership-cards.print', $latestLibraryCard) }}" data-i18n="printCard">چاپ کارت فعلی</a>
                        @endif
                        <a class="btn btn-dark" href="{{ route('library.members.show', $member) }}" data-i18n="cancel">لغو</a>
                    </div>
                </section>
        </aside>
    </div>
@endsection
