@extends('admin.layout')

@section('title', 'Library Payment Receipt - Fanous Admin')

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
            border: 1px solid rgba(108, 114, 147, .18);
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
            background: linear-gradient(90deg, #0090e7, #00d25b);
        }

        .receipt-head,
        .receipt-total,
        .receipt-actions,
        .receipt-line {
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
            font-weight: 900;
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
        .receipt-signature span,
        .receipt-line span {
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

        .receipt-field,
        .receipt-lines {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #ffffff;
            padding: 14px 16px;
        }

        .receipt-field strong,
        .receipt-line strong {
            display: block;
            margin-top: 4px;
            color: #111827;
            font-size: 16px;
            overflow-wrap: anywhere;
        }

        .receipt-lines {
            display: grid;
            gap: 12px;
            grid-column: 1 / -1;
        }

        .receipt-line {
            border-bottom: 1px solid #eef2f7;
            padding-bottom: 10px;
        }

        .receipt-line:last-child {
            border-bottom: 0;
            padding-bottom: 0;
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
            color: rgba(255, 255, 255, .72);
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

        @media print {
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
                border: 0;
                border-radius: 0;
            }
        }

        @media (max-width: 680px) {
            .receipt-head,
            .receipt-total,
            .receipt-actions,
            .receipt-line {
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
            <a class="btn btn-dark" href="{{ route('library.members.show', $member) }}">Member profile</a>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('library.index') }}">Back to library</a>
    </div>

    <article class="receipt-paper">
        <div class="receipt-head">
            <div class="receipt-brand">
                <div class="receipt-mark">F</div>
                <div>
                    <h1>Library Monthly Payment</h1>
                    <span>Fanous library membership fee receipt</span>
                </div>
            </div>
            <div class="receipt-number">
                <span>Receipt number</span>
                <strong>{{ $receiptNumber }}</strong>
            </div>
        </div>

        <div class="receipt-grid">
            <div class="receipt-field"><span>Member</span><strong>{{ $member->full_name }}</strong></div>
            <div class="receipt-field"><span>Member code</span><strong>{{ $member->member_code ?: 'N/A' }}</strong></div>
            <div class="receipt-field"><span>Phone</span><strong>{{ $member->phone ?: 'N/A' }}</strong></div>
            <div class="receipt-field"><span>Paid date</span><strong>{{ $receipt['paid_at'] }}</strong></div>
            <div class="receipt-field"><span>Next due</span><strong>{{ $member->next_payment_due_at?->format('Y-m-d') ?: 'N/A' }}</strong></div>
            <div class="receipt-field"><span>Recorded by</span><strong>{{ $receipt['recorded_by'] ?: 'Librarian' }}</strong></div>

            <div class="receipt-lines">
                <div class="receipt-line"><span>Monthly fee</span><strong>{{ number_format((int) $receipt['fee_amount']) }} AFN</strong></div>
                <div class="receipt-line"><span>Late fine</span><strong>{{ number_format((int) $receipt['fine_amount']) }} AFN</strong></div>
            </div>
        </div>

        <div class="receipt-total">
            <span>Total paid</span>
            <strong>{{ number_format((int) $receipt['total_amount']) }} AFN</strong>
        </div>

        <div class="receipt-footer">
            <div class="receipt-signature">
                <strong>Librarian signature</strong>
                <span>{{ $receipt['recorded_by'] ?: 'Librarian' }}</span>
            </div>
            <div class="receipt-signature">
                <strong>Member signature</strong>
                <span>{{ $member->full_name }}</span>
            </div>
        </div>
    </article>
@endsection
