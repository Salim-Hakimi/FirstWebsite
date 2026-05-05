@extends('admin.layout')

@section('title', 'Students - Fanous Admin')

@section('content')
    @php
        $activeCount = $students->where('status', 'active')->count();
        $waitingCount = $students->where('status', 'waiting')->count();

        $documentCount = $students->sum(function ($student) {
            return count($student->document_names ?? []);
        });

        $withRoomCount = $students->filter(function ($student) {
            return $student->room || $student->room_number;
        })->count();

        $withPhotoCount = $students->whereNotNull('profile_photo_path')->count();
        $statusNames = $statusLabels;
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Dorm Students</h3>
                            <p class="mb-0 text-white-50">Search student records, open profiles, review documents, rooms, cards, and linked financial history.</p>
                        </div>
                        @if (auth()->user()->canAccessAdmin())
                            <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                                <a href="{{ route('dorm.students.create') }}" class="btn btn-outline-light btn-rounded">+ Register student</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <h3 class="mb-0">{{ $activeCount }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-success">
                                <span class="metric-icon">A</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Active students</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <h3 class="mb-0">{{ $waitingCount }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-warning">
                                <span class="metric-icon">W</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Waiting list</h6>
                    <p class="text-muted mb-0">{{ $waitingApplicants->count() }} under review</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <h3 class="mb-0">{{ $withRoomCount }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-warning">
                                <span class="metric-icon">R</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Assigned rooms</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <h3 class="mb-0">{{ $documentCount }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-danger">
                                <span class="metric-icon">D</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Documents</h6>
                    <p class="text-muted mb-0">{{ $withPhotoCount }} with photo</p>
                </div>
            </div>
        </div>
    </div>

    @if (auth()->user()->canAccessAdmin())
        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                            <div>
                                <h4 class="card-title mb-1">Admission waiting list</h4>
                                <p class="text-muted mb-0">Rank applicants by eligibility score, education percentage, and the oldest application date.</p>
                            </div>
                            <a class="btn btn-outline-secondary mt-3 mt-lg-0" href="{{ route('dorm.students.index', ['status' => 'waiting']) }}">View waiting only</a>
                        </div>

                        <div class="waitlist-queue-grid">
                            @forelse ($waitingApplicants as $applicant)
                                @php
                                    $statusClass = $applicant->status === 'waiting' ? 'badge-outline-warning' : 'badge-outline-secondary';
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
                                            <span>{{ $applicant->province ?: 'Province not recorded' }} &middot; {{ $applicant->education_place }}</span>
                                        </div>

                                        <span class="badge {{ $statusClass }}">{{ $statusNames[$applicant->status] ?? $applicant->status }}</span>
                                    </div>

                                    <div class="waitlist-score-grid">
                                        <span><strong>Eligibility</strong>{{ $applicant->eligibility_score ?? 'N/A' }}</span>
                                        <span><strong>Education %</strong>{{ $applicant->education_score ?? 'N/A' }}</span>
                                        <span><strong>Applied</strong>{{ optional($applicant->application_date)->format('Y-m-d') ?? 'N/A' }}</span>
                                    </div>

                                    <p class="text-muted mt-3 mb-3">{{ $applicant->eligibility_notes ?: 'No eligibility notes recorded.' }}</p>

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
                                                    Room {{ $room->room_number }} &middot; {{ $freeBeds }} free
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
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Student directory</h4>
                            <p class="text-muted mb-0">Filter by name, father name, phone, ID number, or room.</p>
                        </div>

                        @if (auth()->user()->canAccessAdmin())
                            <a class="btn btn-primary mt-3 mt-lg-0" href="{{ route('dorm.students.create') }}">Register student</a>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('dorm.students.index') }}" class="student-filter-row mb-4">
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

                    <div class="student-directory-grid mb-4">
                        @forelse ($students as $student)
                            @php
                                $dormCard = $student->membershipCards->first();

                                $badgeClasses = [
                                    'active' => 'badge-outline-success',
                                    'waiting' => 'badge-outline-warning',
                                    'on_hold' => 'badge-outline-secondary',
                                    'rejected' => 'badge-outline-danger',
                                    'suspended' => 'badge-outline-warning',
                                    'graduated' => 'badge-outline-primary',
                                    'left' => 'badge-outline-danger',
                                ];

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

                    <div class="table-responsive student-directory-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Room</th>
                                    <th>Contact</th>
                                    <th>Education</th>
                                    <th>Documents</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($students as $student)
                                    @php
                                        $dormCard = $student->membershipCards->first();

                                        $badgeClasses = [
                                            'active' => 'badge-outline-success',
                                            'waiting' => 'badge-outline-warning',
                                            'on_hold' => 'badge-outline-secondary',
                                            'rejected' => 'badge-outline-danger',
                                            'suspended' => 'badge-outline-warning',
                                            'graduated' => 'badge-outline-primary',
                                            'left' => 'badge-outline-danger',
                                        ];

                                        $badgeClass = $badgeClasses[$student->status] ?? 'badge-outline-secondary';
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($student->profile_photo_path)
                                                    <img class="user-table-avatar user-table-avatar-img" src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name }}">
                                                @else
                                                    <div class="user-table-avatar">{{ strtoupper(substr($student->full_name, 0, 1)) }}</div>
                                                @endif

                                                <div>
                                                    <span class="font-weight-bold">{{ $student->full_name }}</span>
                                                    <div class="text-muted small">Father: {{ $student->father_name }} · ID: {{ $student->tazkira_number }}</div>
                                                </div>
                                            </div>
                                        </td>

                                        <td>
                                            Room {{ optional($student->room)->room_number ?: ($student->room_number ?: 'Not assigned') }}
                                            <div class="text-muted small">Bed: {{ $student->bed_number ?: 'Not recorded' }}</div>
                                        </td>

                                        <td>
                                            {{ $student->phone }}
                                            <div class="text-muted small">WhatsApp: {{ $student->whatsapp ?: 'Not recorded' }}</div>
                                        </td>

                                        <td>
                                            {{ $student->education_place }}
                                            <div class="text-muted small">{{ $student->department_or_grade ?: 'Department / grade not recorded' }}</div>
                                        </td>

                                        <td>{{ count($student->document_names ?? []) }} files</td>

                                        <td>
                                            <span class="badge {{ $badgeClass }}">{{ $statusNames[$student->status] ?? $student->status }}</span>
                                        </td>

                                        <td>
                                            <div class="student-table-actions">
                                                <a class="btn btn-primary btn-sm mr-2" href="{{ route('dorm.students.show', $student) }}">Profile</a>

                                                @if (auth()->user()->canAccessAdmin())
                                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.edit', $student) }}">Edit</a>
                                                @endif
                                            </div>

                                            <div class="text-muted small mt-2">
                                                Card: {{ optional(optional($dormCard)->expires_at)->format('Y-m-d') ?? 'None' }}
                                                ·
                                                {{ optional($dormCard)->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No students were found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection