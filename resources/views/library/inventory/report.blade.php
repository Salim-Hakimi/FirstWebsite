@extends('admin.layout')

@section('title', 'Library Inventory Report - Fanous Admin')

@section('content')
    @php
        $statusMeta = [
            'available' => ['label' => 'Available', 'class' => 'badge-outline-success'],
            'on_loan' => ['label' => 'On loan', 'class' => 'badge-outline-warning'],
            'damaged' => ['label' => 'Damaged', 'class' => 'badge-outline-danger'],
            'lost' => ['label' => 'Lost', 'class' => 'badge-outline-danger'],
            'archived' => ['label' => 'Archived', 'class' => 'badge-outline-secondary'],
        ];
    @endphp

    <section class="student-command-shell">
        <div class="student-command-copy">
            <span class="student-command-kicker">Inventory control</span>
            <h1>Library inventory report</h1>
            <p>Track every physical copy by status, shelf, title, barcode, and condition.</p>
        </div>
        <div class="student-command-actions">
            <a class="btn btn-primary" href="{{ route('library.inventory.export', request()->query()) }}">Export CSV</a>
            <button class="btn btn-outline-secondary" type="button" onclick="window.print()">Print</button>
            <a class="btn btn-outline-secondary" href="{{ route('library.index') }}">Library</a>
        </div>
    </section>

    <section class="student-insight-grid">
        <article class="student-insight-card is-primary">
            <span>Total copies</span>
            <strong>{{ number_format($totalCopies) }}</strong>
            <p>{{ number_format((int) $statusCounts->get('available', 0)) }} available</p>
        </article>
        <article class="student-insight-card">
            <span>On loan</span>
            <strong>{{ number_format((int) $statusCounts->get('on_loan', 0)) }}</strong>
            <p>Currently outside the library</p>
        </article>
        <article class="student-insight-card">
            <span>Problem copies</span>
            <strong>{{ number_format((int) $statusCounts->get('damaged', 0) + (int) $statusCounts->get('lost', 0)) }}</strong>
            <p>{{ number_format($lostValue + $damagedValue) }} AFN recorded value</p>
        </article>
        <article class="student-insight-card">
            <span>Archived</span>
            <strong>{{ number_format((int) $statusCounts->get('archived', 0)) }}</strong>
            <p>{{ $emptyBooks->count() }} books without available copies</p>
        </article>
    </section>

    <section class="student-workspace-panel">
        <div class="student-panel-head">
            <div>
                <span class="student-panel-label">Filters</span>
                <h2>Find copies</h2>
                <p>Search by copy code, barcode, title, author, ISBN, shelf, status, or category.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('library.inventory.report') }}" class="library-filter-row mb-0">
            <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Copy code, barcode, title, author, ISBN">
            <select class="form-control" name="status">
                <option value="">All copy statuses</option>
                @foreach ($statusMeta as $value => $meta)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $meta['label'] }}</option>
                @endforeach
            </select>
            <input class="form-control" name="shelf" value="{{ $filters['shelf'] ?? '' }}" placeholder="Shelf">
            <input class="form-control" name="category" value="{{ $filters['category'] ?? '' }}" placeholder="Category">
            <button class="btn btn-primary" type="submit">Search</button>
            <a class="btn btn-outline-secondary" href="{{ route('library.inventory.export', request()->query()) }}">CSV</a>
            <a class="btn btn-dark" href="{{ route('library.inventory.report') }}">Clear</a>
        </form>
    </section>

    <section class="admin-dashboard-grid">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Problems</span>
                    <h2>Damaged and lost</h2>
                    <p>Copies that need repair, replacement, or follow-up.</p>
                </div>
            </div>
            <div class="student-timeline-list">
                @forelse ($problemCopies as $copy)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">!</span>
                        <div>
                            <strong>{{ $copy->book?->title ?: 'Unknown book' }}</strong>
                            <p>{{ $copy->copy_code }} - {{ ucfirst($copy->status) }} - Shelf {{ $copy->shelf_code ?: 'N/A' }} - {{ number_format((int) $copy->purchase_price) }} AFN</p>
                        </div>
                        @if ($canWriteLibrary && $copy->book)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.books.edit', $copy->book) }}">Open</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">No damaged or lost copies right now.</div>
                @endforelse
            </div>
        </div>

        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Shortage</span>
                    <h2>No available copies</h2>
                    <p>Book titles that cannot currently be loaned.</p>
                </div>
            </div>
            <div class="student-timeline-list">
                @forelse ($emptyBooks as $book)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">0</span>
                        <div>
                            <strong>{{ $book->title }}</strong>
                            <p>{{ $book->author ?: 'Unknown author' }} - {{ $book->total_copies }} total copies</p>
                        </div>
                        @if ($canWriteLibrary)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.books.edit', $book) }}">Edit</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">Every title has at least one available copy.</div>
                @endforelse
            </div>
        </div>

        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Status</span>
                    <h2>Copy summary</h2>
                    <p>Inventory count by physical-copy status.</p>
                </div>
            </div>
            <div class="admin-finance-stack">
                @foreach ($statusMeta as $value => $meta)
                    <span><strong>{{ number_format((int) $statusCounts->get($value, 0)) }}</strong>{{ $meta['label'] }}</span>
                @endforeach
            </div>
        </div>
    </section>

    <section class="student-workspace-panel">
        <div class="student-panel-head">
            <div>
                <span class="student-panel-label">Copies</span>
                <h2>Filtered inventory</h2>
                <p>{{ $copies->count() }} copies match the current filters.</p>
            </div>
        </div>

        <div class="student-timeline-list">
            @forelse ($copies as $copy)
                @php($meta = $statusMeta[$copy->status] ?? ['label' => $copy->status, 'class' => 'badge-outline-secondary'])
                <div class="student-timeline-item">
                    <span class="student-timeline-icon">C</span>
                    <div>
                        <strong>{{ $copy->book?->title ?: 'Unknown book' }}</strong>
                        <p>{{ $copy->copy_code }} - {{ $copy->barcode ?: 'No barcode' }} - Shelf {{ $copy->shelf_code ?: 'N/A' }} - Value {{ number_format((int) $copy->purchase_price) }} AFN</p>
                    </div>
                    <span class="badge {{ $meta['class'] }}">{{ $meta['label'] }}</span>
                    @if ($canWriteLibrary && $copy->book)
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('library.books.edit', $copy->book) }}">Manage</a>
                    @endif
                </div>
            @empty
                <div class="student-directory-empty">No copies matched the current filters.</div>
            @endforelse
        </div>
    </section>
@endsection
