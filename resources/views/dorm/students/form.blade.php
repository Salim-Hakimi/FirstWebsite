@extends('admin.layout')

@section('title', $student->exists ? 'Edit Student - Fanous Admin' : 'Register Student - Fanous Admin')

@section('content')
    @php
        $studentStatusNames = $statusLabels;
        $latestDormCard = $dormCard ?? null;
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">{{ $student->exists ? 'Edit Student Record' : 'Register New Student' }}</h3>
                            <p class="mb-0 text-white-50">Manage identity, contact, education, room assignment, guarantor, documents, and dorm card status.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('dorm.students.index') }}" class="btn btn-outline-light btn-rounded">Back to students</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form id="student-form" method="POST" action="{{ $student->exists ? route('dorm.students.update', $student) : route('dorm.students.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($student->exists)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Student profile</h4>
                        <p class="card-description">Personal, contact, and education information</p>

                        <div class="student-photo-uploader mb-4">
                            @if ($student->profile_photo_path)
                                <img src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name ?: 'Student photo' }}">
                            @else
                                <span>{{ strtoupper(substr($student->full_name ?: 'S', 0, 1)) }}</span>
                            @endif
                            <div class="flex-grow-1">
                                <label>Profile photo</label>
                                <input class="form-control @error('profile_photo') is-invalid @enderror" name="profile_photo" type="file" accept="image/*">
                                <span class="text-muted small">Upload a clear portrait. It will appear on the student profile and printed card.</span>
                                @error('profile_photo') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                @if ($student->profile_photo_path)
                                    <label class="d-inline-flex align-items-center text-muted small mt-2">
                                        <input class="mr-2" name="remove_profile_photo" type="checkbox" value="1">
                                        Remove current photo
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Full name</label>
                                <input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name', $student->full_name) }}" required>
                                @error('full_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Father name</label>
                                <input class="form-control @error('father_name') is-invalid @enderror" name="father_name" value="{{ old('father_name', $student->father_name) }}" required>
                                @error('father_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>ID / Tazkira number</label>
                                <input class="form-control @error('tazkira_number') is-invalid @enderror" name="tazkira_number" value="{{ old('tazkira_number', $student->tazkira_number) }}" required>
                                @error('tazkira_number') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Province</label>
                                <input class="form-control @error('province') is-invalid @enderror" name="province" value="{{ old('province', $student->province) }}">
                                @error('province') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Phone</label>
                                <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $student->phone) }}" required>
                                @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>WhatsApp</label>
                                <input class="form-control @error('whatsapp') is-invalid @enderror" name="whatsapp" value="{{ old('whatsapp', $student->whatsapp) }}">
                                @error('whatsapp') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $student->email) }}">
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Joined date</label>
                                <input class="form-control @error('joined_at') is-invalid @enderror" name="joined_at" type="date" value="{{ old('joined_at', $student->joined_at?->format('Y-m-d')) }}">
                                @error('joined_at') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Education place</label>
                                <input class="form-control @error('education_place') is-invalid @enderror" name="education_place" value="{{ old('education_place', $student->education_place) }}" required>
                                @error('education_place') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Department / grade</label>
                                <input class="form-control @error('department_or_grade') is-invalid @enderror" name="department_or_grade" value="{{ old('department_or_grade', $student->department_or_grade) }}">
                                @error('department_or_grade') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <h4 class="card-title mt-4">Admission assessment</h4>
                        <p class="card-description">Use these fields when the student is an applicant or should be placed on the waiting list.</p>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>Application date</label>
                                <input class="form-control @error('application_date') is-invalid @enderror" name="application_date" type="date" value="{{ old('application_date', $student->application_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                                @error('application_date') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label>School / university percentage</label>
                                <input class="form-control @error('education_score') is-invalid @enderror" name="education_score" type="number" min="0" max="100" step="0.01" value="{{ old('education_score', $student->education_score) }}" placeholder="0 - 100">
                                @error('education_score') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Eligibility score</label>
                                <input class="form-control @error('eligibility_score') is-invalid @enderror" name="eligibility_score" type="number" min="0" max="100" value="{{ old('eligibility_score', $student->eligibility_score) }}" placeholder="0 - 100">
                                @error('eligibility_score') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12 form-group">
                                <label>Eligibility notes</label>
                                <textarea class="form-control @error('eligibility_notes') is-invalid @enderror" name="eligibility_notes" rows="3" placeholder="Reason for priority, document review, distance, financial need, or committee note">{{ old('eligibility_notes', $student->eligibility_notes) }}</textarea>
                                @error('eligibility_notes') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <h4 class="card-title mt-4">Guarantor and documents</h4>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Guarantor name</label>
                                <input class="form-control @error('guarantor_name') is-invalid @enderror" name="guarantor_name" value="{{ old('guarantor_name', $student->guarantor_name) }}">
                                @error('guarantor_name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Guarantor phone</label>
                                <input class="form-control @error('guarantor_phone') is-invalid @enderror" name="guarantor_phone" value="{{ old('guarantor_phone', $student->guarantor_phone) }}">
                                @error('guarantor_phone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-12 form-group">
                                <label>Upload documents</label>
                                <input class="form-control @error('documents') is-invalid @enderror" name="documents[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                                <span class="text-muted small">Allowed: images, PDF, Word. Maximum 5MB per file.</span>
                                @error('documents') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                @error('documents.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        @if ($student->document_names)
                            <div class="preview-list mb-4">
                                @foreach ($student->document_names as $index => $document)
                                    <div class="preview-item border-bottom">
                                        <div class="preview-thumbnail"><div class="preview-icon bg-dark"><span>D</span></div></div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject mb-1">{{ $document['name'] ?? 'Unnamed document' }}</p>
                                            <p class="text-muted mb-2">Uploaded: {{ $document['uploaded_at'] ?? 'Unknown' }}</p>
                                            <label class="d-inline-flex align-items-center text-muted small">
                                                <input class="mr-2" name="remove_documents[]" type="checkbox" value="{{ $index }}">
                                                Remove this file when saving
                                            </label>
                                        </div>
                                        @if (! empty($document['path']))
                                            <a class="btn btn-outline-primary btn-sm" href="{{ asset('storage/'.$document['path']) }}" target="_blank" rel="noopener">Open</a>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', $student->notes) }}</textarea>
                            @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Room and status</h4>
                        <p class="card-description">Room capacity is checked before saving.</p>

                        <div class="form-group">
                            <label>Room</label>
                            <select class="form-control @error('dorm_room_id') is-invalid @enderror" name="dorm_room_id">
                                <option value="">Not assigned yet</option>
                                @foreach ($rooms as $room)
                                    @php
                                        $availableBeds = max(0, $room->capacity - $room->occupied_beds);
                                        $selectedRoomId = old('dorm_room_id', $student->dorm_room_id);
                                        $isCurrentRoom = (int) $selectedRoomId === (int) $room->id;
                                    @endphp
                                    <option value="{{ $room->id }}" @selected($isCurrentRoom) @disabled(! $isCurrentRoom && $availableBeds < 1)>
                                        Room {{ $room->room_number }} · capacity {{ $room->capacity }} · free {{ $isCurrentRoom ? $availableBeds + 1 : $availableBeds }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-muted small">Room is used only when status is Active. Waiting-list applicants stay without a bed until admission.</span>
                            @error('dorm_room_id') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Manual room number</label>
                            <input class="form-control @error('room_number') is-invalid @enderror" name="room_number" value="{{ old('room_number', $student->room_number) }}" placeholder="If room is not in list">
                            @error('room_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Bed number</label>
                            <input class="form-control @error('bed_number') is-invalid @enderror" name="bed_number" value="{{ old('bed_number', $student->bed_number) }}">
                            @error('bed_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                                @foreach ($studentStatusNames as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $student->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="text-muted small">Use Waiting list when there is no space yet or the applicant still needs review.</span>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @unless ($student->exists)
                            <h4 class="card-title mt-4">Dorm card</h4>
                            <div class="form-group">
                                <label>Card / registration fee</label>
                                <input class="form-control @error('card_fee') is-invalid @enderror" name="card_fee" type="number" min="0" step="0.01" value="{{ old('card_fee', 0) }}">
                                @error('card_fee') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label>Payment status</label>
                                <select class="form-control @error('card_payment_status') is-invalid @enderror" name="card_payment_status">
                                    <option value="unpaid" @selected(old('card_payment_status', 'unpaid') === 'unpaid')>Unpaid</option>
                                    <option value="paid" @selected(old('card_payment_status') === 'paid')>Paid</option>
                                </select>
                                @error('card_payment_status') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="user-access-note mt-4">
                                <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>C</span></div></div>
                                <p class="text-muted mb-0">Current card: {{ $latestDormCard?->expires_at?->format('Y-m-d') ?? 'No card' }} · {{ $latestDormCard?->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</p>
                            </div>
                        @endunless

                        <div class="d-flex flex-wrap mt-4">
                            <button class="btn btn-primary mr-2 mb-2" type="submit">Save</button>
                            @if ($student->exists)
                                @if ($student->status === 'active')
                                    <button class="btn btn-outline-primary mr-2 mb-2" type="submit" form="issue-card-form">{{ $latestDormCard ? 'Renew card' : 'Issue card' }}</button>
                                    @if ($latestDormCard)
                                        <a class="btn btn-outline-secondary mr-2 mb-2" href="{{ route('membership-cards.print', $latestDormCard) }}">Print card</a>
                                    @endif
                                @else
                                    <span class="text-muted small d-block w-100 mb-2">Card can be issued after admission.</span>
                                @endif
                            @else
                                <button class="btn btn-outline-primary mr-2 mb-2" type="submit" name="issue_card" value="1">Save & print card</button>
                            @endif
                            <a class="btn btn-dark mb-2" href="{{ route('dorm.students.index') }}">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @if ($student->exists)
        <form id="issue-card-form" method="POST" action="{{ route('dorm.students.card.issue', $student) }}">
            @csrf
        </form>
    @endif
@endsection
