@extends('admin.layout')

@section('title', 'اتاق‌ها و ظرفیت - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $freeBeds = max(0, $totalCapacity - $occupiedBeds);
        $occupancyRate = $totalCapacity > 0 ? min(100, round(($occupiedBeds / $totalCapacity) * 100)) : 0;
        $activeRooms = $rooms->where('status', 'active')->count();
        $maintenanceRooms = $rooms->where('status', 'maintenance')->count();
        $closedRooms = $rooms->where('status', 'closed')->count();
        $roomStatusNames = $statusLabels ?? [
            'active' => 'فعال',
            'maintenance' => 'در تعمیر',
            'closed' => 'بسته',
        ];
        $statusTones = [
            'active' => 'success',
            'maintenance' => 'warning',
            'closed' => 'danger',
        ];
        $floors = $rooms->pluck('floor')->filter()->unique()->values();
        $query = trim((string) request('q'));
        $statusFilter = request('status');
        $floorFilter = request('floor');
        $filteredRooms = $rooms
            ->when($query !== '', fn ($items) => $items->filter(fn ($room) => str_contains(mb_strtolower((string) $room->room_number), mb_strtolower($query)) || str_contains(mb_strtolower((string) $room->floor), mb_strtolower($query))))
            ->when($statusFilter, fn ($items) => $items->where('status', $statusFilter))
            ->when($floorFilter, fn ($items) => $items->where('floor', $floorFilter))
            ->values();
    @endphp

    <div class="fanous-rooms-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">کنترل لیلیه</span>
                <h1>اتاق‌ها و ظرفیت</h1>
                <p>ظرفیت اتاق‌ها، تخت‌های خالی، تخت‌های اشغال‌شده و وضعیت هر اتاق را مدیریت کنید.</p>
            </div>

            <div class="fanous-page-actions">
                <x-ds.button variant="outline" href="#room-filters">فیلتر اتاق‌ها</x-ds.button>
                <x-ds.button :href="route('dorm.rooms.create')">
                    <x-ds.icon name="plus" />
                    افزودن اتاق جدید
                </x-ds.button>
            </div>
        </section>

        <section class="dashboard-stat-grid" aria-label="آمار اتاق‌ها">
            <article class="dashboard-stat">
                <div>
                    <span>ظرفیت مجموعی</span>
                    <strong>{{ Locale::number($totalCapacity) }}</strong>
                    <small>کل تخت‌ها در لیلیه</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="bed" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>تخت‌های خالی</span>
                    <strong>{{ Locale::number($freeBeds) }}</strong>
                    <small>آماده برای اختصاص</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="bed" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>تخت‌های اشغال‌شده</span>
                    <strong>{{ Locale::number($occupiedBeds) }}</strong>
                    <small>در حال استفاده</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="users" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>اتاق‌های ثبت‌شده</span>
                    <strong>{{ Locale::number($rooms->count()) }}</strong>
                    <small>{{ Locale::number($activeRooms) }} فعال، {{ Locale::number($maintenanceRooms) }} در تعمیر، {{ Locale::number($closedRooms) }} بسته</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="building" /></span>
            </article>
        </section>

        <section class="fanous-rooms-overview">
            <article class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">کارت‌های اتاق</span>
                        <h2>فهرست اتاق‌ها</h2>
                        <p>برای هر اتاق ظرفیت، تخت خالی، تخت اشغال‌شده و عملیات مدیریت را ببینید.</p>
                    </div>
                    <x-ds.button size="sm" :href="route('dorm.rooms.create')">اتاق جدید</x-ds.button>
                </div>

                <div data-vue-app="dorm-rooms-table" data-title="نمای سریع اتاق‌ها" data-endpoint="{{ route('api.dorm.rooms') }}"></div>

                <form id="room-filters" method="GET" action="{{ route('dorm.rooms.index') }}" class="fanous-room-filters">
                    <input class="form-control" type="search" name="q" value="{{ request('q') }}" placeholder="جستجوی نام یا کد اتاق...">
                    <select class="form-control" name="status">
                        <option value="">همه وضعیت‌ها</option>
                        @foreach ($roomStatusNames as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select class="form-control" name="floor">
                        <option value="">همه منزل‌ها</option>
                        @foreach ($floors as $floor)
                            <option value="{{ $floor }}" @selected(request('floor') === $floor)>{{ $floor }}</option>
                        @endforeach
                    </select>
                    <div class="fanous-filter-actions">
                        <x-ds.button type="submit">فیلتر</x-ds.button>
                        <x-ds.button variant="outline" :href="route('dorm.rooms.index')">پاک کردن</x-ds.button>
                    </div>
                </form>

                <div class="fanous-room-grid">
                    @forelse ($filteredRooms as $room)
                        @php
                            $roomFreeBeds = max(0, $room->capacity - $room->occupied_beds);
                            $usedPercent = $room->capacity > 0 ? min(100, round(($room->occupied_beds / $room->capacity) * 100)) : 0;
                            $tone = $statusTones[$room->status] ?? 'primary';
                        @endphp
                        <article class="fanous-room-card">
                            <div class="fanous-room-head">
                                <div class="fanous-room-title">
                                    <span class="fanous-room-icon">ا</span>
                                    <div>
                                        <strong>اتاق {{ Locale::number($room->room_number) }}</strong>
                                        <span>کد اتاق: <b class="ltr-text">{{ $room->room_number }}</b></span>
                                    </div>
                                </div>
                                <x-ds.badge :tone="$tone">{{ $roomStatusNames[$room->status] ?? $room->status }}</x-ds.badge>
                            </div>

                            <div class="fanous-room-progress" aria-label="میزان استفاده">
                                <span style="width: {{ $usedPercent }}%"></span>
                            </div>

                            <div class="fanous-room-stats">
                                <div><strong>{{ Locale::number($room->capacity) }}</strong><span>ظرفیت</span></div>
                                <div><strong>{{ Locale::number($room->occupied_beds) }}</strong><span>اشغال</span></div>
                                <div><strong>{{ Locale::number($roomFreeBeds) }}</strong><span>خالی</span></div>
                            </div>

                            <p>{{ $room->notes ?: 'برای این اتاق یادداشتی ثبت نشده است.' }}</p>

                            <div class="fanous-room-actions">
                                <x-ds.button variant="outline" size="sm" :href="route('dorm.rooms.edit', $room)">ویرایش</x-ds.button>
                                <x-ds.button size="sm" :href="route('dorm.rooms.show', $room)">مدیریت</x-ds.button>
                            </div>
                        </article>
                    @empty
                        <div class="dashboard-empty">
                            <strong>اتاقی پیدا نشد</strong>
                            <p>فیلترها را پاک کنید یا یک اتاق جدید ثبت کنید.</p>
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="dashboard-panel fanous-room-usage">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">نمای کلی استفاده</span>
                        <h2>استفاده لیلیه</h2>
                        <p>نرخ استفاده از ظرفیت موجود در اتاق‌ها.</p>
                    </div>
                    <x-ds.badge tone="primary">امروز</x-ds.badge>
                </div>

                <div class="dashboard-donut" style="--value: {{ $occupancyRate }}">
                    <div>
                        <strong>{{ Locale::percent($occupancyRate) }}</strong>
                        <span>نرخ استفاده</span>
                    </div>
                </div>

                <div class="fanous-usage-list">
                    <div>
                        <span>ظرفیت مجموعی</span>
                        <strong>{{ Locale::number($totalCapacity) }}</strong>
                    </div>
                    <div>
                        <span>تخت‌های خالی</span>
                        <strong>{{ Locale::number($freeBeds) }}</strong>
                    </div>
                    <div>
                        <span>استفاده‌شده</span>
                        <strong>{{ Locale::number($occupiedBeds) }}</strong>
                    </div>
                </div>
            </aside>
        </section>
    </div>
@endsection
