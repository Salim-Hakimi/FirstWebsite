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

            * {
                box-sizing: border-box;
            }

            body {
                min-height: 100vh;
                display: grid;
                place-items: center;
                margin: 0;
                background: #eef2f7;
                color: #10241d;
                font-family: 'Vazir', Tahoma, Arial, sans-serif;
            }

            .print-shell {
                display: grid;
                gap: 18px;
                justify-items: center;
                padding: 26px;
            }

            .id-card {
                width: 86mm;
                min-height: 54mm;
                display: grid;
                grid-template-rows: auto 1fr auto;
                overflow: hidden;
                border: 1px solid #1f7a38;
                border-radius: 10px;
                background: #fffdf7;
                box-shadow: 0 22px 50px rgba(15, 23, 42, .18);
            }

            .card-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                background: #123f32;
                color: #fffdf7;
                padding: 10px 12px;
            }

            .brand {
                display: flex;
                align-items: center;
                gap: 8px;
                font-weight: 700;
            }

            .mark {
                width: 32px;
                height: 32px;
                display: grid;
                place-items: center;
                border-radius: 8px;
                background: #f2c94c;
                color: #123f32;
                font-weight: 700;
            }

            .scope {
                font-size: 12px;
                opacity: .82;
            }

            .card-body {
                display: grid;
                grid-template-columns: 26mm 1fr;
                gap: 10px;
                padding: 12px;
            }

            .photo-box {
                display: grid;
                place-items: center;
                border: 1px dashed #8aa59b;
                border-radius: 8px;
                background: #f5f7f1;
                color: #45645a;
                font-size: 11px;
                text-align: center;
                overflow: hidden;
                aspect-ratio: 1 / 1.25;
            }

            .photo-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .details {
                display: grid;
                gap: 4px;
                font-size: 11px;
                line-height: 1.55;
            }

            .details h1 {
                margin: 0 0 2px;
                color: #123f32;
                font-size: 16px;
                line-height: 1.35;
            }

            .row {
                display: flex;
                justify-content: space-between;
                gap: 8px;
                border-bottom: 1px solid #edf1e6;
                padding-bottom: 2px;
            }

            .row span {
                color: #5b756c;
            }

            .row strong {
                text-align: left;
            }

            .card-foot {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 10px;
                border-top: 1px solid #e5eadf;
                padding: 8px 12px;
                font-size: 10px;
            }

            .status {
                border-radius: 999px;
                background: #e9f8ed;
                color: #1f7a38;
                padding: 4px 8px;
                font-weight: 700;
            }

            .actions {
                display: flex;
                gap: 10px;
            }

            .btn {
                min-height: 38px;
                border: 1px solid #1f7a38;
                border-radius: 8px;
                background: #1f7a38;
                color: #ffffff;
                padding: 8px 14px;
                cursor: pointer;
                font: inherit;
                font-weight: 700;
                text-decoration: none;
            }

            .btn.secondary {
                background: #ffffff;
                color: #1f7a38;
            }

            @media print {
                @page {
                    size: 86mm 54mm;
                    margin: 0;
                }

                body {
                    min-height: auto;
                    background: #ffffff;
                }

                .print-shell {
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
            $isDorm = $card->scope === 'dorm';
            $subject = $card->cardable;
            $photoPath = $subject?->profile_photo_path;
            $scopeLabel = $isDorm ? 'کارت لیلیه' : 'کارت کتاب‌خانه';
            $paymentLabel = $card->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده';
        @endphp

        <main class="print-shell">
            <section class="id-card">
                <header class="card-head">
                    <div class="brand"><span class="mark">ف</span><span>سیستم فانوس</span></div>
                    <div class="scope">{{ $scopeLabel }}</div>
                </header>

                <div class="card-body">
                    <div class="photo-box">
                        @if ($photoPath)
                            <img src="{{ asset('storage/'.$photoPath) }}" alt="{{ $card->holder_name }}">
                        @else
                            جای عکس<br>{{ $card->card_number }}
                        @endif
                    </div>
                    <div class="details">
                        <h1>{{ $card->holder_name }}</h1>
                        <div class="row"><span>نام پدر</span><strong>{{ $card->father_name ?: 'ثبت نشده' }}</strong></div>
                        <div class="row"><span>آی‌دی کارت</span><strong>{{ $card->card_number }}</strong></div>
                        <div class="row"><span>تاریخ ثبت</span><strong>{{ $card->issued_at?->format('Y-m-d') }}</strong></div>
                        <div class="row"><span>تاریخ ختم</span><strong>{{ $card->expires_at?->format('Y-m-d') }}</strong></div>
                        @if ($isDorm)
                            <div class="row"><span>اتاق</span><strong>{{ $subject?->room_number ?: 'ثبت نشده' }}</strong></div>
                            <div class="row"><span>تماس</span><strong>{{ $subject?->phone ?: 'ثبت نشده' }}</strong></div>
                        @else
                            <div class="row"><span>کد عضو</span><strong>{{ $subject?->member_code ?: 'ثبت نشده' }}</strong></div>
                            <div class="row"><span>تماس</span><strong>{{ $subject?->phone ?: 'ثبت نشده' }}</strong></div>
                        @endif
                    </div>
                </div>

                <footer class="card-foot">
                    <span>فیس: {{ number_format((float) $card->fee_amount, 0) }} افغانی</span>
                    <span class="status">{{ $paymentLabel }}</span>
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
