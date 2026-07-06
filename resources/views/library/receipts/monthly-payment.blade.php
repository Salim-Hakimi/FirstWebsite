<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>بیل ماهانه {{ $member->full_name }}</title>
    @php
        use App\Support\Locale;

        $paidDate = \Illuminate\Support\Carbon::parse($receipt['paid_at']);
    @endphp
    <style>
        @font-face {
            font-family: 'Vazir';
            src: url('{{ asset('font/vazir-font-v16.1.0/Vazir.woff2') }}') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Vazir';
            src: url('{{ asset('font/vazir-font-v16.1.0/Vazir-Bold.woff2') }}') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html,
        body {
            margin: 0;
            background: #eef3f7;
            color: #0f172a;
            font-family: 'Vazir', Tahoma, Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 18px;
        }

        .bill-shell {
            display: grid;
            gap: 10px;
            justify-items: center;
        }

        .bill-actions {
            width: 58mm;
            display: flex;
            justify-content: space-between;
            gap: 6px;
        }

        .btn {
            min-height: 30px;
            border: 1px solid #0f766e;
            border-radius: 8px;
            background: #0f766e;
            color: #fff;
            padding: 5px 9px;
            font: inherit;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .btn.secondary {
            background: #fff;
            color: #0f766e;
        }

        .monthly-bill {
            width: 58mm;
            overflow: hidden;
            border: 1px solid #c8d8df;
            border-radius: 8px;
            background: #ffffff;
            padding: 8px;
            box-shadow: 0 16px 38px rgba(15, 23, 42, .16);
            font-size: 9.4px;
            line-height: 1.55;
        }

        .bill-head {
            display: grid;
            gap: 2px;
            text-align: center;
            border-bottom: 1px dashed #9fb5c1;
            padding-bottom: 5px;
            margin-bottom: 4px;
        }

        .bill-head strong {
            font-size: 12px;
            font-weight: 700;
        }

        .bill-head span {
            color: #64748b;
            font-size: 8.2px;
            font-weight: 700;
        }

        .bill-row,
        .bill-total {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            border-bottom: 1px dashed #d7e3ea;
            padding: 3px 0;
        }

        .bill-row span {
            color: #64748b;
            font-weight: 700;
            white-space: nowrap;
        }

        .bill-row strong {
            color: #0f172a;
            font-weight: 700;
            text-align: left;
            overflow-wrap: anywhere;
        }

        .bill-lines {
            margin-top: 4px;
            border-top: 1px solid #dbe7ef;
            border-bottom: 1px solid #dbe7ef;
            padding-block: 2px;
        }

        .bill-total {
            align-items: center;
            margin-top: 5px;
            border: 0;
            border-radius: 7px;
            background: #0f766e;
            color: #ffffff;
            padding: 6px 7px;
        }

        .bill-total span,
        .bill-total strong {
            color: #ffffff;
            font-weight: 700;
        }

        .bill-total strong {
            font-size: 14px;
        }

        .bill-note {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 7.8px;
            line-height: 1.65;
            text-align: center;
        }

        .bill-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-top: 12px;
        }

        .bill-signatures div {
            border-top: 1px solid #94a3b8;
            padding-top: 3px;
            color: #334155;
            font-size: 8.3px;
            font-weight: 700;
            text-align: center;
        }

        .ltr-text {
            direction: ltr;
            unicode-bidi: plaintext;
            text-align: left;
        }

        @media print {
            @page {
                size: 58mm 92mm;
                margin: 0;
            }

            html,
            body {
                width: 58mm;
                height: 92mm;
                min-width: 58mm;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
                background: #ffffff !important;
            }

            body {
                display: block !important;
            }

            .bill-shell {
                position: fixed;
                inset: 0 auto auto 0;
                display: block;
                width: 58mm;
                height: 92mm;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden !important;
            }

            .bill-actions {
                display: none !important;
            }

            .monthly-bill {
                width: 58mm;
                height: 92mm;
                border: 0;
                border-radius: 0;
                box-shadow: none;
                padding: 4mm;
                break-inside: avoid;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <main class="bill-shell">
        <div class="bill-actions">
            <button class="btn" type="button" onclick="window.print()">چاپ بیل</button>
            <a class="btn secondary" href="{{ route('library.members.show', $member) }}">برگشت</a>
        </div>

        <article class="monthly-bill">
            <header class="bill-head">
                <strong>بیل ماهانه کتاب‌خانه فانوس</strong>
                <span>رسید فیس ماهانه، کارت جدید نیست</span>
                <span class="ltr-text">{{ $receiptNumber }}</span>
            </header>

            <div class="bill-row"><span>نام</span><strong>{{ $member->full_name }}</strong></div>
            <div class="bill-row"><span>کد</span><strong class="ltr-text">{{ $member->member_code ?: 'ثبت نشده' }}</strong></div>
            <div class="bill-row"><span>واتساپ</span><strong class="ltr-text">{{ $member->phone ?: 'ثبت نشده' }}</strong></div>
            <div class="bill-row"><span>ماه</span><strong>{{ Locale::number($paidDate->format('Y/m')) }}</strong></div>
            <div class="bill-row"><span>تاریخ</span><strong>{{ Locale::number($paidDate->format('Y/m/d')) }}</strong></div>
            <div class="bill-row"><span>موعد بعدی</span><strong>{{ $member->next_payment_due_at ? Locale::number($member->next_payment_due_at->format('Y/m/d')) : 'ثبت نشده' }}</strong></div>

            <div class="bill-lines">
                <div class="bill-row"><span>فیس ماهانه</span><strong>{{ Locale::money((int) $receipt['fee_amount']) }}</strong></div>
                <div class="bill-row"><span>جریمه</span><strong>{{ Locale::money((int) $receipt['fine_amount']) }}</strong></div>
            </div>

            <div class="bill-total">
                <span>مجموع</span>
                <strong>{{ Locale::money((int) $receipt['total_amount']) }}</strong>
            </div>

            <p class="bill-note">این بیل فقط برای پرداخت ماهانه است. کارت عضویت هر شش ماه یک‌بار تمدید می‌شود.</p>

            <div class="bill-row"><span>ثبت‌کننده</span><strong>{{ $receipt['recorded_by'] ?: 'کتابدار' }}</strong></div>

            <div class="bill-signatures">
                <div>امضای کتابدار</div>
                <div>امضای عضو</div>
            </div>
        </article>
    </main>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => window.print(), 300);
        });
    </script>
</body>
</html>
