@extends('admin.layout')

@section('title', $title.' - Fanous Admin')

@section('content')
    <style>
        .receipt-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .receipt-paper {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 8px;
            background: #f8fafc;
            color: #111827;
            padding: 34px;
        }

        .receipt-paper::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 6px;
            background: linear-gradient(90deg, #0090e7, #00d25b, #fc424a);
        }

        .receipt-head,
        .receipt-total,
        .receipt-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .receipt-head {
            gap: 24px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 22px;
        }

        .receipt-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .receipt-mark {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: #111827;
            color: #00d25b;
            font-size: 26px;
            font-weight: 700;
        }

        .receipt-brand h1,
        .receipt-number strong,
        .receipt-total strong {
            margin: 0;
            line-height: 1.3;
        }

        .receipt-brand span,
        .receipt-number span,
        .receipt-field span,
        .receipt-signature span {
            display: block;
            color: #6b7280;
            font-size: 13px;
            font-weight: 700;
        }

        .receipt-number {
            text-align: right;
        }

        .receipt-number strong {
            display: block;
            margin-top: 4px;
            font-size: 22px;
        }

        .receipt-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 24px;
        }

        .receipt-field {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            padding: 14px 16px;
        }

        .receipt-field strong {
            display: block;
            margin-top: 4px;
            color: #111827;
            font-size: 16px;
            overflow-wrap: anywhere;
        }

        .receipt-field.full {
            grid-column: 1 / -1;
        }

        .receipt-total {
            gap: 18px;
            margin-top: 22px;
            border-radius: 8px;
            background: #111827;
            color: #ffffff;
            padding: 20px;
        }

        .receipt-total span {
            color: rgba(255, 255, 255, .7);
            font-weight: 700;
        }

        .receipt-total strong {
            font-size: 32px;
        }

        .receipt-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
            margin-top: 34px;
        }

        .receipt-signature {
            border-top: 1px solid #9ca3af;
            padding-top: 10px;
        }

        .receipt-watermark {
            position: absolute;
            right: 24px;
            bottom: 18px;
            color: rgba(17, 24, 39, .05);
            font-size: 68px;
            font-weight: 700;
            pointer-events: none;
        }

        @media print {
            @page {
                size: 80mm 120mm;
                margin: 0;
            }

            html,
            body {
                width: 80mm !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                background: #ffffff !important;
            }

            .sidebar,
            .navbar,
            .footer,
            .receipt-actions {
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

            .receipt-paper {
                position: fixed;
                inset: 0 auto auto 0;
                width: 80mm;
                max-height: 120mm;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 5mm;
                font-size: 9px;
                overflow: hidden;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .receipt-head,
            .receipt-total {
                gap: 8px;
                padding-bottom: 8px;
            }

            .receipt-grid {
                grid-template-columns: 1fr;
                gap: 6px;
                margin-top: 8px;
            }

            .receipt-field {
                border-radius: 4px;
                padding: 6px 8px;
            }

            .receipt-total {
                margin-top: 8px;
                padding: 8px;
            }

            .receipt-total strong {
                font-size: 18px;
            }

            .receipt-footer {
                gap: 12px;
                margin-top: 16px;
            }
        }

        @media (max-width: 680px) {
            .receipt-head,
            .receipt-total,
            .receipt-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .receipt-number {
                text-align: left;
            }

            .receipt-grid,
            .receipt-footer {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="receipt-actions">
        <div>
            <button class="btn btn-primary mr-2" type="button" onclick="window.print()">Print receipt</button>
            @if ($profileRoute)
                <a class="btn btn-dark" href="{{ $profileRoute }}">Student profile</a>
            @endif
        </div>
        <a class="btn btn-outline-secondary" href="{{ $backRoute }}">Back</a>
    </div>

    <article class="receipt-paper">
        <div class="receipt-head">
            <div class="receipt-brand">
                <div class="receipt-mark">F</div>
                <div>
                    <h1>{{ $title }}</h1>
                    <span>{{ $subtitle }} - Unified management system</span>
                </div>
            </div>

            <div class="receipt-number">
                <span>Receipt number</span>
                <strong>{{ $receiptNumber }}</strong>
            </div>
        </div>

        <div class="receipt-grid">
            <div class="receipt-field">
                <span>Record type</span>
                <strong>{{ $typeLabel }}</strong>
            </div>
            <div class="receipt-field">
                <span>Record date</span>
                <strong>{{ $date?->format('Y-m-d') ?: 'Not recorded' }}</strong>
            </div>
            <div class="receipt-field">
                <span>Period</span>
                <strong>{{ $period ?: 'Not recorded' }}</strong>
            </div>
            <div class="receipt-field">
                <span>Recorded by</span>
                <strong>{{ $recordedBy?->name ?: 'Unknown' }}</strong>
            </div>
            <div class="receipt-field">
                <span>Student name</span>
                <strong>{{ $student?->full_name ?: 'General expense' }}</strong>
            </div>
            <div class="receipt-field">
                <span>Room / bed</span>
                <strong>
                    @if ($student)
                        Room {{ $student->room?->room_number ?: ($student->room_number ?: 'Not recorded') }}
                        - Bed {{ $student->bed_number ?: 'Not recorded' }}
                    @else
                        Not linked to a specific student
                    @endif
                </strong>
            </div>
            <div class="receipt-field">
                <span>Father name</span>
                <strong>{{ $student?->father_name ?: 'Not recorded' }}</strong>
            </div>
            <div class="receipt-field">
                <span>Phone number</span>
                <strong>{{ $student?->phone ?: 'Not recorded' }}</strong>
            </div>
            @if (! empty($sourceLabel) || ! empty($source))
                <div class="receipt-field full">
                    <span>{{ $sourceLabel ?? 'Source' }}</span>
                    <strong>{{ $source ?: 'Not recorded' }}</strong>
                </div>
            @endif
            <div class="receipt-field full">
                <span>{{ $noteLabel }}</span>
                <strong>{{ $note ?: 'No note' }}</strong>
            </div>
        </div>

        <div class="receipt-total">
            <span>Recorded amount</span>
            <strong>{{ number_format($amount) }} AFN</strong>
        </div>

        <div class="receipt-footer">
            <div class="receipt-signature">
                <strong>Collector / recorder signature</strong>
                <span>{{ $recordedBy?->name ?: 'Unknown' }}</span>
            </div>
            <div class="receipt-signature">
                <strong>Student / verifier signature</strong>
                <span>{{ $student?->full_name ?: 'General expense' }}</span>
            </div>
        </div>

        <div class="receipt-watermark">FANOUS</div>
    </article>
@endsection
