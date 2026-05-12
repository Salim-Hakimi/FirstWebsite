<!DOCTYPE html>
<html lang="en" dir="ltr" data-theme="dark">
    @php
        $currentUser = auth()->user();
        $homeRoute = $currentUser?->role === \App\Models\User::ROLE_LIBRARIAN
            ? route('library.index')
            : ($currentUser?->canAccessAdmin() ? route('admin.dashboard') : route('dashboard'));
        $isLibrarian = $currentUser?->role === \App\Models\User::ROLE_LIBRARIAN;
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
        $roleMeta = $roleKeys[$currentUser->role ?? ''] ?? ['key' => 'roleUser', 'label' => 'User'];
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>@yield('title', 'Fanous Admin')</title>
        <script>
            (function () {
                const lang = localStorage.getItem('fanous.lang') || 'en';
                const theme = localStorage.getItem('fanous.theme') || '{{ $currentUser->theme === 'light' ? 'light' : 'dark' }}';
                document.documentElement.lang = lang === 'fa' ? 'fa' : 'en';
                document.documentElement.dir = lang === 'fa' ? 'rtl' : 'ltr';
                document.documentElement.dataset.theme = theme === 'light' ? 'light' : 'dark';
            })();
        </script>
        <link rel="stylesheet" href="{{ asset('corona/assets/css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/corona-admin-overrides.css') }}">
    </head>
    <body>
        <div class="container-scroller">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
                    <a class="sidebar-brand brand-logo corona-brand" href="{{ $homeRoute }}">Fanous</a>
                    <a class="sidebar-brand brand-logo-mini corona-brand-mini" href="{{ $homeRoute }}">F</a>
                </div>

                <ul class="nav">
                    <li class="nav-item profile">
                        <div class="profile-desc">
                            <div class="profile-pic">
                                <div class="count-indicator">
                                    @if ($currentUser->profile_photo_path)
                                        <img class="fanous-avatar fanous-avatar-img" src="{{ asset('storage/'.$currentUser->profile_photo_path) }}" alt="{{ $currentUser->name }}">
                                    @else
                                        <span class="fanous-avatar">{{ strtoupper(substr($currentUser->name ?? 'F', 0, 1)) }}</span>
                                    @endif
                                    <span class="count bg-success"></span>
                                </div>
                                <div class="profile-name">
                                    <h5 class="mb-0 font-weight-normal">{{ $currentUser->name }}</h5>
                                    <span data-i18n="{{ $roleMeta['key'] }}">{{ $roleMeta['label'] }}</span>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item nav-category">
                        <span class="nav-link" data-i18n="navigation">Navigation</span>
                    </li>

                    <li class="nav-item menu-items {{ request()->routeIs('admin.dashboard') || request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ $homeRoute }}">
                            <span class="menu-icon"><span class="menu-letter">D</span></span>
                            <span class="menu-title" data-i18n="dashboard">Dashboard</span>
                        </a>
                    </li>

                    @if ($currentUser->canManageUsers())
                        <li class="nav-item menu-items {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">
                                <span class="menu-icon"><span class="menu-letter">U</span></span>
                                <span class="menu-title" data-i18n="usersAndRoles">Users & Roles</span>
                            </a>
                        </li>
                    @endif

                    @if ($currentUser->canManageFinance())
                        <li class="nav-item menu-items {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.finance.index') }}">
                                <span class="menu-icon"><span class="menu-letter">F</span></span>
                                <span class="menu-title">Dorm Finance</span>
                            </a>
                        </li>
                    @endif

                    @if ($currentUser->canAccessAdmin())
                        <li class="nav-item menu-items {{ request()->routeIs('dorm.rooms.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('dorm.rooms.index') }}">
                                <span class="menu-icon"><span class="menu-letter">R</span></span>
                                <span class="menu-title" data-i18n="dormRooms">Dorm Rooms</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::dormRecordViewerRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('dorm.students.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('dorm.students.index') }}">
                            <span class="menu-icon"><span class="menu-letter">S</span></span>
                            <span class="menu-title" data-i18n="dormStudents">Dorm Students</span>
                        </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::studentRepresentativeRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('representative.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('representative.index') }}">
                                <span class="menu-icon"><span class="menu-letter">R</span></span>
                                <span class="menu-title" data-i18n="representative">Representative</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::purchaserRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('purchaser.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('purchaser.index') }}">
                                <span class="menu-icon"><span class="menu-letter">F</span></span>
                                <span class="menu-title" data-i18n="finance">Finance</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::libraryViewerRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('library.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('library.index') }}">
                                <span class="menu-icon"><span class="menu-letter">L</span></span>
                                <span class="menu-title" data-i18n="library">Library</span>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item menu-items {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('settings.edit') }}">
                            <span class="menu-icon"><span class="menu-letter">A</span></span>
                            <span class="menu-title" data-i18n="accountSettings">Account Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="container-fluid page-body-wrapper">
                <nav class="navbar p-0 fixed-top d-flex flex-row">
                    <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
                        <a class="navbar-brand brand-logo-mini corona-brand-mini" href="{{ $homeRoute }}">F</a>
                    </div>

                    <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
                        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                            <span class="fanous-menu-lines"></span>
                        </button>

                        <ul class="navbar-nav w-100">
                            <li class="nav-item w-100">
                                <form class="nav-link mt-2 mt-md-0 d-none d-lg-flex search" method="GET" action="{{ $isLibrarian ? route('library.index') : $homeRoute }}">
                                    <input
                                        type="text"
                                        class="form-control"
                                        @if ($isLibrarian)
                                            name="q"
                                            value="{{ request('q') }}"
                                            placeholder="Search by name, phone, ID, code, or education"
                                            data-i18n-placeholder="searchLibraryMembers"
                                        @else
                                            placeholder="Search legal records"
                                            data-i18n-placeholder="searchPlaceholder"
                                        @endif
                                    >
                                </form>
                            </li>
                        </ul>

                        <ul class="navbar-nav navbar-nav-right">
                            @if ($currentUser->canManageUsers())
                                <li class="nav-item d-none d-lg-block">
                                    <a class="nav-link btn btn-success create-new-button" href="{{ route('admin.users.create') }}"><span aria-hidden="true">+</span> <span data-i18n="createNewUser">Create New User</span></a>
                                </li>
                            @elseif ($isLibrarian)
                                <li class="nav-item d-none d-lg-block">
                                    @if (request()->routeIs('library.index'))
                                        <button class="nav-link btn btn-success create-new-button" type="button" data-library-panel-trigger="new-library-member" aria-controls="new-library-member" aria-expanded="false"><span aria-hidden="true">+</span> <span data-i18n="registerMember">Register member</span></button>
                                    @else
                                        <a class="nav-link btn btn-success create-new-button" href="{{ route('library.index') }}#new-library-member"><span aria-hidden="true">+</span> <span data-i18n="registerMember">Register member</span></a>
                                    @endif
                                </li>
                            @endif
                            <li class="nav-item fanous-toolbar-item">
                                <button class="fanous-tool-button" type="button" data-lang-toggle>
                                    <span class="fanous-tool-icon fanous-lang-icon">A</span>
                                    <span data-lang-label>Persian</span>
                                </button>
                            </li>
                            <li class="nav-item fanous-toolbar-item">
                                <button class="fanous-tool-button" type="button" data-theme-toggle>
                                    <span class="fanous-tool-icon fanous-moon-icon"></span>
                                    <span data-theme-label data-i18n="darkMode">Dark mode</span>
                                </button>
                            </li>
                            <li class="nav-item dropdown border-left">
                                <span class="nav-link count-indicator">
                                    <span class="fanous-top-dot"></span>
                                </span>
                            </li>
                            <li class="nav-item dropdown">
                                <form method="POST" action="{{ route('logout') }}" class="m-0">
                                    @csrf
                                    <button class="nav-link btn btn-link logout-btn" type="submit" data-i18n="logout">Log out</button>
                                </form>
                            </li>
                        </ul>

                        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                            <span class="fanous-menu-lines"></span>
                        </button>
                    </div>
                </nav>

                <div class="main-panel">
                    <div class="content-wrapper">
                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @yield('content')
                    </div>

                    <footer class="footer">
                        <div class="d-sm-flex justify-content-center justify-content-sm-between">
                            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block" data-i18n="fanousDormitoryManagementSystem">Fanous Dormitory Management System</span>
                            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center" data-i18n="adminDashboard">Admin dashboard</span>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/fanous-i18n.js') }}"></script>
        <script>
            document.addEventListener('click', function (event) {
                const minimize = event.target.closest('[data-toggle="minimize"]');
                const offcanvas = event.target.closest('[data-toggle="offcanvas"]');

                if (minimize) {
                    document.body.classList.toggle('sidebar-icon-only');
                }

                if (offcanvas) {
                    document.querySelector('.sidebar-offcanvas')?.classList.toggle('active');
                }
            });
        </script>
    </body>
</html>
