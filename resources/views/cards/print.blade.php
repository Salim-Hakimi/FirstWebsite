<!DOCTYPE html>
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>چاپ کارت {{ $card->card_number }}</title>
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

            :root {
                --card-green: #0f5138;
                --card-green-2: #087f5b;
                --card-teal: #0ea5a4;
                --card-gold: #f7c948;
                --card-ink: #10241d;
                --card-muted: #58706a;
                --card-line: rgba(15, 81, 56, .16);
                --card-paper: #fffdf6;
                --card-soft: #eefaf4;
            }

            * {
                box-sizing: border-box;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body {
                min-height: 100vh;
                display: grid;
                place-items: center;
                margin: 0;
                background:
                    radial-gradient(circle at 20% 15%, rgba(14, 165, 164, .13), transparent 30%),
                    linear-gradient(135deg, #eef6f8 0%, #f7fafc 100%);
                color: var(--card-ink);
                font-family: 'Vazir', Tahoma, Arial, sans-serif;
            }

            .print-shell {
                display: grid;
                gap: 18px;
                justify-items: center;
                padding: 28px;
            }

            .id-card {
                position: relative;
                width: 86mm;
                min-height: 54mm;
                display: grid;
                grid-template-rows: auto 1fr auto;
                overflow: hidden;
                border: 1px solid rgba(8, 127, 91, .72);
                border-radius: 14px;
                background: var(--card-paper);
                box-shadow: 0 24px 60px rgba(15, 23, 42, .18);
            }

            .id-card::before {
                content: "";
                position: absolute;
                inset-block-start: -22mm;
                inset-inline-start: -16mm;
                inline-size: 54mm;
                block-size: 54mm;
                border-radius: 999px;
                background: rgba(14, 165, 164, .13);
            }

            .id-card::after {
                content: "";
                position: absolute;
                inset-block-end: -24mm;
                inset-inline-end: -18mm;
                inline-size: 56mm;
                block-size: 56mm;
                border-radius: 999px;
                background: rgba(247, 201, 72, .15);
            }

            .card-head,
            .card-body,
            .card-foot {
                position: relative;
                z-index: 1;
            }

            .card-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                min-height: 14mm;
                padding: 8px 11px;
                background: linear-gradient(135deg, var(--card-green), var(--card-green-2));
                color: #fff;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 8px;
                min-width: 0;
            }

            .brand-logo {
                width: 10mm;
                height: 10mm;
                flex-shrink: 0;
                border-radius: 9px;
                object-fit: contain;
                background: #fff;
                padding: 2px;
            }

            .brand-text {
                display: grid;
                gap: 1px;
            }

            .brand-text strong {
                font-size: 13px;
                line-height: 1.2;
                font-weight: 700;
                white-space: nowrap;
            }

            .brand-text span {
                font-size: 8px;
                opacity: .78;
                white-space: nowrap;
            }

            .scope {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(255, 255, 255, .24);
                border-radius: 999px;
                padding: 5px 9px;
                background: rgba(255, 255, 255, .12);
                font-size: 9px;
                font-weight: 700;
                white-space: nowrap;
            }

            .card-body {
                display: grid;
                grid-template-columns: 24mm minmax(0, 1fr);
                gap: 10px;
                padding: 10px 11px 7px;
            }

            .photo-box {
                display: grid;
                place-items: center;
                width: 24mm;
                height: 31mm;
                overflow: hidden;
                border: 1px dashed rgba(8, 127, 91, .55);
                border-radius: 10px;
                background: #f6fbf7;
                color: var(--card-muted);
                font-size: 9px;
                line-height: 1.65;
                text-align: center;
            }

            .photo-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                object-position: center top;
                display: block;
            }

            .details {
                display: grid;
                align-content: start;
                gap: 4px;
                min-width: 0;
                font-size: 9.5px;
                line-height: 1.45;
            }

            .details h1 {
                margin: 0 0 2px;
                color: var(--card-green);
                font-size: 15.5px;
                line-height: 1.3;
                font-weight: 700;
            }

            .row {
                display: grid;
                grid-template-columns: 21mm minmax(0, 1fr);
                gap: 6px;
                align-items: center;
                border-bottom: 1px solid var(--card-line);
                padding-bottom: 2px;
            }

            .row span {
                color: var(--card-muted);
                white-space: nowrap;
            }

            .row strong {
                min-width: 0;
                overflow: hidden;
                color: #10241d;
                font-weight: 700;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .ltr-text {
                direction: ltr;
                unicode-bidi: plaintext;
                text-align: left;
            }

            .card-foot {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                min-height: 9mm;
                border-top: 1px solid rgba(15, 81, 56, .13);
                padding: 6px 11px 8px;
                background: linear-gradient(90deg, rgba(238, 250, 244, .92), rgba(255, 253, 246, .96));
                font-size: 9px;
            }

            .price {
                color: var(--card-ink);
                font-weight: 700;
            }

            .status {
                border-radius: 999px;
                background: #dff8eb;
                color: #087f5b;
                padding: 4px 8px;
                font-size: 8.5px;
                font-weight: 700;
                white-space: nowrap;
            }

            .status.is-unpaid {
                background: #fff4d6;
                color: #9a6700;
            }

            .actions {
                display: flex;
                gap: 10px;
            }

            .btn {
                min-height: 42px;
                border: 1px solid #087f5b;
                border-radius: 12px;
                background: #087f5b;
                color: #ffffff;
                padding: 9px 16px;
                cursor: pointer;
                font: inherit;
                font-weight: 700;
                text-decoration: none;
                transition: .18s ease;
            }

            .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 12px 24px rgba(8, 127, 91, .18);
            }

            .btn.secondary {
                background: #ffffff;
                color: #087f5b;
            }

            @media print {
                @page {
                    size: 86mm 54mm;
                    margin: 0;
                }

                html,
                body {
                    width: 86mm;
                    min-height: 54mm;
                    background: #ffffff !important;
                }

                .print-shell {
                    width: 86mm;
                    min-height: 54mm;
                    padding: 0;
                }

                .actions {
                    display: none;
                }

                .id-card {
                    width: 86mm;
                    min-height: 54mm;
                    border-radius: 0;
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        @php
            use App\Support\Locale;

            $isDorm = $card->scope === 'dorm';
            $subject = $card->cardable;
            $photoPath = $subject?->profile_photo_path;
            $scopeLabel = $isDorm ? 'کارت لیلیه' : 'کارت کتابخانه';
            $brandLabel = $isDorm ? 'لیلیه فانوس' : 'کتابخانه فانوس';
            $brandSubLabel = $isDorm ? 'کارت رسمی محصل' : 'کارت عضویت کتابخانه';
            $paymentLabel = $card->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده';
            $phone = $subject?->phone ?: 'ثبت نشده';
            $printedAt = now();
        @endphp

        <main class="print-shell">
            <section class="id-card" aria-label="{{ $scopeLabel }}">
                <header class="card-head">
                    <div class="brand">
                        <img class="brand-logo" src="{{ asset('logo/logo.jpg') }}" alt="لوگوی فانوس">
                        <div class="brand-text">
                            <strong>{{ $brandLabel }}</strong>
                            <span>{{ $brandSubLabel }}</span>
                        </div>
                    </div>
                    <div class="scope">{{ $scopeLabel }}</div>
                </header>

                <div class="card-body">
                    <div class="photo-box">
                        @if ($photoPath)
                            <img src="{{ asset('storage/'.$photoPath) }}" alt="{{ $card->holder_name }}">
                        @else
                            جای عکس<br><span class="ltr-text">{{ $card->card_number }}</span>
                        @endif
                    </div>

                    <div class="details">
                        <h1>{{ $card->holder_name }}</h1>
                        <div class="row"><span>نام پدر</span><strong>{{ $card->father_name ?: 'ثبت نشده' }}</strong></div>
                        <div class="row"><span>{{ $isDorm ? 'آی‌دی کارت' : 'کد عضویت' }}</span><strong class="ltr-text">{{ $isDorm ? $card->card_number : ($subject?->member_code ?: $card->card_number) }}</strong></div>
                        <div class="row"><span>تاریخ صدور</span><strong>{{ $card->issued_at ? Locale::number($card->issued_at->format('Y/m/d')) : 'ثبت نشده' }}</strong></div>
                        <div class="row"><span>اعتبار تا</span><strong>{{ $card->expires_at ? Locale::number($card->expires_at->format('Y/m/d')) : 'ثبت نشده' }}</strong></div>
                        <div class="row"><span>زمان چاپ</span><strong>{{ Locale::number($printedAt->format('H:i:s')) }}</strong></div>

                        @if ($isDorm)
                            <div class="row"><span>اتاق</span><strong>{{ $subject?->room_number ?: 'ثبت نشده' }}</strong></div>
                        @else
                            <div class="row"><span>بخش</span><strong>{{ $subject?->department_or_grade ?: 'کتابخانه' }}</strong></div>
                        @endif

                        <div class="row"><span>تماس</span><strong class="ltr-text">{{ $phone }}</strong></div>
                    </div>
                </div>

                <footer class="card-foot">
                    <span class="price">قیمت کارت: {{ Locale::money(50) }}</span>
                    <span class="status {{ $card->payment_status === 'paid' ? '' : 'is-unpaid' }}">{{ $paymentLabel }}</span>
                </footer>
            </section>

            <div class="actions">
                <button class="btn" onclick="window.print()">چاپ کارت</button>
                <a class="btn secondary" href="{{ $isDorm ? route('dorm.students.index') : route('library.index') }}">بازگشت</a>
            </div>
        </main>

        <script>
            window.addEventListener('load', () => {
                setTimeout(() => window.print(), 350);
            });
        </script>
    </body>
</html>
