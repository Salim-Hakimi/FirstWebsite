<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="{{ auth()->user()?->theme === 'light' ? 'light' : 'dark' }}">
    @php
        $currentUser = auth()->user();
        $homeRoute = $currentUser?->canAccessAdmin() ? route('admin.dashboard') : route('dashboard');
        $isLibrarian = $currentUser?->role === \App\Models\User::ROLE_LIBRARIAN;
        $roleKeys = [
            \App\Models\User::ROLE_ADMIN => ['key' => 'roleAdmin', 'label' => 'ادمین'],
            \App\Models\User::ROLE_GUARD => ['key' => 'roleGuard', 'label' => 'گارد'],
            \App\Models\User::ROLE_STUDENT_REPRESENTATIVE => ['key' => 'roleStudentRepresentative', 'label' => 'نماینده'],
            \App\Models\User::ROLE_PURCHASER => ['key' => 'rolePurchaser', 'label' => 'خرج‌آور'],
            \App\Models\User::ROLE_LIBRARIAN => ['key' => 'roleLibrarian', 'label' => 'کتاب‌دار'],
        ];
        $roleMeta = $roleKeys[$currentUser->role ?? ''] ?? ['key' => 'roleUser', 'label' => 'کاربر'];
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'ادمین فانوس')</title>
        <script>
            (function () {
                const theme = localStorage.getItem('fanous.theme') || '{{ $currentUser->theme === 'light' ? 'light' : 'dark' }}';
                localStorage.setItem('fanous.lang', 'fa');
                document.documentElement.lang = 'fa';
                document.documentElement.dir = 'rtl';
                document.documentElement.dataset.theme = theme === 'light' ? 'light' : 'dark';
            })();
        </script>
        <link rel="stylesheet" href="{{ asset('corona/assets/css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/corona-admin-overrides.css') }}">
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite('resources/js/app.js')
        @endif
    </head>
    <body class="{{ request()->boolean('fanous_modal') ? 'fanous-embedded-modal' : '' }}">
        <div class="container-scroller">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
                    <a class="sidebar-brand brand-logo corona-brand brand" href="{{ $homeRoute }}">
                        <img src="{{ asset('logo/logo.jpg') }}" alt="Fanous Logo" class="brand-logo-img">
                        <span class="brand-name">فانوس</span>
                    </a>
                    <a class="sidebar-brand brand-logo-mini corona-brand-mini brand brand-mini" href="{{ $homeRoute }}">
                        <img src="{{ asset('logo/logo.jpg') }}" alt="Fanous Logo" class="brand-logo-img">
                    </a>
                </div>

                <ul class="nav">
                    <li class="nav-item profile">
                        <div class="profile-desc">
                            <div class="profile-pic">
                                <div class="count-indicator">
                                    @if ($currentUser->profile_photo_path)
                                        <img class="fanous-avatar fanous-avatar-img" src="{{ asset('storage/'.$currentUser->profile_photo_path) }}" alt="{{ $currentUser->name }}">
                                    @else
                                        <span class="fanous-avatar">{{ mb_substr($currentUser->name ?? 'ف', 0, 1) }}</span>
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
                        <span class="nav-link" data-i18n="navigation">ناوبری</span>
                    </li>

                    <li class="nav-item menu-items {{ request()->routeIs('admin.dashboard') || request()->routeIs('dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ $homeRoute }}">
                            <span class="menu-icon"><x-ds.icon name="dashboard" /></span>
                            <span class="menu-title" data-i18n="dashboard">داشبورد</span>
                        </a>
                    </li>

                    @if ($currentUser->canManageUsers())
                        <li class="nav-item menu-items {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.users.index') }}">
                                <span class="menu-icon"><x-ds.icon name="users" /></span>
                                <span class="menu-title" data-i18n="usersAndRoles">کاربران و نقش‌ها</span>
                            </a>
                        </li>
                    @endif

                    @if ($currentUser->canAccessAdmin())
                        <li class="nav-item menu-items {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('admin.finance.index') }}">
                                <span class="menu-icon"><x-ds.icon name="cash" /></span>
                                <span class="menu-title">مالی لیلیه</span>
                            </a>
                        </li>
                    @endif

                    @if ($currentUser->canAccessAdmin())
                        <li class="nav-item menu-items {{ request()->routeIs('dorm.rooms.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('dorm.rooms.index') }}">
                                <span class="menu-icon"><x-ds.icon name="building" /></span>
                                <span class="menu-title" data-i18n="dormRooms">اتاق‌های لیلیه</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::dormRecordViewerRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('dorm.students.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('dorm.students.index') }}">
                                <span class="menu-icon"><x-ds.icon name="users" /></span>
                                <span class="menu-title" data-i18n="dormStudents">شاگردان لیلیه</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::studentRepresentativeRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('representative.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('representative.index') }}">
                                <span class="menu-icon"><x-ds.icon name="user" /></span>
                                <span class="menu-title" data-i18n="representative">نماینده</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::purchaserRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('purchaser.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('purchaser.index') }}">
                                <span class="menu-icon"><x-ds.icon name="cash-minus" /></span>
                                <span class="menu-title" data-i18n="finance">مالی</span>
                            </a>
                        </li>
                    @endif

                    @if (in_array($currentUser->role, \App\Models\User::libraryViewerRoles(), true))
                        <li class="nav-item menu-items {{ request()->routeIs('library.index', 'library.inventory.*', 'library.fee-reminders.*', 'library.members.*', 'library.books.*', 'library.loans.*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('library.index') }}">
                                <span class="menu-icon"><x-ds.icon name="library" /></span>
                                <span class="menu-title" data-i18n="library">کتابخانه</span>
                            </a>
                        </li>
                        @if ($isLibrarian)
                            <li class="nav-item menu-items {{ request()->routeIs('library.finance.*') ? 'active' : '' }}">
                                <a class="nav-link" href="{{ route('library.finance.index') }}#library-finance-ledger">
                                    <span class="menu-icon"><x-ds.icon name="cash" /></span>
                                    <span class="menu-title" data-i18n="finance">مالی</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    <li class="nav-item menu-items {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('settings.edit') }}">
                            <span class="menu-icon"><x-ds.icon name="settings" /></span>
                            <span class="menu-title" data-i18n="accountSettings">تنظیمات حساب</span>
                        </a>
                    </li>
                </ul>

                <div class="developer-credit developer-credit-sidebar">
                    <span>طراحی و توسعه توسط</span>
                    <img src="{{ asset('logo/company-logo-small.png') }}" alt="Company Logo">
                </div>
            </nav>

            <div class="container-fluid page-body-wrapper">
                <nav class="navbar p-0 fixed-top d-flex flex-row">
                    <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
                        <a class="navbar-brand brand-logo-mini corona-brand-mini brand brand-mini" href="{{ $homeRoute }}">
                            <img src="{{ asset('logo/logo.jpg') }}" alt="Fanous Logo" class="brand-logo-img">
                        </a>
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
                                            placeholder="جستجو بر اساس نام، شماره تماس، ID، کود یا تحصیل"
                                            data-i18n-placeholder="searchLibraryMembers"
                                        @else
                                            placeholder="جستجو در ثبت‌ها"
                                            data-i18n-placeholder="searchPlaceholder"
                                        @endif
                                    >
                                </form>
                            </li>
                        </ul>

                        <ul class="navbar-nav navbar-nav-right">
                            @if ($currentUser->canManageUsers())
                                <li class="nav-item d-none d-lg-block">
                                    <a class="nav-link btn btn-success create-new-button" href="{{ route('dorm.students.create') }}" data-fanous-page-modal data-modal-title="ثبت شاگرد جدید"><x-ds.icon name="plus" /> <span>ثبت شاگرد جدید</span></a>
                                </li>
                            @elseif ($isLibrarian)
                                <li class="nav-item d-none d-lg-block">
                                    @if (request()->routeIs('library.index'))
                                        <button class="nav-link btn btn-success create-new-button" type="button" data-library-panel-trigger="new-library-member" aria-controls="new-library-member" aria-expanded="false"><x-ds.icon name="plus" /> <span data-i18n="registerMember">ثبت عضو</span></button>
                                    @else
                                        <a class="nav-link btn btn-success create-new-button" href="{{ route('library.index') }}#new-library-member"><x-ds.icon name="plus" /> <span data-i18n="registerMember">ثبت عضو</span></a>
                                    @endif
                                </li>
                            @endif
                            <li class="nav-item fanous-toolbar-item">
                                <button class="fanous-tool-button" type="button" data-theme-toggle>
                                    <span class="fanous-tool-icon fanous-moon-icon"></span>
                                    <span data-theme-label>حالت روشن</span>
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
                                    <button class="nav-link btn btn-link logout-btn" type="submit"><x-ds.icon name="logout" /> <span data-i18n="logout">خروج</span></button>
                                </form>
                            </li>
                        </ul>

                        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                            <span class="fanous-menu-lines"></span>
                        </button>
                    </div>
                </nav>

                <div class="main-panel">
                    <div class="content-wrapper @yield('content_wrapper_class')">
                        @if (session('status') || session('error'))
                            <noscript>
                                @if (session('status'))
                                    <div class="alert alert-success">{{ session('status') }}</div>
                                @endif

                                @if (session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                            </noscript>
                        @endif

                        @yield('content')
                    </div>

                    <footer class="footer">
                        <div class="d-sm-flex justify-content-center justify-content-sm-between">
                            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block" data-i18n="fanousDormitoryManagementSystem">سیستم مدیریت لیلیه فانوس</span>
                            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center developer-credit developer-credit-footer">
                                <span>طراحی و توسعه توسط</span>
                                <img src="{{ asset('logo/company-logo-small.png') }}" alt="Company Logo">
                            </span>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
        @php
            $fanousFlashPayload = [
                'status' => session('status'),
                'error' => session('error'),
                'validation' => $errors->any() ? $errors->first() : null,
            ];
        @endphp
        <script src="{{ asset('js/fanous-i18n.js') }}"></script>
        <script src="{{ asset('js/fanous-page-modal.js') }}"></script>
        <script>
            (function () {
                const flash = {!! json_encode($fanousFlashPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
                let toastRegion = null;

                function createElement(tag, className, text) {
                    const element = document.createElement(tag);

                    if (className) {
                        element.className = className;
                    }

                    if (typeof text === 'string') {
                        element.textContent = text;
                    }

                    return element;
                }

                function resolveAlert(result) {
                    return {
                        isConfirmed: result === 'confirm',
                        isDenied: result === 'deny',
                        isDismissed: result === 'cancel',
                        dismiss: result === 'cancel' ? 'cancel' : undefined
                    };
                }

                function closeAlert(backdrop, result, resolve) {
                    backdrop.classList.remove('is-visible');
                    document.body.classList.remove('fanous-alert-open');

                    window.setTimeout(function () {
                        backdrop.remove();
                        resolve(resolveAlert(result));
                    }, 160);
                }

                function toast(options) {
                    if (!toastRegion) {
                        toastRegion = createElement('div', 'fanous-toast-region');
                        toastRegion.setAttribute('aria-live', 'polite');
                        document.body.appendChild(toastRegion);
                    }

                    const item = createElement('div', 'fanous-toast fanous-toast--' + (options.icon || 'info'));
                    const icon = createElement('span', 'fanous-toast__icon', options.icon === 'success' ? '✓' : options.icon === 'error' ? '!' : 'i');
                    const textWrap = createElement('div', 'fanous-toast__body');
                    textWrap.appendChild(createElement('strong', null, options.title || 'پیام سیستم'));

                    if (options.text) {
                        textWrap.appendChild(createElement('span', null, options.text));
                    }

                    item.appendChild(icon);
                    item.appendChild(textWrap);
                    toastRegion.appendChild(item);

                    window.requestAnimationFrame(function () {
                        item.classList.add('is-visible');
                    });

                    window.setTimeout(function () {
                        item.classList.remove('is-visible');
                        window.setTimeout(function () { item.remove(); }, 180);
                    }, options.timer || 3800);

                    return Promise.resolve(resolveAlert('confirm'));
                }

                function fire(options) {
                    options = Object.assign({
                        icon: 'info',
                        title: 'پیام سیستم',
                        text: '',
                        confirmButtonText: 'تایید',
                        denyButtonText: 'ذخیره نشود',
                        cancelButtonText: 'لغو',
                        showCancelButton: false,
                        showDenyButton: false
                    }, options || {});

                    if (options.toast) {
                        return toast(options);
                    }

                    return new Promise(function (resolve) {
                        const backdrop = createElement('div', 'fanous-alert-backdrop');
                        const dialog = createElement('section', 'fanous-alert fanous-alert--' + options.icon);
                        const icon = createElement('div', 'fanous-alert__icon', options.icon === 'success' ? '✓' : options.icon === 'error' ? '!' : options.icon === 'warning' ? '!' : 'i');
                        const title = createElement('h2', null, options.title);
                        const text = createElement('p', null, options.text);
                        const actions = createElement('div', 'fanous-alert__actions');
                        const confirmButton = createElement('button', 'fanous-alert__button fanous-alert__button--confirm', options.confirmButtonText);
                        let isClosing = false;

                        function finish(result) {
                            if (isClosing) {
                                return;
                            }

                            isClosing = true;
                            document.removeEventListener('keydown', onKeydown);
                            closeAlert(backdrop, result, resolve);
                        }

                        dialog.setAttribute('role', 'dialog');
                        dialog.setAttribute('aria-modal', 'true');
                        dialog.setAttribute('dir', 'rtl');
                        confirmButton.type = 'button';
                        dialog.appendChild(icon);
                        dialog.appendChild(title);

                        if (options.text) {
                            dialog.appendChild(text);
                        }

                        if (options.footer) {
                            dialog.appendChild(createElement('small', 'fanous-alert__footer', options.footer));
                        }

                        if (options.showCancelButton) {
                            const cancelButton = createElement('button', 'fanous-alert__button fanous-alert__button--cancel', options.cancelButtonText);
                            cancelButton.type = 'button';
                            cancelButton.addEventListener('click', function () {
                                finish('cancel');
                            });
                            actions.appendChild(cancelButton);
                        }

                        if (options.showDenyButton) {
                            const denyButton = createElement('button', 'fanous-alert__button fanous-alert__button--deny', options.denyButtonText);
                            denyButton.type = 'button';
                            denyButton.addEventListener('click', function () {
                                finish('deny');
                            });
                            actions.appendChild(denyButton);
                        }

                        confirmButton.addEventListener('click', function () {
                            finish('confirm');
                        });
                        actions.appendChild(confirmButton);
                        dialog.appendChild(actions);
                        backdrop.appendChild(dialog);
                        document.body.appendChild(backdrop);
                        document.body.classList.add('fanous-alert-open');

                        window.requestAnimationFrame(function () {
                            backdrop.classList.add('is-visible');
                            confirmButton.focus({ preventScroll: true });
                        });

                        backdrop.addEventListener('click', function (event) {
                            if (event.target === backdrop && options.showCancelButton) {
                                finish('cancel');
                            }
                        });

                        function onKeydown(event) {
                            if (event.key === 'Escape' && options.showCancelButton) {
                                finish('cancel');
                            }
                        }

                        document.addEventListener('keydown', onKeydown);
                    });
                }

                window.FanousAlert = {
                    fire: fire,
                    success: function (text) {
                        return fire({
                            icon: 'success',
                            title: 'انجام شد',
                            text: text,
                            toast: true
                        });
                    },
                    error: function (text) {
                        return fire({
                            icon: 'error',
                            title: 'مشکل پیش آمد',
                            text: text || 'لطفاً دوباره تلاش کنید.',
                            confirmButtonText: 'فهمیدم'
                        });
                    },
                    confirmSave: function () {
                        return fire({
                            icon: 'question',
                            title: 'معلومات ذخیره شود؟',
                            text: 'پس از تایید، معلومات این فرم در سیستم ثبت می‌شود.',
                            showDenyButton: true,
                            showCancelButton: true,
                            confirmButtonText: 'ذخیره',
                            denyButtonText: 'ذخیره نشود',
                            cancelButtonText: 'لغو'
                        });
                    },
                    confirmDelete: function () {
                        return fire({
                            icon: 'warning',
                            title: 'آیا مطمئن هستید؟',
                            text: 'این عملیات قابل برگشت نیست.',
                            showCancelButton: true,
                            confirmButtonText: 'بلی، حذف شود',
                            cancelButtonText: 'لغو'
                        });
                    }
                };

                document.addEventListener('DOMContentLoaded', function () {
                    if (flash.status) {
                        window.FanousAlert.success(flash.status);
                    }

                    if (flash.error) {
                        window.FanousAlert.error(flash.error);
                    } else if (flash.validation) {
                        window.FanousAlert.error(flash.validation);
                    }
                });
            })();

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

            document.addEventListener('DOMContentLoaded', function () {
                function toPersianDigits(text) {
                    return String(text).replace(/[0-9]/g, function (digit) {
                        return '۰۱۲۳۴۵۶۷۸۹'[Number(digit)];
                    });
                }

                function gregorianToJalali(gy, gm, gd) {
                    const gDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                    const jDays = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
                    gy -= 1600;
                    gm -= 1;
                    gd -= 1;
                    let gDayNo = 365 * gy + Math.floor((gy + 3) / 4) - Math.floor((gy + 99) / 100) + Math.floor((gy + 399) / 400);

                    for (let i = 0; i < gm; i += 1) {
                        gDayNo += gDays[i];
                    }

                    if (gm > 1 && ((gy + 1600) % 4 === 0 && ((gy + 1600) % 100 !== 0 || (gy + 1600) % 400 === 0))) {
                        gDayNo += 1;
                    }

                    gDayNo += gd;
                    let jDayNo = gDayNo - 79;
                    const jNp = Math.floor(jDayNo / 12053);
                    jDayNo %= 12053;
                    let jy = 979 + 33 * jNp + 4 * Math.floor(jDayNo / 1461);
                    jDayNo %= 1461;

                    if (jDayNo >= 366) {
                        jy += Math.floor((jDayNo - 1) / 365);
                        jDayNo = (jDayNo - 1) % 365;
                    }

                    let i = 0;
                    for (; i < 11 && jDayNo >= jDays[i]; i += 1) {
                        jDayNo -= jDays[i];
                    }

                    return [jy, i + 1, jDayNo + 1];
                }

                function solarDateText(year, month, day) {
                    const parts = gregorianToJalali(Number(year), Number(month), Number(day));

                    return toPersianDigits(
                        String(parts[0]).padStart(4, '0') + '/' +
                        String(parts[1]).padStart(2, '0') + '/' +
                        String(parts[2]).padStart(2, '0') + ' ه.ش'
                    );
                }

                function localizeVisibleDates(root) {
                    const walker = document.createTreeWalker(root || document.body, NodeFilter.SHOW_TEXT, {
                        acceptNode: function (node) {
                            const parent = node.parentElement;

                            if (!parent || parent.closest('script, style, textarea, input, select, code, pre')) {
                                return NodeFilter.FILTER_REJECT;
                            }

                            return /\b20\d{2}[-\/]\d{2}[-\/]\d{2}\b/.test(node.nodeValue || '')
                                ? NodeFilter.FILTER_ACCEPT
                                : NodeFilter.FILTER_REJECT;
                        }
                    });

                    const nodes = [];

                    while (walker.nextNode()) {
                        nodes.push(walker.currentNode);
                    }

                    nodes.forEach(function (node) {
                        node.nodeValue = node.nodeValue.replace(/\b(20\d{2})[-\/](\d{2})[-\/](\d{2})\b/g, function (_, year, month, day) {
                            return solarDateText(year, month, day);
                        });
                    });
                }

                function enhanceDateInputs(root) {
                    (root || document).querySelectorAll?.('input[type="date"]').forEach(function (input) {
                        if (input.dataset.fanousSolarDate === 'ready') {
                            return;
                        }

                        input.dataset.fanousSolarDate = 'ready';

                        const preview = document.createElement('small');
                        preview.className = 'fanous-date-preview text-muted';
                        preview.setAttribute('aria-live', 'polite');
                        input.insertAdjacentElement('afterend', preview);

                        function syncPreview() {
                            const match = String(input.value || '').match(/^(20\d{2})-(\d{2})-(\d{2})$/);
                            preview.textContent = match ? solarDateText(match[1], match[2], match[3]) : '';
                            preview.hidden = !match;
                        }

                        input.addEventListener('input', syncPreview);
                        input.addEventListener('change', syncPreview);
                        syncPreview();
                    });
                }

                window.FanousDate = { solarDateText, localizeVisibleDates, enhanceDateInputs };
                localizeVisibleDates(document.body);
                enhanceDateInputs(document.body);

                new MutationObserver(function (mutations) {
                    mutations.forEach(function (mutation) {
                        mutation.addedNodes.forEach(function (node) {
                            if (node.nodeType === Node.ELEMENT_NODE) {
                                localizeVisibleDates(node);
                                enhanceDateInputs(node);
                            }
                        });
                    });
                }).observe(document.body, { childList: true, subtree: true });

                const requiredSelector = 'input[required], select[required], textarea[required]';

                function requiredFields(form) {
                    return Array.from(form.querySelectorAll(requiredSelector)).filter(function (field) {
                        return !field.disabled && field.type !== 'hidden';
                    });
                }

                function isFieldComplete(field, form) {
                    if (field.type === 'radio') {
                        return Array.from(form.querySelectorAll('input[type="radio"]')).some(function (radio) {
                            return radio.name === field.name && radio.checked;
                        });
                    }

                    if (field.type === 'checkbox') {
                        return field.checked;
                    }

                    if (field.type === 'file') {
                        return field.files && field.files.length > 0;
                    }

                    return field.checkValidity() && String(field.value || '').trim() !== '';
                }

                function submitButtons(form) {
                    return Array.from(document.querySelectorAll('button[type="submit"], input[type="submit"]')).filter(function (button) {
                        return button.form === form;
                    });
                }

                function firstIncompleteField(form, scope) {
                    return requiredFields(form).find(function (field) {
                        return (! scope || scope.contains(field)) && ! isFieldComplete(field, form);
                    });
                }

                function navigableFields(form) {
                    return Array.from(form.querySelectorAll('input, select, textarea, button')).filter(function (field) {
                        const type = String(field.type || '').toLowerCase();

                        return !field.disabled
                            && type !== 'hidden'
                            && type !== 'submit'
                            && type !== 'button'
                            && type !== 'reset'
                            && field.offsetParent !== null;
                    });
                }

                function moveFormFocus(form, currentField, direction) {
                    const fields = navigableFields(form);
                    const currentIndex = fields.indexOf(currentField);

                    if (currentIndex === -1 || fields.length < 2) {
                        return false;
                    }

                    const nextIndex = (currentIndex + direction + fields.length) % fields.length;
                    const nextField = fields[nextIndex];

                    nextField.focus({ preventScroll: true });

                    if (typeof nextField.select === 'function' && ['text', 'email', 'number', 'tel', 'search', 'url'].includes(String(nextField.type || '').toLowerCase())) {
                        nextField.select();
                    }

                    return true;
                }

                function isSearchForm(form) {
                    return String(form.method || 'GET').toUpperCase() === 'GET'
                        || form.dataset.searchForm === 'true'
                        || form.matches('[role="search"], .fanous-student-filters, .fanous-filter-form, .fanous-filter-grid');
                }

                function setupFormKeyboardNavigation(form) {
                    if (form.dataset.fanousKeyboardNavigation === 'ready') {
                        return;
                    }

                    form.dataset.fanousKeyboardNavigation = 'ready';
                    form.addEventListener('keydown', function (event) {
                        if (event.defaultPrevented) {
                            return;
                        }

                        const target = event.target;

                        if (! (target instanceof HTMLElement) || ! target.matches('input, select, textarea')) {
                            return;
                        }

                        const isTextarea = target.matches('textarea');
                        const isEnterMove = event.key === 'Enter' && !isTextarea && !isSearchForm(form);
                        const isArrowMove = event.key === 'ArrowDown' || event.key === 'ArrowUp';

                        if (! isEnterMove && ! isArrowMove) {
                            return;
                        }

                        const direction = event.shiftKey || event.key === 'ArrowUp' ? -1 : 1;

                        if (moveFormFocus(form, target, direction)) {
                            event.preventDefault();
                        }
                    });
                }

                window.FanousFormNavigator = {
                    move: moveFormFocus,
                    fields: navigableFields
                };

                function updateFormState(form) {
                    const fields = requiredFields(form);

                    if (fields.length === 0) {
                        return;
                    }

                    const complete = fields.every(function (field) {
                        return isFieldComplete(field, form);
                    });

                    submitButtons(form).forEach(function (button) {
                        if (button.matches('[data-allow-partial-submit]')) {
                            button.disabled = false;
                            button.dataset.validationLocked = 'false';
                            button.setAttribute('aria-disabled', 'false');
                            button.title = '';

                            return;
                        }

                        button.disabled = !complete;
                        button.dataset.validationLocked = complete ? 'false' : 'true';
                        button.setAttribute('aria-disabled', complete ? 'false' : 'true');
                        button.title = complete ? '' : 'لطفاً اول همه فیلدهای ضروری را تکمیل کنید.';
                    });
                }

                document.querySelectorAll('form').forEach(function (form) {
                    setupFormKeyboardNavigation(form);

                    if (requiredFields(form).length === 0) {
                        return;
                    }

                    requiredFields(form).forEach(function (field) {
                        const label = field.id ? form.querySelector('label[for="' + CSS.escape(field.id) + '"]') : field.closest('.form-group')?.querySelector('label');
                        label?.classList.add('fanous-required-label');
                    });

                    updateFormState(form);
                    form.addEventListener('input', function () { updateFormState(form); });
                    form.addEventListener('change', function () { updateFormState(form); });
                    form.addEventListener('submit', function (event) {
                        updateFormState(form);

                        const invalidField = firstIncompleteField(form);

                        if (invalidField) {
                            event.preventDefault();
                            form.fanousShowWizardSection?.(invalidField.closest('.student-form-section'));
                            invalidField.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            invalidField.classList.add('is-invalid');
                            window.setTimeout(function () {
                                invalidField.focus({ preventScroll: true });
                                form.reportValidity();
                            }, 180);
                        }
                    });
                    form.addEventListener('keydown', function (event) {
                        if (event.key !== 'Enter' || event.target.matches('textarea')) {
                            return;
                        }

                        if (!requiredFields(form).every(function (field) { return isFieldComplete(field, form); })) {
                            event.preventDefault();
                            updateFormState(form);
                        }
                    });
                });

                function formMethod(form) {
                    const methodField = form.querySelector('input[name="_method"]');
                    return String(methodField?.value || form.method || 'GET').toUpperCase();
                }

                function isDeleteForm(form) {
                    return formMethod(form) === 'DELETE';
                }

                function isDataEntryForm(form) {
                    if (String(form.method || 'GET').toUpperCase() !== 'POST' || isDeleteForm(form)) {
                        return false;
                    }

                    if (form.dataset.noSaveConfirm === 'true' || form.closest('.navbar') || form.action.includes('/logout')) {
                        return false;
                    }

                    return form.matches('.fanous-library-form, .fanous-finance-form, .fanous-representative-form, .fanous-project-form')
                        || Boolean(form.querySelector('.student-form-section'))
                        || Boolean(form.closest('.student-form-layout'));
                }

                function submitWithOriginalButton(form, submitter) {
                    form.dataset.fanousAlertConfirmed = 'true';

                    const existingSubmitValue = Array.from(form.querySelectorAll('input[type="hidden"][data-fanous-submit-value]')).some(function (field) {
                        return field.dataset.fanousSubmitValue === submitter?.name;
                    });

                    if (submitter && submitter.name && submitter.value && !existingSubmitValue) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = submitter.name;
                        hidden.value = submitter.value;
                        hidden.dataset.fanousSubmitValue = submitter.name;
                        form.appendChild(hidden);
                    }

                    if (submitter) {
                        submitter.dataset.originalText = submitter.dataset.originalText || submitter.textContent;
                        submitter.textContent = 'در حال ذخیره...';
                        submitter.disabled = true;
                        submitter.setAttribute('aria-busy', 'true');
                    }

                    HTMLFormElement.prototype.submit.call(form);

                    window.setTimeout(function () {
                        if (document.body.contains(form)) {
                            delete form.dataset.fanousAlertConfirmed;
                        }
                    }, 1200);
                }

                document.querySelectorAll('form').forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.dataset.fanousAlertConfirmed === 'true' || event.defaultPrevented) {
                            return;
                        }

                        if (isDeleteForm(form)) {
                            event.preventDefault();
                            const submitter = event.submitter;
                            window.FanousAlert.confirmDelete().then(function (result) {
                                if (result.isConfirmed) {
                                    submitWithOriginalButton(form, submitter);
                                }
                            });
                            return;
                        }

                        if (isDataEntryForm(form)) {
                            event.preventDefault();
                            const submitter = event.submitter;
                            window.FanousAlert.confirmSave().then(function (result) {
                                if (result.isConfirmed) {
                                    submitWithOriginalButton(form, submitter);
                                } else if (result.isDenied) {
                                    window.FanousAlert.fire({
                                        icon: 'info',
                                        title: 'ذخیره نشد',
                                        text: 'تغییرات این فرم ثبت نشد.',
                                        toast: true
                                    });
                                }
                            });
                        }
                    });
                });

                document.querySelectorAll('form').forEach(function (form) {
                    return;

                    const sections = Array.from(form.querySelectorAll('.student-form-section')).filter(function (section) {
                        return section.closest('form') === form && ! section.classList.contains('is-sticky');
                    });

                    if (sections.length < 2 || form.dataset.noWizard === 'true') {
                        return;
                    }

                    const container = form.querySelector('.student-form-main') || form;
                    const stepper = document.createElement('nav');
                    const controls = document.createElement('div');
                    const previousButton = document.createElement('button');
                    const nextButton = document.createElement('button');
                    let activeIndex = 0;

                    form.classList.add('fanous-wizard-form');
                    stepper.className = 'fanous-form-stepper';
                    stepper.setAttribute('aria-label', 'مراحل فورم');
                    controls.className = 'fanous-form-wizard-controls';

                    previousButton.type = 'button';
                    previousButton.className = 'btn btn-outline-secondary';
                    previousButton.textContent = 'قبلی';

                    nextButton.type = 'button';
                    nextButton.className = 'btn btn-primary';
                    nextButton.textContent = 'بعدی';

                    function sectionTitle(section, index) {
                        const heading = section.querySelector('.student-form-section-head h2, h2, h3');
                        return (heading?.textContent || ('مرحله ' + (index + 1))).trim();
                    }

                    function sectionRequiredFields(section) {
                        return requiredFields(form).filter(function (field) {
                            return section.contains(field);
                        });
                    }

                    function sectionComplete(section) {
                        const fields = sectionRequiredFields(section);
                        return fields.length === 0 || fields.every(function (field) {
                            return isFieldComplete(field, form);
                        });
                    }

                    const stepButtons = sections.map(function (section, index) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'fanous-form-step-button';
                        button.innerHTML = '<span>' + String(index + 1).padStart(2, '0') + '</span><strong>' + sectionTitle(section, index) + '</strong>';
                        button.addEventListener('click', function () {
                            if (index <= activeIndex || sections.slice(0, index).every(sectionComplete)) {
                                showStep(index);
                            } else {
                                const invalidField = firstIncompleteField(form, sections[activeIndex]);
                                invalidField?.reportValidity();
                            }
                        });
                        stepper.appendChild(button);
                        return button;
                    });

                    function updateStepState() {
                        stepButtons.forEach(function (button, index) {
                            button.classList.toggle('is-active', index === activeIndex);
                            button.classList.toggle('is-complete', sectionComplete(sections[index]));
                            button.disabled = index > activeIndex && ! sections.slice(0, index).every(sectionComplete);
                        });

                        previousButton.disabled = activeIndex === 0;
                        nextButton.hidden = activeIndex === sections.length - 1;
                        nextButton.disabled = ! sectionComplete(sections[activeIndex]);
                    }

                    function showStep(index) {
                        activeIndex = Math.max(0, Math.min(index, sections.length - 1));

                        sections.forEach(function (section, sectionIndex) {
                            section.dataset.wizardStep = String(sectionIndex + 1);
                            section.classList.toggle('is-wizard-active', sectionIndex === activeIndex);
                        });

                        updateStepState();
                    }

                    previousButton.addEventListener('click', function () {
                        showStep(activeIndex - 1);
                    });

                    nextButton.addEventListener('click', function () {
                        const invalidField = firstIncompleteField(form, sections[activeIndex]);

                        if (invalidField) {
                            invalidField.reportValidity();
                            invalidField.focus({ preventScroll: true });
                            return;
                        }

                        showStep(activeIndex + 1);
                    });

                    form.addEventListener('input', updateStepState);
                    form.addEventListener('change', updateStepState);

                    form.fanousShowWizardSection = function (section) {
                        const index = sections.indexOf(section);

                        if (index >= 0) {
                            showStep(index);
                        }
                    };

                    controls.appendChild(previousButton);
                    controls.appendChild(nextButton);
                    container.insertBefore(stepper, sections[0]);
                    container.appendChild(controls);
                    showStep(0);
                });
            });
        </script>
    </body>
</html>
