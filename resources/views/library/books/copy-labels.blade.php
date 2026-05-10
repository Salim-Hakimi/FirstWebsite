@extends('admin.layout')

@section('title', 'Book Copy Labels - Fanous Admin')

@section('content')
    <style>
        .label-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .label-sheet {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 12px;
        }

        .copy-label {
            min-height: 132px;
            display: grid;
            align-content: space-between;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: #ffffff;
            color: #111827;
            padding: 10px;
            page-break-inside: avoid;
        }

        .copy-label strong,
        .copy-label span {
            display: block;
            overflow-wrap: anywhere;
        }

        .copy-label strong {
            font-size: 13px;
            line-height: 1.3;
        }

        .copy-label span {
            color: #4b5563;
            font-size: 11px;
        }

        .copy-label svg {
            width: 100%;
            height: 58px;
            margin: 8px 0 4px;
        }

        .copy-label-code {
            text-align: center;
            font-family: Consolas, monospace;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1px;
        }

        @media print {
            .sidebar,
            .navbar,
            .footer,
            .label-actions {
                display: none !important;
            }

            .page-body-wrapper,
            .main-panel,
            .content-wrapper {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            .label-sheet {
                grid-template-columns: repeat(3, 1fr);
                gap: 8mm;
            }

            .copy-label {
                box-shadow: none;
            }
        }
    </style>

    <div class="label-actions">
        <div>
            <button class="btn btn-primary mr-2" type="button" onclick="window.print()">Print labels</button>
            <a class="btn btn-dark" href="{{ route('library.books.edit', $book) }}">Back to book</a>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('library.index') }}">Library</a>
    </div>

    <section class="label-sheet">
        @forelse ($labels as $label)
            @php
                $copy = $label['copy'];
            @endphp
            <article class="copy-label">
                <div>
                    <strong>{{ $book->title }}</strong>
                    <span>{{ $book->author ?: 'Unknown author' }} - Shelf {{ $copy->shelf_code ?: ($book->shelf_code ?: 'N/A') }}</span>
                </div>
                <div>
                    {!! $label['barcodeSvg'] !!}
                    <div class="copy-label-code">{{ $copy->barcode ?: $copy->copy_code }}</div>
                </div>
                <span>Status: {{ $copy->status }}</span>
            </article>
        @empty
            <div class="student-directory-empty">No physical copies exist for this book yet.</div>
        @endforelse
    </section>
@endsection
