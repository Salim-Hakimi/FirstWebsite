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
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-8 py-4 px-4">
                            <h3 class="mb-1" data-i18n="fanousMainAdminDashboard">Fanous Main Admin Dashboard</h3>
                            <p class="mb-0 text-white-50" data-i18n="adminDashboardDescription">Manage users, dorm rooms, students, finance, representatives, and library records from one database-driven control room.</p>
                        </div>
                        <div class="col-12 col-md-4 text-md-right px-4 pb-4 pb-md-0">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-light btn-rounded" data-i18n="createStaffAccount">Create staff account</a>
                        </div>
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
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">{{ $totalUsers }}</h3>
                                <p class="text-success ml-2 mb-0 font-weight-medium" data-i18n="staff">staff</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-success">
                                <span class="metric-icon">U</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal" data-i18n="totalUsers">Total users</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">{{ $activeUsers }}</h3>
                                <p class="text-success ml-2 mb-0 font-weight-medium" data-i18n="active">active</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-success">
                                <span class="metric-icon">A</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal" data-i18n="activeAccounts">Active accounts</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">{{ $pendingUsers }}</h3>
                                <p class="text-warning ml-2 mb-0 font-weight-medium" data-i18n="review">review</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-warning">
                                <span class="metric-icon">P</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal" data-i18n="pendingAccounts">Pending accounts</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">6</h3>
                                <p class="text-success ml-2 mb-0 font-weight-medium" data-i18n="modules">modules</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-success">
                                <span class="metric-icon">M</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal" data-i18n="mainModules">Main modules</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-row justify-content-between">
                        <h4 class="card-title mb-1" data-i18n="managementPanels">Management panels</h4>
                        <p class="text-muted mb-1" data-i18n="activeRoutes">Active routes</p>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="preview-list">
                                @foreach ([
                                    ['labelKey' => 'usersAndRoles', 'label' => 'Users & Roles', 'bodyKey' => 'createStaffAccountsRolesAndAccessStatus', 'body' => 'Create staff accounts, roles, and access status.', 'url' => route('admin.users.index'), 'icon' => 'U', 'color' => 'bg-primary'],
                                    ['labelKey' => 'dormRooms', 'label' => 'Dorm Rooms', 'bodyKey' => 'manageRoomCapacityOccupancyAndRoomStatus', 'body' => 'Manage room capacity, occupancy, and room status.', 'url' => route('dorm.rooms.index'), 'icon' => 'R', 'color' => 'bg-success'],
                                    ['labelKey' => 'dormStudents', 'label' => 'Dorm Students', 'bodyKey' => 'openStudentRecordsDocumentsCardsAndRoomDetails', 'body' => 'Open student records, documents, cards, and room details.', 'url' => route('dorm.students.index'), 'icon' => 'S', 'color' => 'bg-info'],
                                    ['labelKey' => 'representative', 'label' => 'Representative', 'bodyKey' => 'recordMonthlyFeesElectricityWaterFinesAndRepresentativeExpenses', 'body' => 'Record monthly fees, electricity, water, fines, and representative expenses.', 'url' => route('representative.index'), 'icon' => 'R', 'color' => 'bg-warning'],
                                    ['labelKey' => 'financeReport', 'label' => 'Finance report', 'bodyKey' => 'reviewCollectionsExpensesAndBalances', 'body' => 'Review collections, expenses, and balances.', 'url' => route('purchaser.report'), 'icon' => 'F', 'color' => 'bg-danger'],
                                    ['labelKey' => 'library', 'label' => 'Library', 'bodyKey' => 'manageMembersBooksLoansAndReturns', 'body' => 'Manage members, books, loans, and returns.', 'url' => route('library.index'), 'icon' => 'L', 'color' => 'bg-warning'],
                                ] as $item)
                                    <a class="preview-item border-bottom" href="{{ $item['url'] }}">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon {{ $item['color'] }}">
                                                <span>{{ $item['icon'] }}</span>
                                            </div>
                                        </div>
                                        <div class="preview-item-content d-sm-flex flex-grow">
                                            <div class="flex-grow">
                                                <h6 class="preview-subject" data-i18n="{{ $item['labelKey'] }}">{{ $item['label'] }}</h6>
                                                <p class="text-muted mb-0" data-i18n="{{ $item['bodyKey'] }}">{{ $item['body'] }}</p>
                                            </div>
                                            <div class="shortcut-action text-sm-right pt-2 pt-sm-0">
                                                <p class="text-muted" data-i18n="open">Open</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="recentUsers">Recent users</h4>

                    <div class="preview-list">
                        @forelse ($recentUsers as $user)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-dark rounded-circle">
                                        <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                </div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $user->name }}</p>
                                    @php
                                        $roleMeta = $roleTranslations[$user->role] ?? ['key' => 'roleUser', 'label' => $user->role];
                                        $statusMeta = $statusTranslations[$user->status] ?? ['key' => 'statusUnknown', 'label' => $user->status];
                                    @endphp
                                    <p class="text-muted mb-0">
                                        <span data-i18n="{{ $roleMeta['key'] }}">{{ $roleMeta['label'] }}</span>
                                        <span aria-hidden="true">&middot;</span>
                                        <span data-i18n="{{ $statusMeta['key'] }}">{{ $statusMeta['label'] }}</span>
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0" data-i18n="noUsersCreatedYet">No users have been created yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
