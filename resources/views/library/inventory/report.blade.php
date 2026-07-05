@extends('admin.layout')

@section('title', 'گزارش موجودی کتابخانه - فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $statusMeta = [
            'available' => ['label' => 'موجود', 'tone' => 'success'],
            'on_loan' => ['label' => 'در امانت', 'tone' => 'warning'],
            'damaged' => ['label' => 'خراب', 'tone' => 'danger'],
            'lost' => ['label' => 'گم‌شده', 'tone' => 'danger'],
            'archived' => ['label' => 'آرشیف', 'tone' => 'default'],
        ];
        $problemCount = (int) $statusCounts->get('damaged', 0) + (int) $statusCounts->get('lost', 0);
    @endphp

    <div class="fanous-library-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">کنترل موجودی</span>
                <h1>گزارش موجودی کتابخانه</h1>
                <p>تمام نسخه‌های فزیکی کتابخانه را بر اساس وضعیت، قفسه، بارکد، عنوان و حالت نسخه بررسی کنید.</p>
            </div>
            <div class="fanous-page-actions">
                <x-ds.button :href="route('library.inventory.export', request()->query())">خروجی CSV</x-ds.button>
                <x-ds.button variant="outline" type="button" onclick="window.print()">چاپ</x-ds.button>
                <x-ds.button variant="outline" :href="route('library.index')">کتابخانه</x-ds.button>
            </div>
        </section>

        <section class="dashboard-stat-grid" aria-label="خلاصه موجودی">
            <article class="dashboard-stat">
                <div>
                    <span>کل نسخه‌ها</span>
                    <strong>{{ Locale::number($totalCopies) }}</strong>
                    <small>{{ Locale::number((int) $statusCounts->get('available', 0)) }} نسخه آماده امانت</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="books" /></span>
            </article>
            <article class="dashboard-stat">
                <div>
                    <span>در امانت</span>
                    <strong>{{ Locale::number((int) $statusCounts->get('on_loan', 0)) }}</strong>
                    <small>نسخه‌هایی که فعلاً بیرون از کتابخانه است</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="book" /></span>
            </article>
            <article class="dashboard-stat">
                <div>
                    <span>نسخه‌های مشکل‌دار</span>
                    <strong>{{ Locale::number($problemCount) }}</strong>
                    <small>ارزش ثبت‌شده: {{ Locale::money($lostValue + $damagedValue) }}</small>
                </div>
                <span class="dashboard-stat-icon is-danger"><x-ds.icon name="bell" /></span>
            </article>
            <article class="dashboard-stat">
                <div>
                    <span>آرشیف</span>
                    <strong>{{ Locale::number((int) $statusCounts->get('archived', 0)) }}</strong>
                    <small>{{ Locale::number($emptyBooks->count()) }} کتاب بدون نسخه موجود</small>
                </div>
                <span class="dashboard-stat-icon is-purple"><x-ds.icon name="logs" /></span>
            </article>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">فیلتر</span>
                    <h2>جستجوی نسخه‌ها</h2>
                    <p>بر اساس کد نسخه، بارکد، عنوان، نویسنده، ISBN، قفسه، وضعیت یا دسته‌بندی جستجو کنید.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('library.inventory.report') }}" class="fanous-finance-filter-grid">
                <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="کد نسخه، بارکد، عنوان، نویسنده یا ISBN">
                <select class="form-control" name="status">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach ($statusMeta as $value => $meta)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
                <input class="form-control" name="shelf" value="{{ $filters['shelf'] ?? '' }}" placeholder="قفسه">
                <input class="form-control" name="category" value="{{ $filters['category'] ?? '' }}" placeholder="دسته‌بندی">
                <div class="fanous-filter-actions">
                    <x-ds.button type="submit">جستجو</x-ds.button>
                    <x-ds.button variant="outline" :href="route('library.inventory.export', request()->query())">CSV</x-ds.button>
                    <x-ds.button variant="outline" :href="route('library.inventory.report')">پاک کردن</x-ds.button>
                </div>
            </form>
        </section>

        <section class="dashboard-main-grid">
            <article class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">مشکل‌ها</span>
                        <h2>خراب و گم‌شده</h2>
                        <p>نسخه‌هایی که نیاز به ترمیم، جایگزینی یا پیگیری دارند.</p>
                    </div>
                </div>
                <div class="dashboard-activity-list">
                    @forelse ($problemCopies as $copy)
                        <div class="dashboard-activity">
                            <span class="dashboard-activity-icon"><x-ds.icon name="bell" /></span>
                            <div>
                                <strong>{{ $copy->book?->title ?: 'کتاب نامشخص' }}</strong>
                                <small><span class="ltr-text">{{ $copy->copy_code }}</span> · {{ $statusMeta[$copy->status]['label'] ?? $copy->status }} · قفسه: <span class="ltr-text">{{ $copy->shelf_code ?: 'N/A' }}</span> · {{ Locale::money((int) $copy->purchase_price) }}</small>
                            </div>
                            @if ($canWriteLibrary && $copy->book)
                                <x-ds.button variant="outline" size="sm" :href="route('library.books.edit', $copy->book)">مدیریت</x-ds.button>
                            @endif
                        </div>
                    @empty
                        <div class="dashboard-empty">فعلاً نسخه خراب یا گم‌شده وجود ندارد.</div>
                    @endforelse
                </div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">کمبود</span>
                        <h2>کتاب‌های بدون نسخه موجود</h2>
                        <p>عنوان‌هایی که فعلاً قابل امانت دادن نیستند.</p>
                    </div>
                </div>
                <div class="dashboard-activity-list">
                    @forelse ($emptyBooks as $book)
                        <div class="dashboard-activity">
                            <span class="dashboard-activity-icon"><x-ds.icon name="book" /></span>
                            <div>
                                <strong>{{ $book->title }}</strong>
                                <small>{{ $book->author ?: 'نویسنده نامشخص' }} · {{ Locale::number($book->total_copies) }} نسخه ثبت‌شده</small>
                            </div>
                            @if ($canWriteLibrary)
                                <x-ds.button variant="outline" size="sm" :href="route('library.books.edit', $book)">ویرایش</x-ds.button>
                            @endif
                        </div>
                    @empty
                        <div class="dashboard-empty">همه عنوان‌ها حداقل یک نسخه موجود دارند.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">نسخه‌ها</span>
                    <h2>نسخه‌های فیزیکی مطابق فیلتر</h2>
                    <p>{{ Locale::number($copies->count()) }} نسخه فیزیکی پیدا شد؛ اگر یک کتاب چند نسخه داشته باشد، هر نسخه در یک ردیف جدا نمایش داده می‌شود.</p>
                </div>
            </div>

            <div class="fanous-table-wrap">
                <table class="fanous-finance-table">
                    <thead>
                        <tr>
                            <th>کتاب</th>
                            <th>کد نسخه</th>
                            <th>بارکد</th>
                            <th>قفسه</th>
                            <th>وضعیت</th>
                            <th>ارزش</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($copies as $copy)
                            @php $meta = $statusMeta[$copy->status] ?? ['label' => $copy->status, 'tone' => 'default']; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $copy->book?->title ?: 'کتاب نامشخص' }}</strong>
                                    <small>{{ $copy->book?->author ?: 'نویسنده نامشخص' }}</small>
                                </td>
                                <td class="ltr-text">{{ $copy->copy_code }}</td>
                                <td class="ltr-text">{{ $copy->barcode ?: 'N/A' }}</td>
                                <td class="ltr-text">{{ $copy->shelf_code ?: 'N/A' }}</td>
                                <td><x-ds.badge :tone="$meta['tone']">{{ $meta['label'] }}</x-ds.badge></td>
                                <td>{{ Locale::money((int) $copy->purchase_price) }}</td>
                                <td>
                                    @if ($canWriteLibrary && $copy->book)
                                        <x-ds.button variant="outline" size="sm" :href="route('library.books.edit', $copy->book)">مدیریت</x-ds.button>
                                    @else
                                        <span>ندارد</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="dashboard-empty">هیچ نسخه‌ای با فیلتر فعلی پیدا نشد.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
