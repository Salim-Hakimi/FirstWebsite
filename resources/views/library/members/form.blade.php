@extends('admin.layout')

@section('title', 'Edit Library Member - Fanous Admin')

@section('content')
    @php
        $latestLibraryCard = $libraryCard ?? null;
        $memberStatusMeta = [
            'active' => ['key' => 'statusActive', 'label' => 'Active'],
            'suspended' => ['key' => 'statusSuspended', 'label' => 'Suspended'],
            'left' => ['key' => 'statusLeft', 'label' => 'Left'],
        ];
        $selectedStatus = old('status', $member->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker" data-i18n="editMember">Edit member</span>
            <h1>{{ $member->full_name }}</h1>
            <p data-i18n="editMemberDescription">Update member details, payment status, expiry dates, and library card information.</p>
        </div>

        <div class="student-command-actions">
            <a class="btn btn-outline-light" href="{{ route('library.members.show', $member) }}" data-i18n="back">Back</a>
            <a class="btn btn-primary" href="{{ route('library.index') }}" data-i18n="library">Library</a>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form id="library-member-form" method="POST" action="{{ route('library.members.update', $member) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="student-form-layout">
            <main class="student-form-main">
                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">01</span>
                        <div>
                            <h2 data-i18n="memberDetails">Member details</h2>
                            <p data-i18n="memberDetailsDescription">Identity, education, contact, and registration information.</p>
                        </div>
                    </div>

                    <div class="student-photo-uploader student-form-photo">
                        @if ($member->profile_photo_path)
                            <img src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
                        @else
                            <span>{{ strtoupper(substr($member->full_name ?: 'M', 0, 1)) }}</span>
                        @endif
                        <div class="flex-grow-1">
                            <label data-i18n="profilePhoto">Profile photo</label>
                            <input class="form-control" name="profile_photo" type="file" accept="image/*">
                            @if ($member->profile_photo_path)
                                <label class="student-checkbox-line mt-2">
                                    <input name="remove_profile_photo" type="checkbox" value="1">
                                    <span data-i18n="removeCurrentPhoto">Remove current photo</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group"><label data-i18n="memberCode">Member code</label><input class="form-control" name="member_code" value="{{ old('member_code', $member->member_code) }}"></div>
                        <div class="form-group"><label data-i18n="fullName">Full name</label><input class="form-control" name="full_name" value="{{ old('full_name', $member->full_name) }}" required></div>
                        <div class="form-group"><label data-i18n="fatherName">Father name</label><input class="form-control" name="father_name" value="{{ old('father_name', $member->father_name) }}" required></div>
                        <div class="form-group"><label data-i18n="phoneNumber">Phone number</label><input class="form-control" name="phone" value="{{ old('phone', $member->phone) }}" required></div>
                        <div class="form-group"><label data-i18n="emailAddress">Email address</label><input class="form-control" name="email" type="email" value="{{ old('email', $member->email) }}"></div>
                        <div class="form-group"><label data-i18n="idTazkira">ID / Tazkira</label><input class="form-control" name="tazkira_number" value="{{ old('tazkira_number', $member->tazkira_number) }}"></div>
                        <div class="form-group"><label data-i18n="educationPlace">Education place</label><input class="form-control" name="education_place" value="{{ old('education_place', $member->education_place) }}"></div>
                        <div class="form-group"><label data-i18n="departmentGrade">Department / grade</label><input class="form-control" name="department_or_grade" value="{{ old('department_or_grade', $member->department_or_grade) }}"></div>
                        <div class="form-group full"><label data-i18n="address">Address</label><input class="form-control" name="address" value="{{ old('address', $member->address) }}"></div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2 data-i18n="cardsAndPayments">Cards and payments</h2>
                            <p data-i18n="cardsAndPaymentsDescription">Library card status and monthly fee tracking.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group"><label data-i18n="monthlyFee">Monthly fee</label><input class="form-control" name="membership_fee" type="number" min="0" value="{{ old('membership_fee', $member->membership_fee) }}"></div>
                        <div class="form-group">
                            <label data-i18n="paymentStatus">Payment status</label>
                            <select class="form-control" name="payment_status">
                                <option value="unpaid" @selected(old('payment_status', $member->payment_status) === 'unpaid') data-i18n="unpaid">Unpaid</option>
                                <option value="paid" @selected(old('payment_status', $member->payment_status) === 'paid') data-i18n="paid">Paid</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label data-i18n="status">Status</label>
                            <select class="form-control" name="status">
                                @foreach ($memberStatusMeta as $value => $meta)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label data-i18n="joinedAt">Joined at</label><input class="form-control" name="joined_at" type="date" value="{{ old('joined_at', $member->joined_at?->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label data-i18n="membershipExpiry">Membership expiry</label><input class="form-control" name="membership_expires_at" type="date" value="{{ old('membership_expires_at', $member->membership_expires_at?->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label data-i18n="nextDue">Next due</label><input class="form-control" name="next_payment_due_at" type="date" value="{{ old('next_payment_due_at', $member->next_payment_due_at?->format('Y-m-d')) }}"></div>
                        <div class="form-group"><label>Daily late fine</label><input class="form-control" name="monthly_fee_daily_fine" type="number" min="0" value="{{ old('monthly_fee_daily_fine', $member->monthly_fee_daily_fine ?? 20) }}"></div>
                        <div class="form-group"><label>Current monthly fine</label><input class="form-control" name="monthly_fee_fine_amount" type="number" min="0" value="{{ old('monthly_fee_fine_amount', $member->monthly_fee_fine_amount ?? 0) }}"></div>
                        <div class="form-group full"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $member->notes) }}</textarea></div>
                    </div>
                </section>
            </main>

            <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">03</span>
                        <div>
                            <h2 data-i18n="currentCard">Current card</h2>
                            <p>{{ $latestLibraryCard?->expires_at?->format('Y-m-d') ?? 'N/A' }} · <span data-i18n="{{ $latestLibraryCard?->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $latestLibraryCard?->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</span></p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary" data-i18n="{{ $memberStatusMeta[$selectedStatus]['key'] ?? 'statusUnknown' }}">{{ $memberStatusMeta[$selectedStatus]['label'] ?? $selectedStatus }}</span>
                        <strong>{{ $member->full_name }}</strong>
                        <p>{{ $member->member_code ?: 'N/A' }}</p>
                    </div>

                    <div class="student-card-summary">
                        <span class="student-timeline-icon">C</span>
                        <div>
                            <strong>{{ $latestLibraryCard ? $latestLibraryCard->card_number : __('No library card') }}</strong>
                            <p>{{ $latestLibraryCard?->expires_at?->format('Y-m-d') ?? __('Issue new card') }}</p>
                        </div>
                    </div>

                    <form id="library-card-form" method="POST" action="{{ route('library.members.card.issue', $member) }}">
                        @csrf
                    </form>

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit" form="library-member-form" data-i18n="saveChanges">Save changes</button>
                        <button class="btn btn-outline-primary" type="submit" form="library-card-form" data-i18n="{{ $latestLibraryCard ? 'renewCard' : 'issueNewCard' }}">{{ $latestLibraryCard ? 'Renew card' : 'Issue new card' }}</button>
                        @if ($latestLibraryCard)
                            <a class="btn btn-outline-secondary" href="{{ route('membership-cards.print', $latestLibraryCard) }}" data-i18n="printCard">Print card</a>
                        @endif
                        <a class="btn btn-dark" href="{{ route('library.members.show', $member) }}" data-i18n="cancel">Cancel</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
@endsection
