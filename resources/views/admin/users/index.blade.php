@extends('admin.layout')

@section('title', 'کاربران و نقش‌ها - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $activeCount = $users->where('status', \App\Models\User::STATUS_ACTIVE)->count();
        $pendingCount = $users->where('status', \App\Models\User::STATUS_PENDING)->count();
        $suspendedCount = $users->where('status', \App\Models\User::STATUS_SUSPENDED)->count();
        $adminCount = $users->filter(fn ($user) => in_array($user->role, \App\Models\User::managementRoles(), true))->count();

        $roleNames = $roleLabels ?? \App\Models\User::roleOptions();
        $statusNames = $statusLabels ?? \App\Models\User::statusOptions();

        $statusTone = [
            \App\Models\User::STATUS_ACTIVE => 'success',
            \App\Models\User::STATUS_PENDING => 'warning',
            \App\Models\User::STATUS_SUSPENDED => 'danger',
        ];

        $accessRows = [
            [
                'label' => 'فعال',
                'description' => 'حساب‌هایی که اجازه ورود به سیستم را دارند',
                'count' => $activeCount,
                'tone' => 'success',
                'icon' => 'ف',
            ],
            [
                'label' => 'در انتظار',
                'description' => 'حساب‌هایی که هنوز نیاز به بررسی دارند',
                'count' => $pendingCount,
                'tone' => 'warning',
                'icon' => 'د',
            ],
            [
                'label' => 'مسدود',
                'description' => 'حساب‌هایی که دسترسی آن‌ها بسته شده است',
                'count' => $suspendedCount,
                'tone' => 'danger',
                'icon' => 'م',
            ],
        ];
    @endphp

    <div class="fanous-users-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">مدیریت دسترسی</span>
                <h1>کاربران و نقش‌ها</h1>
                <p>حساب کارمندان را بسازید، نقش تعیین کنید و وضعیت دسترسی هر بخش سیستم فانوس را مدیریت کنید.</p>
            </div>

            <div class="fanous-page-actions">
                <x-ds.button :href="route('admin.users.create')">
                    <span aria-hidden="true">+</span>
                    افزودن کاربر جدید
                </x-ds.button>
            </div>
        </section>

        <section class="dashboard-stat-grid" aria-label="خلاصه کاربران">
            <article class="dashboard-stat">
                <div>
                    <span>کل کاربران</span>
                    <strong>{{ Locale::number($users->count()) }}</strong>
                    <small>همه حساب‌های ثبت‌شده در سیستم</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="users" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>حساب‌های فعال</span>
                    <strong>{{ Locale::number($activeCount) }}</strong>
                    <small>کاربران دارای اجازه ورود</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="shield" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>در انتظار بررسی</span>
                    <strong>{{ Locale::number($pendingCount) }}</strong>
                    <small>حساب‌های نیازمند تایید مدیر</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="calendar" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>کاربران مدیریتی</span>
                    <strong>{{ Locale::number($adminCount) }}</strong>
                    <small>حساب‌های دارای نقش ادمین</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="settings" /></span>
            </article>
        </section>

        <section class="fanous-users-layout">
            <article class="dashboard-panel fanous-users-table-card">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">فهرست کاربران</span>
                        <h2>حساب‌های کاربری</h2>
                        <p>هر حساب در این فهرست دارای نقش و وضعیت دسترسی مشخص است.</p>
                    </div>

                    <x-ds.button size="sm" :href="route('admin.users.create')">ساخت کاربر</x-ds.button>
                </div>

                <div class="fanous-table-wrap">
                    <table class="fanous-users-table">
                        <thead>
                            <tr>
                                <th>کاربر</th>
                                <th>تماس / ایمیل</th>
                                <th>نقش</th>
                                <th>وضعیت</th>
                                <th>ساخته‌شده</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                @php
                                    $roleLabel = $roleNames[$user->role] ?? $user->role;
                                    $statusLabel = $statusNames[$user->status] ?? $user->status;
                                    $tone = $statusTone[$user->status] ?? 'primary';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fanous-user-cell">
                                            @if ($user->profile_photo_path)
                                                <img class="fanous-user-avatar" src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}">
                                            @else
                                                <span class="fanous-user-avatar">{{ mb_substr($user->name, 0, 1) }}</span>
                                            @endif
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                @if (auth()->id() === $user->id)
                                                    <small>حساب فعلی</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fanous-contact-cell">
                                            <span class="ltr-text">{{ $user->email }}</span>
                                            <small>{{ $user->phone ?: 'شماره تماس ثبت نشده' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <x-ds.badge tone="primary">{{ $roleLabel }}</x-ds.badge>
                                    </td>
                                    <td>
                                        <x-ds.badge :tone="$tone">{{ $statusLabel }}</x-ds.badge>
                                    </td>
                                    <td>{{ $user->created_at ? Locale::number($user->created_at->format('Y/m/d')) : 'ثبت نشده' }}</td>
                                    <td>
                                        @if (auth()->id() !== $user->id)
                                            <div class="fanous-row-actions">
                                                <x-ds.button variant="outline" size="sm" :href="route('admin.users.edit', $user)">ویرایش</x-ds.button>
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ds.button variant="danger" size="sm" type="submit">حذف</x-ds.button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="fanous-locked-label">قفل</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="dashboard-empty">هنوز هیچ کاربر ساخته نشده است.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="dashboard-panel fanous-access-card">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">نمای کلی دسترسی</span>
                        <h2>وضعیت حساب‌ها</h2>
                        <p>گروه‌بندی حساب‌ها بر اساس اجازه ورود و وضعیت بررسی.</p>
                    </div>
                </div>

                <div class="fanous-access-list">
                    @foreach ($accessRows as $row)
                        <div class="fanous-access-row fanous-access-row--{{ $row['tone'] }}">
                            <span class="fanous-access-icon">{{ $row['icon'] }}</span>
                            <div>
                                <strong>{{ $row['label'] }}</strong>
                                <p>{{ $row['description'] }}</p>
                            </div>
                            <span class="fanous-access-count">{{ Locale::number($row['count']) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="fanous-role-note">
                    <strong>نقش‌های مدیریتی</strong>
                    <p>{{ Locale::number($adminCount) }} حساب به بخش‌های مدیریتی سیستم دسترسی دارد.</p>
                </div>
            </aside>
        </section>
    </div>
@endsection
