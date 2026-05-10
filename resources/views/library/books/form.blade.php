@extends('admin.layout')

@section('title', 'Edit Book - Fanous Admin')

@section('content')
    @php
        $bookStatusMeta = [
            'available' => ['key' => 'bookAvailable', 'label' => 'Available'],
            'damaged' => ['key' => 'bookDamaged', 'label' => 'Damaged'],
            'lost' => ['key' => 'bookLost', 'label' => 'Lost'],
            'archived' => ['key' => 'bookArchived', 'label' => 'Archived'],
        ];
        $selectedStatus = old('status', $book->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker" data-i18n="editBook">Edit book</span>
            <h1>{{ $book->title }}</h1>
            <p data-i18n="editBookDescription">Update book metadata, shelf location, status, and copy count.</p>
        </div>

        <div class="student-command-actions">
            <a class="btn btn-outline-light" href="{{ route('library.index') }}" data-i18n="back">Back</a>
            <a class="btn btn-primary" href="{{ route('library.books.copy-labels', $book) }}">Print labels</a>
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
                            <h2 data-i18n="bookDetails">Book details</h2>
                            <p data-i18n="editBookDescription">Update book metadata, shelf location, status, and copy count.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group"><label>ISBN</label><input class="form-control" name="isbn" value="{{ old('isbn', $book->isbn) }}"></div>
                        <div class="form-group"><label data-i18n="barcode">Barcode</label><input class="form-control" name="barcode" value="{{ old('barcode', $book->barcode) }}"></div>
                        <div class="form-group"><label data-i18n="shelfCode">Shelf code</label><input class="form-control" name="shelf_code" value="{{ old('shelf_code', $book->shelf_code) }}"></div>
                        <div class="form-group full"><label data-i18n="bookTitle">Book title</label><input class="form-control" name="title" value="{{ old('title', $book->title) }}" required></div>
                        <div class="form-group"><label data-i18n="author">Author</label><input class="form-control" name="author" value="{{ old('author', $book->author) }}"></div>
                        <div class="form-group"><label data-i18n="publisher">Publisher</label><input class="form-control" name="publisher" value="{{ old('publisher', $book->publisher) }}"></div>
                        <div class="form-group"><label data-i18n="language">Language</label><input class="form-control" name="language" value="{{ old('language', $book->language) }}"></div>
                        <div class="form-group"><label data-i18n="edition">Edition</label><input class="form-control" name="edition" value="{{ old('edition', $book->edition) }}"></div>
                        <div class="form-group"><label data-i18n="publishedYear">Published year</label><input class="form-control" name="published_year" type="number" min="1000" max="{{ now()->year }}" value="{{ old('published_year', $book->published_year) }}"></div>
                        <div class="form-group"><label data-i18n="pages">Pages</label><input class="form-control" name="pages" type="number" min="1" value="{{ old('pages', $book->pages) }}"></div>
                        <div class="form-group"><label data-i18n="category">Category</label><input class="form-control" name="category" value="{{ old('category', $book->category) }}"></div>
                        <div class="form-group"><label data-i18n="totalCopies">Total copies</label><input class="form-control" name="total_copies" type="number" min="1" value="{{ old('total_copies', $book->total_copies) }}" required></div>
                        <div class="form-group">
                            <label data-i18n="status">Status</label>
                            <select class="form-control" name="status">
                                @foreach ($bookStatusMeta as $value => $meta)
                                    <option value="{{ $value }}" @selected($selectedStatus === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group full"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $book->notes) }}</textarea></div>
                    </div>
                </section>
            </main>

            <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2 data-i18n="bookInventory">Book inventory</h2>
                            <p>{{ $book->available_copies }} / {{ $book->total_copies }} <span data-i18n="available">available</span></p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary" data-i18n="{{ $bookStatusMeta[$selectedStatus]['key'] ?? 'statusUnknown' }}">{{ $bookStatusMeta[$selectedStatus]['label'] ?? $selectedStatus }}</span>
                        <strong>{{ $book->title }}</strong>
                        <p>{{ $book->shelf_code ?: 'N/A' }}</p>
                    </div>

                    <div class="student-side-divider"></div>
                    <h3 class="student-side-title">Physical copies</h3>
                    <div class="student-document-list">
                        @forelse ($book->copies()->orderBy('copy_code')->get() as $copy)
                            <form method="POST" action="{{ route('library.book-copies.update', $copy) }}" class="student-card-summary library-copy-editor">
                                @csrf
                                @method('PUT')
                                <span class="student-timeline-icon">C</span>
                                <div>
                                    <strong>{{ $copy->copy_code }}</strong>
                                    <p>{{ $copy->barcode ?: 'No barcode' }}</p>
                                    <div class="student-form-grid compact mt-2">
                                        <select class="form-control form-control-sm" name="status" @disabled($copy->status === 'on_loan')>
                                            @foreach (['available' => 'Available', 'on_loan' => 'On loan', 'damaged' => 'Damaged', 'lost' => 'Lost', 'archived' => 'Archived'] as $value => $label)
                                                <option value="{{ $value }}" @selected($copy->status === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <input class="form-control form-control-sm" name="shelf_code" value="{{ $copy->shelf_code }}" placeholder="Shelf">
                                        <input class="form-control form-control-sm" name="condition" value="{{ $copy->condition }}" placeholder="Condition">
                                        <input class="form-control form-control-sm" name="purchase_price" type="number" min="0" value="{{ $copy->purchase_price }}" placeholder="Price AFN">
                                    </div>
                                    <input class="form-control form-control-sm mt-2" name="notes" value="{{ $copy->notes }}" placeholder="Copy note">
                                    @if ($copy->status === 'on_loan')
                                        <small class="text-warning">This copy is on loan. Return it before changing inventory status.</small>
                                    @endif
                                </div>
                                <button class="btn btn-outline-secondary btn-sm" type="submit">Save</button>
                            </form>
                        @empty
                            <div class="student-directory-empty">Copies will be generated after saving this book.</div>
                        @endforelse
                    </div>

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit" data-i18n="saveChanges">Save changes</button>
                        <a class="btn btn-outline-secondary" href="{{ route('library.books.copy-labels', $book) }}">Print labels</a>
                        <a class="btn btn-dark" href="{{ route('library.index') }}" data-i18n="cancel">Cancel</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>
@endsection
