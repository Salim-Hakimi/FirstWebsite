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
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1"><span data-i18n="editBook">Edit book</span>: {{ $book->title }}</h3>
                            <p class="mb-0 text-white-50" data-i18n="editBookDescription">Update book metadata, shelf location, status, and copy count.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a class="btn btn-outline-light btn-rounded" href="{{ route('library.index') }}" data-i18n="back">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title" data-i18n="bookDetails">Book details</h4>
                    <form method="POST" action="{{ route('library.books.update', $book) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4 form-group"><label>ISBN</label><input class="form-control" name="isbn" value="{{ old('isbn', $book->isbn) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="barcode">Barcode</label><input class="form-control" name="barcode" value="{{ old('barcode', $book->barcode) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="shelfCode">Shelf code</label><input class="form-control" name="shelf_code" value="{{ old('shelf_code', $book->shelf_code) }}"></div>
                            <div class="col-12 form-group"><label data-i18n="bookTitle">Book title</label><input class="form-control" name="title" value="{{ old('title', $book->title) }}" required></div>
                            <div class="col-md-6 form-group"><label data-i18n="author">Author</label><input class="form-control" name="author" value="{{ old('author', $book->author) }}"></div>
                            <div class="col-md-6 form-group"><label data-i18n="publisher">Publisher</label><input class="form-control" name="publisher" value="{{ old('publisher', $book->publisher) }}"></div>
                            <div class="col-md-3 form-group"><label data-i18n="language">Language</label><input class="form-control" name="language" value="{{ old('language', $book->language) }}"></div>
                            <div class="col-md-3 form-group"><label data-i18n="edition">Edition</label><input class="form-control" name="edition" value="{{ old('edition', $book->edition) }}"></div>
                            <div class="col-md-3 form-group"><label data-i18n="publishedYear">Published year</label><input class="form-control" name="published_year" type="number" min="1000" max="{{ now()->year }}" value="{{ old('published_year', $book->published_year) }}"></div>
                            <div class="col-md-3 form-group"><label data-i18n="pages">Pages</label><input class="form-control" name="pages" type="number" min="1" value="{{ old('pages', $book->pages) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="category">Category</label><input class="form-control" name="category" value="{{ old('category', $book->category) }}"></div>
                            <div class="col-md-4 form-group"><label data-i18n="totalCopies">Total copies</label><input class="form-control" name="total_copies" type="number" min="1" value="{{ old('total_copies', $book->total_copies) }}" required></div>
                            <div class="col-md-4 form-group">
                                <label data-i18n="status">Status</label>
                                <select class="form-control" name="status">
                                    @foreach ($bookStatusMeta as $value => $meta)
                                        <option value="{{ $value }}" @selected(old('status', $book->status) === $value) data-i18n="{{ $meta['key'] }}">{{ $meta['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 form-group"><label data-i18n="notes">Notes</label><textarea class="form-control" name="notes" rows="4">{{ old('notes', $book->notes) }}</textarea></div>
                            <div class="col-12">
                                <button class="btn btn-primary mr-2" type="submit" data-i18n="saveChanges">Save changes</button>
                                <a class="btn btn-dark" href="{{ route('library.index') }}" data-i18n="cancel">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
