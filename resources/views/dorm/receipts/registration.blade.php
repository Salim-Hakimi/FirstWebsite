@extends('admin.layout')

@section('title', 'Registration Receipt - Fanous Admin')

@section('content')
    <style>
        .receipt-actions {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .admission-receipt {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(108, 114, 147, .18);
            border-radius: 10px;
            background: #f8fafc;
            color: #111827;
            padding: 34px;
        }

        .admission-receipt::before {
            content: "";
            position: absolute;
            inset: 0 0 auto;
            height: 6px;
            background: linear-gradient(90deg, #0090e7, #00d25b);
        }

        .admission-receipt-head,
        .admission-total,
        .receipt-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admission-receipt-head {
            gap: 24px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 22px;
        }

        .admission-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admission-mark {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: #111827;
            color: #00d25b;
            font-size: 26px;
            font-weight: 900;
        }

        .admission-brand h1,
        .admission-number strong,
        .admission-total strong {
            margin: 0;
            line-height: 1.3;
        }

        .admission-brand span,
        .admission-number span,
        .admission-field span,
        .admission-row span,
        .admission-signature span {
            display: block;
            color: #6b7280;
            font-size: 13px;
            font-weight: 700;
        }

        .admission-number {
            text-align: right;
        }

        .admission-number strong {
            display: block;
            margin-top: 4px;
            font-size: 22px;
        }

        .admission-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 24px;
        }

        .admission-field,
        .admission-line-items {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            padding: 14px 16px;
        }

        .admission-field strong {
            display: block;
            margin-top: 4px;
            color: #111827;
            font-size: 16px;
            overflow-wrap: anywhere;
        }

        .admission-line-items {
            display: grid;
            gap: 12px;
            grid-column: 1 / -1;
        }

        .admission-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-bottom: 1px solid #eef2f7;
            padding-bottom: 10px;
        }

        .admission-row:last-child {
            border-bottom: 0;
            padding-bottom: 0;
        }

        .admission-row strong {
            color: #111827;
            font-size: 17px;
        }

        .admission-total {
            gap: 18px;
            margin-top: 22px;
            border-radius: 8px;
            background: #111827;
            color: #ffffff;
            padding: 20px;
        }

        .admission-total span {
            color: rgba(255, 255, 255, .72);
            font-weight: 700;
        }

        .admission-total strong {
            font-size: 32px;
        }

        .admission-footer {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 28px;
            margin-top: 34px;
        }

        .admission-signature {
            border-top: 1px solid #9ca3af;
            padding-top: 10px;
        }

        .admission-watermark {
            position: absolute;
            right: 24px;
            bottom: 18px;
            color: rgba(17, 24, 39, .05);
            font-size: 68px;
            font-weight: 900;
            pointer-events: none;
        }

        @media print {
            @page {
                size: 80mm 140mm;
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

            .admission-receipt {
                position: fixed;
                inset: 0 auto auto 0;
                width: 80mm;
                max-height: 140mm;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 5mm;
                font-size: 9px;
                overflow: hidden;
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .admission-receipt-head,
            .admission-total {
                gap: 8px;
                padding-bottom: 8px;
            }

            .admission-grid {
                grid-template-columns: 1fr;
                gap: 6px;
                margin-top: 8px;
            }

            .admission-field,
            .admission-line-items {
                border-radius: 4px;
                padding: 6px 8px;
            }

            .admission-total {
                margin-top: 8px;
                padding: 8px;
            }

            .admission-total strong {
                font-size: 18px;
            }

            .admission-footer {
                gap: 12px;
                margin-top: 16px;
            }
        }

        @media (max-width: 680px) {
            .admission-receipt-head,
            .admission-total,
            .receipt-actions {
                align-items: stretch;
                flex-direction: column;
            }

            .admission-number {
                text-align: left;
            }

            .admission-grid,
            .admission-footer {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="receipt-actions">
        <div>
            <button class="btn btn-primary mr-2" type="button" onclick="window.print()">Print receipt</button>
            <a class="btn btn-dark" href="{{ $backRoute }}">Student profile</a>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('dorm.students.index') }}">Back to students</a>
    </div>

    <article class="admission-receipt">
        <div class="admission-receipt-head">
            <div class="admission-brand">
                <div class="admission-mark">F</div>
                <div>
                    <h1>Dorm Registration Receipt</h1>
                    <span>Fanous dormitory admission payment</span>
                </div>
            </div>

            <div class="admission-number">
                <span>Receipt number</span>
                <strong>{{ $receiptNumber }}</strong>
            </div>
        </div>

        <div class="admission-grid">
            <div class="admission-field">
                <span>Student name</span>
                <strong>{{ $student->full_name }}</strong>
            </div>
            <div class="admission-field">
                <span>Father name</span>
                <strong>{{ $student->father_name ?: 'Not recorded' }}</strong>
            </div>
            <div class="admission-field">
                <span>Phone number</span>
                <strong>{{ $student->phone ?: 'Not recorded' }}</strong>
            </div>
            <div class="admission-field">
                <span>Room / bed</span>
                <strong>
                    Room {{ $student->room?->room_number ?: ($student->room_number ?: 'Not recorded') }}
                    - Bed {{ $student->bed_number ?: 'Not recorded' }}
                </strong>
            </div>
            <div class="admission-field">
                <span>Payment status</span>
                <strong>{{ ucfirst($paymentStatus) }}</strong>
            </div>
            <div class="admission-field">
                <span>Paid date</span>
                <strong>{{ $paidAt?->format('Y-m-d') ?: now()->format('Y-m-d') }}</strong>
            </div>

            <div class="admission-line-items">
                @foreach ($lineItems as $item)
                    <div class="admission-row">
                        <span>{{ $item['label'] }}</span>
                        <strong>{{ number_format($item['amount']) }} AFN</strong>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="admission-total">
            <span>Total collected at registration</span>
            <strong>{{ number_format($totalAmount) }} AFN</strong>
        </div>

        <div class="admission-footer">
            <div class="admission-signature">
                <strong>Admin / collector signature</strong>
                <span>{{ $student->registeredBy?->name ?: 'Fanous admin' }}</span>
            </div>
            <div class="admission-signature">
                <strong>Student / guardian signature</strong>
                <span>{{ $student->full_name }}</span>
            </div>
        </div>

        <div class="admission-watermark">FANOUS</div>
    </article>
@endsection
