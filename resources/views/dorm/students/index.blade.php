@extends('admin.layout')

@section('title', 'Students - Fanous Admin')

@section('content')
    @php
        $activeCount = $students->where('status', 'active')->count();
        $waitingCount = $students->where('status', 'waiting')->count();
        $onHoldCount = $students->where('status', 'on_hold')->count();
        $withRoomCount = $students->filter(fn ($student) => $student->room || $student->room_number)->count();
        $documentCount = $students->sum(fn ($student) => count($student->document_names ?? []));
        $withPhotoCount = $students->whereNotNull('profile_photo_path')->count();
        $statusNames = $statusLabels;
        $badgeClasses = [
            'active' => 'badge-outline-success',
            'waiting' => 'badge-outline-warning',
            'on_hold' => 'badge-outline-secondary',
            'rejected' => 'badge-outline-danger',
            'suspended' => 'badge-outline-warning',
            'graduated' => 'badge-outline-primary',
            'left' => 'badge-outline-danger',
        ];
    @endphp

    <section class="student-command-shell">
        <div class="student-command-copy">
            <span class="student-command-kicker">Dorm intelligence</span>
            <h1>Dorm Students</h1>
            <p>Search, shortlist, admit, and open complete student records from one focused workspace.</p>
        </div>

        <div class="student-command-actions">
            <a class="btn btn-outline-light" href="{{ route('dorm.students.index', ['status' => 'waiting']) }}">Waiting list</a>
            @if (auth()->user()->canAccessAdmin())
                <a class="btn btn-primary" href="{{ route('dorm.students.create') }}">Register student</a>
            @endif
        </div>
    </section>

    <section class="student-insight-grid">
        <article class="student-insight-card is-primary">
            <span>Active students</span>
            <strong>{{ $activeCount }}</strong>
            <p>{{ $withRoomCount }} assigned to rooms</p>
        </article>
        <article class="student-insight-card">
            <span>Admission queue</span>
            <strong>{{ $waitingCount }}</strong>
            <p>{{ $onHoldCount }} on hold</p>
        </article>
        <article class="student-insight-card">
            <span>Documents</span>
            <strong>{{ $documentCount }}</strong>
            <p>{{ $withPhotoCount }} profiles include photos</p>
        </article>
        <article class="student-insight-card">
            <span>Visible records</span>
            <strong>{{ $students->count() }}</strong>
            <p>Filtered by current search</p>
        </article>
    </section>

    @if (auth()->user()->canAccessAdmin())
        <section class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Admission priority</span>
                    <h2>Waiting list</h2>
                    <p>Ranked by eligibility, education percentage, and oldest application date.</p>
                </div>
                <a class="btn btn-outline-secondary" href="{{ route('dorm.students.index', ['status' => 'waiting']) }}">View queue</a>
            </div>

            <div class="waitlist-queue-grid">
                @forelse ($waitingApplicants as $applicant)
                    @php
                        $badgeClass = $badgeClasses[$applicant->status] ?? 'badge-outline-secondary';
                    @endphp

                    <article class="waitlist-card">
                        <div class="waitlist-card-head">
                            <span class="waitlist-rank">#{{ $loop->iteration }}</span>

                            @if ($applicant->profile_photo_path)
                                <img class="user-table-avatar user-table-avatar-img" src="{{ asset('storage/'.$applicant->profile_photo_path) }}" alt="{{ $applicant->full_name }}">
                            @else
                                <div class="user-table-avatar">{{ strtoupper(substr($applicant->full_name, 0, 1)) }}</div>
                            @endif

                            <div>
                                <h5>{{ $applicant->full_name }}</h5>
                                <span>{{ $applicant->province ?: 'Province not recorded' }} · {{ $applicant->education_place }}</span>
                            </div>

                            <span class="badge {{ $badgeClass }}">{{ $statusNames[$applicant->status] ?? $applicant->status }}</span>
                        </div>

                        <div class="waitlist-score-grid">
                            <span><strong>Eligibility</strong>{{ $applicant->eligibility_score ?? 'N/A' }}</span>
                            <span><strong>Education %</strong>{{ $applicant->education_score ?? 'N/A' }}</span>
                            <span><strong>Applied</strong>{{ optional($applicant->application_date)->format('Y-m-d') ?? 'N/A' }}</span>
                        </div>

                        <p class="student-muted-copy">{{ $applicant->eligibility_notes ?: 'No eligibility notes recorded.' }}</p>

                        <form class="waitlist-admit-form" method="POST" action="{{ route('dorm.students.admit', $applicant) }}">
                            @csrf
                            @method('PUT')

                            <select class="form-control" name="dorm_room_id" required>
                                <option value="">Select room</option>
                                @foreach ($admissionRooms as $room)
                                    @php
                                        $freeBeds = max(0, $room->capacity - $room->occupied_beds);
                                    @endphp
                                    <option value="{{ $room->id }}" {{ $freeBeds < 1 ? 'disabled' : '' }}>
                                        Room {{ $room->room_number }} · {{ $freeBeds }} free
                                    </option>
                                @endforeach
                            </select>

                            <input class="form-control" name="bed_number" placeholder="Bed">
                            <input class="form-control" name="admission_note" placeholder="Admission note">
                            <button class="btn btn-primary" type="submit">Admit</button>
                            <a class="btn btn-outline-secondary" href="{{ route('dorm.students.edit', $applicant) }}">Review</a>
                        </form>
                    </article>
                @empty
                    <div class="student-directory-empty">No applicants are waiting for admission.</div>
                @endforelse
            </div>
        </section>
    @endif

    <section class="student-workspace-panel">
        <div class="student-panel-head">
            <div>
                <span class="student-panel-label">Directory</span>
                <h2>Student records</h2>
                <p>Filter by name, father name, phone, ID number, or room.</p>
            </div>

            @if (auth()->user()->canAccessAdmin())
                <a class="btn btn-primary" href="{{ route('dorm.students.create') }}">Register student</a>
            @endif
        </div>

        <form method="GET" action="{{ route('dorm.students.index') }}" class="student-filter-row">
            <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search students...">

            <select class="form-control" name="status">
                <option value="">All statuses</option>
                @foreach ($statusNames as $value => $label)
                    <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-primary" type="submit">Search</button>
            <a class="btn btn-dark" href="{{ route('dorm.students.index') }}">Clear</a>
        </form>

        <div class="student-directory-grid">
            @forelse ($students as $student)
                @php
                    $dormCard = $student->membershipCards->first();
                    $badgeClass = $badgeClasses[$student->status] ?? 'badge-outline-secondary';
                    $studentRoomNumber = optional($student->room)->room_number;
                    $roomLabel = $student->status === 'active' ? ($studentRoomNumber ?: ($student->room_number ?: 'Not assigned')) : 'Not admitted';
                    $documentTotal = count($student->document_names ?? []);
                @endphp

                <article class="student-directory-card">
                    <div class="student-directory-head">
                        @if ($student->profile_photo_path)
                            <img class="user-table-avatar user-table-avatar-img" src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name }}">
                        @else
                            <div class="user-table-avatar">{{ strtoupper(substr($student->full_name, 0, 1)) }}</div>
                        @endif

                        <div>
                            <h5>{{ $student->full_name }}</h5>
                            <span>Father: {{ $student->father_name }}</span>
                        </div>

                        <span class="badge {{ $badgeClass }}">{{ $statusNames[$student->status] ?? $student->status }}</span>
                    </div>

                    <div class="student-directory-meta">
                        <span><strong>Room</strong>{{ $roomLabel }}</span>
                        <span><strong>Bed</strong>{{ $student->bed_number ?: 'Not recorded' }}</span>
                        <span><strong>Docs</strong>{{ $documentTotal }} files</span>
                        <span><strong>ID</strong>{{ $student->tazkira_number }}</span>
                        <span><strong>Phone</strong>{{ $student->phone }}</span>
                        <span>
                            <strong>{{ $student->status === 'active' ? 'Card' : 'Score' }}</strong>
                            {{ $student->status === 'active' ? (optional(optional($dormCard)->expires_at)->format('Y-m-d') ?? 'None') : ($student->eligibility_score ?? 'N/A') }}
                        </span>
                    </div>

                    <div class="student-directory-actions">
                        <a class="btn btn-primary btn-sm" href="{{ route('dorm.students.show', $student) }}">Profile</a>
                        @if (auth()->user()->canAccessAdmin())
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.edit', $student) }}">Edit</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="student-directory-empty">No students were found.</div>
            @endforelse
        </div>

    </section>
@endsection
