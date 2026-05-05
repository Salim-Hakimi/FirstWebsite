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
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1"><span data-i18n="editMember">Edit member</span>: {{ $member->full_name }}</h3>
                            <p class="mb-0 text-white-50" data-i18n="editMemberDescription">Update member details, payment status, expiry dates, and library card information.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a class="btn btn-outline-light btn-rounded" href="{{ route('library.members.show', $member) }}" data-i18n="back">Back</a>
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
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="memberDetails">Member details</h4>
                    <form id="library-member-form" method="POST" action="{{ route('library.members.update', $member) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6 form-group"><label data-i18n="memberCode">Member code</label><input class="form-control" name="member_code" value="{{ old('member_code', $member->member_code) }}"></div>
                            <div class="col-md-6 form-group"><label data-i18n="fullName">Full name</label><input class="form-control" name="full_name" value="{{ old('full_name', $member->full_name) }}" required></div>
                            <div class="col-md-6 form-group"><label data-i18n="fatherName">Father name</label><input class="form-control" name="father_name" value="{{ old('father_name', $member->father_name) }}" required></div>
                            <div class="col-md-6 form-group"><label data-i18n="phoneNumber">Phone number</label><input class="form-control" name="phone" value="{{ old('phone', $member->phone) }}" required></div>
                            <div class="col-md-6 form-group"><label data-i18n="emailAddress">Email address</label><input class="form-control" name="email" type="email" value="{{ old('email', $member->email) }}"></div>
                            <div class="col-12 form-group">
                                <div class="student-photo-uploader">
                                    @if ($member->profile_photo_path)
                                        <img src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
                                    @else
                                        <span>{{ strtoupper(substr($member->full_name ?: 'M', 0, 1)) }}</span>
                                    @endif
                                    <div class="flex-grow-1">
                                        <label data-i18n="profilePhoto">Profile photo</label>
                                        <input class="form-control" name="profile_photo" type="file" accept="image/*">
                                        @if ($member->profile_photo_path)
                                            <label class="d-inline-flex align-items-center text-muted small mt-2">
                                                <input class="mr-2" name="remove_profile_photo" type="checkbox" value="1">
                                                <span data-i18n="removeCurrentPhoto">Remove current photo</span>
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 form-group"><label data-i18n="idTazkira">ID / Tazkira</label><input class="form-control" name="tazkira_number" value="{{ old('tazkira_number', $member->tazkira_number) }}"></div>
                            <div class="col-md-6 form-group"><label data-i18n="educationPlace">Education place</label><input class="form-control" name="education_place" value="{{ old('education_place', $member->education_place) }}"></div>
                            <div class="col-md-6 form-group"><label data-i18n="departmentGrade">Department / grade</label><input class="form-control" name="department_or_grade" value="{{ old('department_or_grade', $member->department_or_grade) }}"></div>
                            <div class="col-12 form-group"><label data-i18n="address">Address</label><input class="form-control" name="address" value="{{ old('address', $member->address) }}"></div>

                            <div class="col-md-4 form-group"><label data-i18n="monthlyFee">Monthly fee</label><input class="form-control" name="membership_fee" type="number" min="0" value="{{ old('membership_fee', $member->membership_fee) }}"></div>
                            <div class="col-md-4 form-group">
                                <label data-i18n="paymentStatus">Payment status</label>
                                <select class="form-control" name="payment_status">
                                    <option value="unpaid" @selected(old('payment_status', $member->payment_status) === 'unpaid') data-i18n="unpaid">Unpaid</option>
                                    <option value="paid" @selected(old('payment_status', $member->payment_status) === 'paid') data-i18n="paid">Paid</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label data-i18n="status">Status</label>
                                <select class="form-control" name="status">
                                    @foreach ($memberStatusMeta as $value => $meta)
                                        <option value="{{ $value }}" @selected(old('status', $member->status) === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group"><label data-i18n="joinedAt">Joined at</label><input class="form-control" name="joined_at" type="date" value="{{ old('joined_at', $member->joined_at?->format('Y-m-d')) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="membershipExpiry">Membership expiry</label><input class="form-control" name="membership_expires_at" type="date" value="{{ old('membership_expires_at', $member->membership_expires_at?->format('Y-m-d')) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="nextDue">Next due</label><input class="form-control" name="next_payment_due_at" type="date" value="{{ old('next_payment_due_at', $member->next_payment_due_at?->format('Y-m-d')) }}"></div>
                            <div class="col-12 form-group"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $member->notes) }}</textarea></div>
                            <div class="col-12">
                                <button class="btn btn-primary mr-2" type="submit" data-i18n="saveChanges">Save changes</button>
                                <a class="btn btn-dark" href="{{ route('library.members.show', $member) }}" data-i18n="cancel">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="currentCard">Current card</h4>
                    <p class="card-description">{{ $latestLibraryCard?->expires_at?->format('Y-m-d') ?? 'N/A' }} · <span data-i18n="{{ $latestLibraryCard?->payment_status === 'paid' ? 'paid' : 'unpaid' }}">{{ $latestLibraryCard?->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</span></p>
                    <form id="library-card-form" method="POST" action="{{ route('library.members.card.issue', $member) }}">
                        @csrf
                    </form>
                    <button class="btn btn-primary mr-2" type="submit" form="library-card-form" data-i18n="{{ $latestLibraryCard ? 'renewCard' : 'issueNewCard' }}">{{ $latestLibraryCard ? 'Renew card' : 'Issue new card' }}</button>
                    @if ($latestLibraryCard)
                        <a class="btn btn-outline-secondary mt-2" href="{{ route('membership-cards.print', $latestLibraryCard) }}" data-i18n="printCard">Print card</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
