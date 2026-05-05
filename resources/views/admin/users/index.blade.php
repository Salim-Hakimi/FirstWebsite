@extends('admin.layout')

@section('title', 'Users & Roles - Fanous Admin')

@section('content')
    @php
        $activeCount = $users->where('status', \App\Models\User::STATUS_ACTIVE)->count();
        $pendingCount = $users->where('status', \App\Models\User::STATUS_PENDING)->count();
        $suspendedCount = $users->where('status', \App\Models\User::STATUS_SUSPENDED)->count();
        $adminCount = $users->filter(fn ($user) => in_array($user->role, \App\Models\User::managementRoles(), true))->count();

        $roleNames = [
            \App\Models\User::ROLE_OWNER => 'Owner',
            \App\Models\User::ROLE_MANAGER => 'Manager',
            \App\Models\User::ROLE_ADMIN => 'Admin',
            \App\Models\User::ROLE_GUARD => 'Guard',
            \App\Models\User::ROLE_STUDENT_REPRESENTATIVE => 'Student Representative',
            \App\Models\User::ROLE_PURCHASER => 'Purchaser',
            \App\Models\User::ROLE_LIBRARIAN => 'Librarian',
            \App\Models\User::ROLE_COOK => 'Cook',
            \App\Models\User::ROLE_DORM_STUDENT => 'Dorm Student',
            \App\Models\User::ROLE_LIBRARY_MEMBER => 'Library Member',
            \App\Models\User::ROLE_APPLICANT => 'Applicant',
        ];

        $statusNames = [
            \App\Models\User::STATUS_ACTIVE => 'Active',
            \App\Models\User::STATUS_PENDING => 'Pending',
            \App\Models\User::STATUS_SUSPENDED => 'Suspended',
        ];

        $roleKeys = [
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

        $statusKeys = [
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
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1" data-i18n="usersAndRoles">Users & Roles</h3>
                            <p class="mb-0 text-white-50" data-i18n="usersRolesDescription">Create staff accounts, assign roles, and control who can access each Fanous module.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-outline-light btn-rounded"><span aria-hidden="true">+</span> <span data-i18n="addNewUser">Add new user</span></a>
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
                            <h3 class="mb-0">{{ $users->count() }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-success"><span class="metric-icon">U</span></div>
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
                            <h3 class="mb-0">{{ $activeCount }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-success"><span class="metric-icon">A</span></div>
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
                            <h3 class="mb-0">{{ $pendingCount }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-warning"><span class="metric-icon">P</span></div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal" data-i18n="pendingReview">Pending review</h6>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <h3 class="mb-0">{{ $adminCount }}</h3>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-danger"><span class="metric-icon">M</span></div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal" data-i18n="managementUsers">Management users</h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="accessOverview">Access Overview</h4>
                    <div class="preview-list">
                        <div class="preview-item border-bottom">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-success"><span>A</span></div>
                            </div>
                            <div class="preview-item-content">
                                <p class="preview-subject mb-1" data-i18n="statusActive">Active</p>
                                <p class="text-muted mb-0">{{ $activeCount }} <span data-i18n="usersCanSignIn">users can sign in</span></p>
                            </div>
                        </div>
                        <div class="preview-item border-bottom">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-warning"><span>P</span></div>
                            </div>
                            <div class="preview-item-content">
                                <p class="preview-subject mb-1" data-i18n="statusPending">Pending</p>
                                <p class="text-muted mb-0">{{ $pendingCount }} <span data-i18n="accountsNeedReview">accounts need review</span></p>
                            </div>
                        </div>
                        <div class="preview-item">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-danger"><span>S</span></div>
                            </div>
                            <div class="preview-item-content">
                                <p class="preview-subject mb-1" data-i18n="statusSuspended">Suspended</p>
                                <p class="text-muted mb-0">{{ $suspendedCount }} <span data-i18n="accountsAreBlocked">accounts are blocked</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <div>
                            <h4 class="card-title mb-1" data-i18n="staffDirectory">Staff directory</h4>
                            <p class="text-muted mb-0" data-i18n="staffDirectoryDescription">Every account listed here has a role and an access status.</p>
                        </div>
                        <a class="btn btn-primary mt-3 mt-md-0" href="{{ route('admin.users.create') }}" data-i18n="createUser">Create user</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th data-i18n="user">User</th>
                                    <th data-i18n="contact">Contact</th>
                                    <th data-i18n="role">Role</th>
                                    <th data-i18n="status">Status</th>
                                    <th data-i18n="created">Created</th>
                                    <th data-i18n="action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if ($user->profile_photo_path)
                                                    <img class="user-table-avatar user-table-avatar-img" src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}">
                                                @else
                                                    <div class="user-table-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                                @endif
                                                <div>
                                                    <span class="font-weight-bold">{{ $user->name }}</span>
                                                    @if (auth()->id() === $user->id)
                                                        <div class="text-muted small" data-i18n="currentAccount">Current account</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>{{ $user->email }}</div>
                                            @if ($user->phone)
                                                <span class="text-muted">{{ $user->phone }}</span>
                                            @else
                                                <span class="text-muted" data-i18n="noPhoneRecorded">No phone recorded</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php $roleMeta = $roleKeys[$user->role] ?? ['key' => 'roleUser', 'label' => $user->role]; @endphp
                                            <span data-i18n="{{ $roleMeta['key'] }}">{{ $roleMeta['label'] }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = match ($user->status) {
                                                    \App\Models\User::STATUS_ACTIVE => 'badge-outline-success',
                                                    \App\Models\User::STATUS_PENDING => 'badge-outline-warning',
                                                    \App\Models\User::STATUS_SUSPENDED => 'badge-outline-danger',
                                                    default => 'badge-outline-secondary',
                                                };
                                            @endphp
                                            @php $statusMeta = $statusKeys[$user->status] ?? ['key' => 'statusUnknown', 'label' => $user->status]; @endphp
                                            <span class="badge {{ $badgeClass }}" data-i18n="{{ $statusMeta['key'] }}">{{ $statusMeta['label'] }}</span>
                                        </td>
                                        <td>{{ $user->created_at?->format('Y-m-d') }}</td>
                                        <td>
                                            @if (auth()->id() !== $user->id)
                                                <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.users.edit', $user) }}" data-i18n="edit">Edit</a>
                                            @else
                                                <span class="text-muted" data-i18n="locked">Locked</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4" data-i18n="noUsersCreatedYet">No users have been created yet.</td>
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
