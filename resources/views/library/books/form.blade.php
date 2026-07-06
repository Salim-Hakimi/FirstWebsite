@extends('admin.layout')

@section('title', 'ویرایش کتاب - ادمین فانوس')

@section('content')
    @php
        $bookStatusMeta = [
            'available' => ['key' => 'bookAvailable', 'label' => 'قابل استفاده'],
            'damaged' => ['key' => 'bookDamaged', 'label' => 'خراب'],
            'lost' => ['key' => 'bookLost', 'label' => 'گم‌شده'],
            'archived' => ['key' => 'bookArchived', 'label' => 'آرشیف'],
        ];
        $selectedStatus = old('status', $book->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker" data-i18n="editBook">ویرایش کتاب</span>
            <h1>{{ $book->title }}</h1>
            <p data-i18n="editBookDescription">معلومات کتاب، قفسه، وضعیت و تعداد نسخه‌ها را ویرایش کنید.</p>
        </div>

        <div class="student-command-actions">
            <a class="btn btn-outline-light" href="{{ route('library.index') }}" data-i18n="back">برگشت</a>
            <a class="btn btn-primary" href="{{ route('library.books.copy-labels', $book) }}">چاپ لیبل‌ها</a>
        </div>
    </section>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('library.books.update', $book) }}">
        @csrf
        @method('PUT')

        <div class="student-form-layout">
            <main class="student-form-main">
                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">01</span>
                        <div>
                            <h2 data-i18n="bookDetails">جزئیات کتاب</h2>
                            <p data-i18n="editBookDescription">معلومات کتاب، قفسه، وضعیت و تعداد نسخه‌ها را ویرایش کنید.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group"><label>ISBN</label><input class="form-control @error('isbn') is-invalid @enderror" name="isbn" value="{{ old('isbn', $book->isbn) }}">@error('isbn') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group"><label data-i18n="barcode">بارکد</label><input class="form-control @error('barcode') is-invalid @enderror" name="barcode" value="{{ old('barcode', $book->barcode) }}">@error('barcode') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group"><label data-i18n="shelfCode">کد قفسه</label><input class="form-control" name="shelf_code" value="{{ old('shelf_code', $book->shelf_code) }}"></div>
                        <div class="form-group full"><label data-i18n="bookTitle">نام کتاب</label><input class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $book->title) }}" required>@error('title') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group"><label data-i18n="author">نویسنده</label><input class="form-control" name="author" value="{{ old('author', $book->author) }}"></div>
                        <div class="form-group"><label data-i18n="publisher">ناشر</label><input class="form-control" name="publisher" value="{{ old('publisher', $book->publisher) }}"></div>
                        <div class="form-group"><label data-i18n="language">زبان</label><input class="form-control" name="language" value="{{ old('language', $book->language) }}"></div>
                        <div class="form-group"><label data-i18n="edition">چاپ / نسخه</label><input class="form-control" name="edition" value="{{ old('edition', $book->edition) }}"></div>
                        <div class="form-group"><label data-i18n="publishedYear">سال نشر</label><input class="form-control" name="published_year" type="number" min="1000" max="{{ now()->year }}" value="{{ old('published_year', $book->published_year) }}"></div>
                        <div class="form-group"><label data-i18n="pages">صفحات</label><input class="form-control" name="pages" type="number" min="1" value="{{ old('pages', $book->pages) }}"></div>
                        <div class="form-group"><label data-i18n="category">دسته‌بندی</label><input class="form-control" name="category" value="{{ old('category', $book->category) }}"></div>
                        <div class="form-group"><label data-i18n="totalCopies">تعداد نسخه</label><input class="form-control @error('total_copies') is-invalid @enderror" name="total_copies" type="number" min="1" value="{{ old('total_copies', $book->total_copies) }}" required>@error('total_copies') <span class="text-danger small">{{ $message }}</span> @enderror</div>
                        <div class="form-group">
                            <label data-i18n="status">وضعیت</label>
                            <select class="form-control" name="status">
                                @foreach ($bookStatusMeta as $value => $meta)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full"><label data-i18n="notes">یادداشت</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $book->notes) }}</textarea></div>
                    </div>
                </section>
            </main>

            <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2 data-i18n="bookInventory">موجودی کتاب</h2>
                            <p>{{ $book->available_copies }} / {{ $book->total_copies }} <span data-i18n="available">موجود</span></p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary" data-i18n="{{ $bookStatusMeta[$selectedStatus]['key'] ?? 'statusUnknown' }}">{{ $bookStatusMeta[$selectedStatus]['label'] ?? $selectedStatus }}</span>
                        <strong>{{ $book->title }}</strong>
                        <p>{{ $book->shelf_code ?: 'ثبت نشده' }}</p>
                    </div>

                    @php
                        $copySummary = $book->copies()
                            ->selectRaw('status, COUNT(*) as total')
                            ->groupBy('status')
                            ->pluck('total', 'status');
                        $shelves = $book->copies()
                            ->whereNotNull('shelf_code')
                            ->where('shelf_code', '!=', '')
                            ->distinct()
                            ->orderBy('shelf_code')
                            ->pluck('shelf_code');
                    @endphp

                    <div class="student-side-divider"></div>
                    <h3 class="student-side-title">خلاصه نسخه‌های کتاب</h3>
                    <div class="library-copy-summary">
                        <div>
                            <span>کل نسخه‌ها</span>
                            <strong>{{ \App\Support\Locale::number((int) $book->total_copies) }}</strong>
                        </div>
                        <div>
                            <span>موجود</span>
                            <strong>{{ \App\Support\Locale::number((int) $copySummary->get('available', 0)) }}</strong>
                        </div>
                        <div>
                            <span>در امانت</span>
                            <strong>{{ \App\Support\Locale::number((int) $copySummary->get('on_loan', 0)) }}</strong>
                        </div>
                        <div>
                            <span>خراب / گم‌شده</span>
                            <strong>{{ \App\Support\Locale::number((int) $copySummary->get('damaged', 0) + (int) $copySummary->get('lost', 0)) }}</strong>
                        </div>
                    </div>

                    <div class="library-copy-shelf">
                        <span>قفسه‌ها</span>
                        <strong>{{ $shelves->isNotEmpty() ? $shelves->join('، ') : ($book->shelf_code ?: 'ثبت نشده') }}</strong>
                        <small>برای تغییر تعداد نسخه‌ها، مقدار «تعداد نسخه» را ویرایش و ذخیره کنید. لیبل هر نسخه از دکمه چاپ لیبل‌ها ساخته می‌شود.</small>
                    </div>

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit" data-i18n="saveChanges">ذخیره تغییرات</button>
                        <a class="btn btn-outline-secondary" href="{{ route('library.books.copy-labels', $book) }}">چاپ لیبل‌ها</a>
                        <a class="btn btn-dark" href="{{ route('library.index') }}" data-i18n="cancel">لغو</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
@endsection
