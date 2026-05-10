@extends('admin.layout')

@section('title', 'Fanous Admin Dashboard')

@section('content')
    @php
        $roleTranslations = [
            \App\Models\User::ROLE_OWNER => ['key' => 'roleOwner', 'label' => 'Owner'],
            \App\Models\User::ROLE_MANAGER => ['key' => 'roleManager', 'label' => 'Manager'],
            \App\Models\User::ROLE_ADMIN => ['key' => 'roleAdmin', 'label' => 'Admin'],
            \App\Models\User::ROLE_GUARD => ['key' => 'roleGuard', 'label' => 'Guard'],
            \App\Models\User::ROLE_STUDENT_REPRESENTATIVE => ['key' => 'roleStudentRepresentative', 'label' => 'Student Representative'],
            \App\Models\User::ROLE_PURCHASER => ['key' => 'rolePurchaser', 'label' => 'Purchaser'],
            \App\Models\User::ROLE_LIBRARIAN => ['key' => 'roleLibrarian', 'label' => 'Librarian'],
            \App\Models\User::ROLE_COOK => ['key' => 'roleCook', 'label' => 'Cook'],
            \App\Models\User::ROLE_DORM_STUDENT => ['key' => 'roleDormStudent', 'label' => 'Dorm Student'],
            \App\Models\User::ROLE_LIBRARY_MEMBER => ['key' => 'roleLibraryMember', 'label' => 'Library Member'],
            \App\Models\User::ROLE_APPLICANT => ['key' => 'roleApplicant', 'label' => 'Applicant'],
        ];
        $statusTranslations = [
            \App\Models\User::STATUS_ACTIVE => ['key' => 'statusActive', 'label' => 'Active'],
            \App\Models\User::STATUS_PENDING => ['key' => 'statusPending', 'label' => 'Pending'],
            \App\Models\User::STATUS_SUSPENDED => ['key' => 'statusSuspended', 'label' => 'Suspended'],
        ];
        $followUpTotal = $waitingStudents + $onHoldStudents + $pendingUsers + $overdueLoans->count();
    @endphp

    <section class="admin-command-center">
        <div>
            <span class="student-command-kicker">Management overview</span>
            <h1>Fanous control room</h1>
            <p>Today’s operational picture for dorm capacity, admissions, registration payments, library activity, and staff access.</p>
        </div>

        <div class="admin-command-actions">
            <a class="btn btn-primary" href="{{ route('dorm.students.index') }}">Open students</a>
            <a class="btn btn-outline-light" href="{{ route('library.index') }}">Open library</a>
            <a class="btn btn-outline-light" href="{{ route('admin.users.create') }}">Create staff</a>
        </div>
    </section>

    <section class="student-insight-grid admin-insight-grid">
        <article class="student-insight-card is-primary">
            <span>Dorm occupancy</span>
            <strong>{{ $occupancyRate }}%</strong>
            <p>{{ $occupiedBeds }} of {{ $totalBeds }} beds used</p>
        </article>
        <article class="student-insight-card">
            <span>Active students</span>
            <strong>{{ $activeStudents }}</strong>
            <p>{{ $waitingStudents }} waiting, {{ $onHoldStudents }} on hold</p>
        </article>
        <article class="student-insight-card">
            <span>Library activity</span>
            <strong>{{ $activeLoans }}</strong>
            <p>{{ $overdueLoans->count() }} overdue loans</p>
        </article>
        <article class="student-insight-card">
            <span>Follow-up queue</span>
            <strong>{{ $followUpTotal }}</strong>
            <p>Admissions, staff access, and overdue books</p>
        </article>
    </section>

    <section class="admin-dashboard-grid">
        <div class="student-workspace-panel admin-span-2">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Capacity</span>
                    <h2>Rooms and beds</h2>
                    <p>{{ $totalRooms }} rooms, {{ $freeBeds }} free beds, {{ $occupiedBeds }} occupied beds.</p>
                </div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.rooms.index') }}">Manage rooms</a>
            </div>

            <div class="admin-capacity-layout">
                <div class="admin-occupancy-ring" style="--value: {{ $occupancyRate }}">
                    <strong>{{ $occupancyRate }}%</strong>
                    <span>occupied</span>
                </div>

                <div class="admin-mini-grid">
                    <span><strong>{{ $totalBeds }}</strong>Total beds</span>
                    <span><strong>{{ $occupiedBeds }}</strong>Used beds</span>
                    <span><strong>{{ $freeBeds }}</strong>Free beds</span>
                    <span><strong>{{ $totalRooms }}</strong>Rooms</span>
                </div>
            </div>

            <div class="student-timeline-list">
                @forelse ($crowdedRooms as $room)
                    @php
                        $rate = $room->capacity > 0 ? round(($room->occupied_beds / $room->capacity) * 100) : 0;
                    @endphp
                    <div class="admin-room-row">
                        <div>
                            <strong>Room {{ $room->room_number }}</strong>
                            <p>{{ $room->occupied_beds }} / {{ $room->capacity }} beds used</p>
                        </div>
                        <div class="admin-progress"><span style="width: {{ min(100, $rate) }}%"></span></div>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.rooms.show', $room) }}">Open</a>
                    </div>
                @empty
                    <div class="student-directory-empty">No crowded rooms right now.</div>
                @endforelse
            </div>
        </div>

        <aside class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">This month</span>
                    <h2>Registration payments</h2>
                    <p>Only the amounts collected by management during dorm admission.</p>
                </div>
            </div>

            <div class="admin-finance-stack">
                <span><strong>{{ number_format($monthlyGuaranteeDeposits) }}</strong>Guarantee deposits</span>
                <span><strong>{{ number_format($monthlyDormRegistrationFees) }}</strong>Dorm expense fees</span>
                <span><strong>{{ number_format($monthlyDormCardFees) }}</strong>Card fees</span>
                <span class="is-positive"><strong>{{ number_format($monthlyRegistrationIncome) }}</strong>{{ $monthlyRegistrationCount }} paid registrations</span>
            </div>

            <div class="admin-command-actions mt-3">
                <a class="btn btn-primary btn-sm" href="{{ route('dorm.students.create') }}">Register student</a>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.index') }}">Student records</a>
            </div>
        </aside>
    </section>

    <section class="admin-dashboard-grid">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Admissions</span>
                    <h2>Recent students</h2>
                    <p>Latest registered dorm student records.</p>
                </div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.index') }}">View all</a>
            </div>

            <div class="student-timeline-list">
                @forelse ($recentStudents as $student)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">S</span>
                        <div>
                            <strong>{{ $student->full_name }}</strong>
                            <p>{{ ucfirst(str_replace('_', ' ', $student->status)) }} · {{ $student->education_place ?: 'Education not recorded' }}</p>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.show', $student) }}">Profile</a>
                    </div>
                @empty
                    <div class="student-directory-empty">No students have been registered yet.</div>
                @endforelse
            </div>
        </div>

        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Library</span>
                    <h2>Overdue books</h2>
                    <p>{{ $libraryMembers }} active members, {{ $bookTitles }} titles, {{ $availableBooks }} available copies.</p>
                </div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.index') }}">Library</a>
            </div>

            <div class="student-timeline-list">
                @forelse ($overdueLoans as $loan)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">L</span>
                        <div>
                            <strong>{{ $loan->member?->full_name ?: 'Unknown member' }}</strong>
                            <p>{{ $loan->book?->title ?: 'Unknown book' }} · Due {{ $loan->due_at?->format('Y-m-d') ?: 'N/A' }}</p>
                        </div>
                        @if ($loan->member)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.members.show', $loan->member) }}">Profile</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">No overdue books right now.</div>
                @endforelse
            </div>
        </div>

        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Staff</span>
                    <h2>Recent users</h2>
                    <p>{{ $activeUsers }} active accounts, {{ $pendingUsers }} waiting for review.</p>
                </div>
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.users.index') }}">Users</a>
            </div>

            <div class="student-timeline-list">
                @forelse ($recentUsers as $user)
                    @php
                        $roleMeta = $roleTranslations[$user->role] ?? ['key' => 'roleUser', 'label' => $user->role];
                        $statusMeta = $statusTranslations[$user->status] ?? ['key' => 'statusUnknown', 'label' => $user->status];
                    @endphp
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">U</span>
                        <div>
                            <strong>{{ $user->name }}</strong>
                            <p>{{ $roleMeta['label'] }} · {{ $statusMeta['label'] }}</p>
                        </div>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                    </div>
                @empty
                    <div class="student-directory-empty">No users have been created yet.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="student-workspace-panel">
        <div class="student-panel-head">
            <div>
                <span class="student-panel-label">Quick access</span>
                <h2>Management panels</h2>
                <p>Open the most used work areas without searching through the sidebar.</p>
            </div>
        </div>

        <div class="admin-shortcut-grid">
            <a href="{{ route('admin.users.index') }}"><span>U</span><strong>Users & Roles</strong><em>Staff accounts and access</em></a>
            <a href="{{ route('dorm.rooms.index') }}"><span>R</span><strong>Dorm Rooms</strong><em>Capacity and occupancy</em></a>
            <a href="{{ route('dorm.students.index') }}"><span>S</span><strong>Dorm Students</strong><em>Profiles and admission</em></a>
            <a href="{{ route('representative.index') }}"><span>C</span><strong>Representative</strong><em>Student collections and fines</em></a>
            <a href="{{ route('purchaser.report') }}"><span>F</span><strong>Purchaser Finance</strong><em>Food account and expenses</em></a>
            <a href="{{ route('library.index') }}"><span>L</span><strong>Library</strong><em>Members, books, loans</em></a>
        </div>
    </section>
@endsection
