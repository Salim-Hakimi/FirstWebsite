@extends('admin.layout')

@section('title', $student->exists ? 'Edit Student - Fanous Admin' : 'Register Student - Fanous Admin')

@section('content')
    @php
        $studentStatusNames = $statusLabels;
        $latestDormCard = $dormCard ?? null;
        $selectedStatus = old('status', $student->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker">{{ $student->exists ? 'Student record' : 'New admission' }}</span>
            <h1>{{ $student->exists ? 'Edit Student Record' : 'Register New Student' }}</h1>
            <p>Capture identity, admission priority, room assignment, guarantor details, documents, and dorm card status in one guided flow.</p>
        </div>

        <div class="student-command-actions">
            <a href="{{ route('dorm.students.index') }}" class="btn btn-outline-light">Back to students</a>
            @if ($student->exists)
                <a href="{{ route('dorm.students.show', $student) }}" class="btn btn-primary">View profile</a>
            @endif
        </div>
    </section>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">Please review the highlighted fields and save again.</div>
    @endif

    <form id="student-form" method="POST" action="{{ $student->exists ? route('dorm.students.update', $student) : route('dorm.students.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($student->exists)
            @method('PUT')
        @endif

        <div class="student-form-layout">
            <main class="student-form-main">
                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">01</span>
                        <div>
                            <h2>Identity and contact</h2>
                            <p>Basic information used on the profile, search cards, and printed dorm card.</p>
                        </div>
                    </div>

                    <div class="student-photo-uploader student-form-photo">
                        @if ($student->profile_photo_path)
                            <img src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name ?: 'Student photo' }}">
                        @else
                            <span>{{ strtoupper(substr($student->full_name ?: 'S', 0, 1)) }}</span>
                        @endif
                        <div class="flex-grow-1">
                            <label>Profile photo</label>
                            <input class="form-control @error('profile_photo') is-invalid @enderror" name="profile_photo" type="file" accept="image/*">
                            <small class="text-muted">Use a clear portrait. This image appears on profile cards and printed cards.</small>
                            @error('profile_photo') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            @if ($student->profile_photo_path)
                                <label class="student-checkbox-line mt-2">
                                    <input name="remove_profile_photo" type="checkbox" value="1">
                                    <span>Remove current photo</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group">
                            <label>Full name</label>
                            <input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name', $student->full_name) }}" required>
                            @error('full_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Father name</label>
                            <input class="form-control @error('father_name') is-invalid @enderror" name="father_name" value="{{ old('father_name', $student->father_name) }}" required>
                            @error('father_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>ID / Tazkira number</label>
                            <input class="form-control @error('tazkira_number') is-invalid @enderror" name="tazkira_number" value="{{ old('tazkira_number', $student->tazkira_number) }}" required>
                            @error('tazkira_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Province</label>
                            <input class="form-control @error('province') is-invalid @enderror" name="province" value="{{ old('province', $student->province) }}">
                            @error('province') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Phone</label>
                            <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $student->phone) }}" required>
                            @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input class="form-control @error('whatsapp') is-invalid @enderror" name="whatsapp" value="{{ old('whatsapp', $student->whatsapp) }}">
                            @error('whatsapp') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $student->email) }}">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Joined date</label>
                            <input class="form-control @error('joined_at') is-invalid @enderror" name="joined_at" type="date" value="{{ old('joined_at', $student->joined_at?->format('Y-m-d')) }}">
                            @error('joined_at') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Education place</label>
                            <input class="form-control @error('education_place') is-invalid @enderror" name="education_place" value="{{ old('education_place', $student->education_place) }}" required>
                            @error('education_place') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Department / grade</label>
                            <input class="form-control @error('department_or_grade') is-invalid @enderror" name="department_or_grade" value="{{ old('department_or_grade', $student->department_or_grade) }}">
                            @error('department_or_grade') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2>Admission assessment</h2>
                            <p>Use these fields for waiting-list priority, applicant review, and admission decisions.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group">
                            <label>Application date</label>
                            <input class="form-control @error('application_date') is-invalid @enderror" name="application_date" type="date" value="{{ old('application_date', $student->application_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                            @error('application_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Education percentage</label>
                            <input class="form-control @error('education_score') is-invalid @enderror" name="education_score" type="number" min="0" max="100" step="0.01" value="{{ old('education_score', $student->education_score) }}" placeholder="0 - 100">
                            @error('education_score') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Eligibility score</label>
                            <input class="form-control @error('eligibility_score') is-invalid @enderror" name="eligibility_score" type="number" min="0" max="100" value="{{ old('eligibility_score', $student->eligibility_score) }}" placeholder="0 - 100">
                            @error('eligibility_score') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group full">
                            <label>Eligibility notes</label>
                            <textarea class="form-control @error('eligibility_notes') is-invalid @enderror" name="eligibility_notes" rows="3" placeholder="Priority reason, distance, financial need, committee note, or document review">{{ old('eligibility_notes', $student->eligibility_notes) }}</textarea>
                            @error('eligibility_notes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">03</span>
                        <div>
                            <h2>Guarantor and documents</h2>
                            <p>Attach supporting files and keep guarantor information near the student profile.</p>
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group">
                            <label>Guarantor name</label>
                            <input class="form-control @error('guarantor_name') is-invalid @enderror" name="guarantor_name" value="{{ old('guarantor_name', $student->guarantor_name) }}">
                            @error('guarantor_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Guarantor phone</label>
                            <input class="form-control @error('guarantor_phone') is-invalid @enderror" name="guarantor_phone" value="{{ old('guarantor_phone', $student->guarantor_phone) }}">
                            @error('guarantor_phone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group full">
                            <label>Upload documents</label>
                            <input class="form-control @error('documents') is-invalid @enderror" name="documents[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <small class="text-muted">Allowed: images, PDF, Word. Maximum 5MB per file.</small>
                            @error('documents') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            @error('documents.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($student->document_names)
                        <div class="student-document-list">
                            @foreach ($student->document_names as $index => $document)
                                <article class="student-document-item">
                                    <span class="student-timeline-icon">D</span>
                                    <div>
                                        <strong>{{ $document['name'] ?? 'Unnamed document' }}</strong>
                                        <p>{{ $document['uploaded_at'] ?? 'Unknown upload date' }}</p>
                                        <label class="student-checkbox-line">
                                            <input name="remove_documents[]" type="checkbox" value="{{ $index }}">
                                            <span>Remove this file when saving</span>
                                        </label>
                                    </div>
                                    @if (! empty($document['path']))
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ asset('storage/'.$document['path']) }}" target="_blank" rel="noopener">Open</a>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group mb-0">
                        <label>Notes</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', $student->notes) }}</textarea>
                        @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </section>
            </main>

            <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">04</span>
                        <div>
                            <h2>Room and status</h2>
                            <p>Room assignment applies only to active students.</p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary">{{ $studentStatusNames[$selectedStatus] ?? $selectedStatus }}</span>
                        <strong>{{ old('full_name', $student->full_name) ?: 'Student name' }}</strong>
                        <p>{{ old('education_place', $student->education_place) ?: 'Education place' }}</p>
                    </div>

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
                        <small class="text-muted">Waiting-list applicants stay without a bed until admission.</small>
                        @error('dorm_room_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="student-form-grid compact">
                        <div class="form-group">
                            <label>Manual room</label>
                            <input class="form-control @error('room_number') is-invalid @enderror" name="room_number" value="{{ old('room_number', $student->room_number) }}" placeholder="Optional">
                            @error('room_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Bed</label>
                            <input class="form-control @error('bed_number') is-invalid @enderror" name="bed_number" value="{{ old('bed_number', $student->bed_number) }}">
                            @error('bed_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                            @foreach ($studentStatusNames as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Use Waiting list when there is no space yet or the applicant needs review.</small>
                        @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="student-side-divider"></div>
                    <h3 class="student-side-title">Registration payment</h3>
                    <div class="student-status-preview">
                        <strong>Total required: {{ number_format((int) old('guarantee_deposit_amount', $student->guarantee_deposit_amount ?? 1000) + (int) old('dorm_expense_fee_amount', $student->dorm_expense_fee_amount ?? 1000) + (int) old('registration_card_fee_amount', $student->registration_card_fee_amount ?? 50)) }} AFN</strong>
                        <p>Guarantee deposit, initial dorm expenses, and card fee are collected by the admin during registration.</p>
                    </div>
                    <div class="student-form-grid compact">
                        <div class="form-group">
                            <label>Guarantee deposit</label>
                            <input class="form-control @error('guarantee_deposit_amount') is-invalid @enderror" name="guarantee_deposit_amount" type="number" min="0" value="{{ old('guarantee_deposit_amount', $student->guarantee_deposit_amount ?? 1000) }}">
                            @error('guarantee_deposit_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Dorm expenses</label>
                            <input class="form-control @error('dorm_expense_fee_amount') is-invalid @enderror" name="dorm_expense_fee_amount" type="number" min="0" value="{{ old('dorm_expense_fee_amount', $student->dorm_expense_fee_amount ?? 1000) }}">
                            @error('dorm_expense_fee_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="student-form-grid compact">
                        <div class="form-group">
                            <label>Payment status</label>
                            <select class="form-control @error('registration_payment_status') is-invalid @enderror" name="registration_payment_status">
                                @php $registrationStatus = old('registration_payment_status', $student->registration_payment_status ?? 'paid'); @endphp
                                <option value="paid" @selected($registrationStatus === 'paid')>Paid</option>
                                <option value="partial" @selected($registrationStatus === 'partial')>Partial</option>
                                <option value="unpaid" @selected($registrationStatus === 'unpaid')>Unpaid</option>
                            </select>
                            @error('registration_payment_status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Paid date</label>
                            <input class="form-control @error('registration_paid_at') is-invalid @enderror" name="registration_paid_at" type="date" value="{{ old('registration_paid_at', $student->registration_paid_at?->format('Y-m-d') ?? now()->toDateString()) }}">
                            @error('registration_paid_at') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Dorm card fee</label>
                        <input class="form-control @error('registration_card_fee_amount') is-invalid @enderror" name="registration_card_fee_amount" type="number" min="0" value="{{ old('registration_card_fee_amount', $student->registration_card_fee_amount ?? 50) }}">
                        <small class="text-muted">Default card fee is 50 AFN and appears on the printed card.</small>
                        @error('registration_card_fee_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    @unless ($student->exists)
                        <div class="student-side-divider"></div>
                        <h3 class="student-side-title">Dorm card</h3>
                        <div class="form-group">
                            <label>Card fee</label>
                            <input class="form-control @error('card_fee') is-invalid @enderror" name="card_fee" type="number" min="0" step="0.01" value="{{ old('card_fee', $student->registration_card_fee_amount ?? 50) }}">
                            <small class="text-muted">This 50 AFN card fee is saved on the printed card.</small>
                            @error('card_fee') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Payment status</label>
                            <select class="form-control @error('card_payment_status') is-invalid @enderror" name="card_payment_status">
                                <option value="paid" @selected(old('card_payment_status', 'paid') === 'paid')>Paid</option>
                                <option value="unpaid" @selected(old('card_payment_status') === 'unpaid')>Unpaid</option>
                            </select>
                            @error('card_payment_status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="student-side-divider"></div>
                        <div class="student-card-summary">
                            <span class="student-timeline-icon">C</span>
                            <div>
                                <strong>{{ $latestDormCard ? 'Current card' : 'No dorm card' }}</strong>
                                <p>{{ $latestDormCard?->expires_at?->format('Y-m-d') ?? 'Issue a card after admission.' }} · {{ $latestDormCard?->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</p>
                            </div>
                        </div>
                    @endunless

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit">Save record</button>
                        @if ($student->exists)
                            @if ($student->status === 'active')
                                <button class="btn btn-outline-primary" type="submit" form="issue-card-form">{{ $latestDormCard ? 'Renew card' : 'Issue card' }}</button>
                                @if ($latestDormCard)
                                    <a class="btn btn-outline-secondary" href="{{ route('membership-cards.print', $latestDormCard) }}">Print card</a>
                                @endif
                            @else
                                <small class="text-muted">Card can be issued after admission.</small>
                            @endif
                        @else
                            <button class="btn btn-outline-primary" type="submit" name="issue_card" value="1">Save and print card</button>
                        @endif
                        <a class="btn btn-dark" href="{{ route('dorm.students.index') }}">Cancel</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>

    @if ($student->exists)
        <form id="issue-card-form" method="POST" action="{{ route('dorm.students.card.issue', $student) }}">
            @csrf
        </form>
    @endif
@endsection
