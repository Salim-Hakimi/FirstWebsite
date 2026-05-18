<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>گزارش مالی لیلیه فانوس</title>
    <style>
        :root {
            --primary: #0891b2;
            --primary-soft: #e0f7fb;
            --bg: #f4f7fb;
            --card: #ffffff;
            --border: #d8e4ec;
            --text: #101827;
            --muted: #64748b;
            --success: #059669;
            --danger: #dc2626;
            --shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
        }

        * { box-sizing: border-box; }

        html,
        body {
            margin: 0;
            overflow-x: hidden;
            background: var(--bg);
            color: var(--text);
            font-family: "Vazirmatn", "Estedad", Tahoma, system-ui, sans-serif;
        }

        .report-shell {
            width: 100%;
            padding: clamp(16px, 2vw, 28px);
        }

        .report-hero,
        .report-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: var(--shadow);
        }

        .report-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: clamp(18px, 2vw, 28px);
            margin-bottom: 18px;
        }

        .kicker {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 8px 14px;
            background: var(--primary-soft);
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        h1 {
            margin-top: 12px;
            font-size: clamp(24px, 3vw, 34px);
            font-weight: 950;
            line-height: 1.35;
        }

        .report-hero p,
        .report-card p,
        .stat small {
            color: var(--muted);
            font-size: 13px;
            line-height: 1.9;
        }

        .toolbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            min-height: 42px;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 10px 16px;
            background: #fff;
            color: var(--text);
            font-weight: 900;
            text-decoration: none;
            cursor: pointer;
            transition: 180ms ease;
        }

        .btn:hover {
            transform: translateY(-1px);
            border-color: rgba(8, 145, 178, 0.45);
            box-shadow: 0 12px 24px rgba(8, 145, 178, 0.14);
        }

        .btn-primary {
            border-color: var(--primary);
            background: var(--primary);
            color: #fff;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat {
            display: grid;
            gap: 8px;
            min-width: 0;
            padding: 18px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
        }

        .stat span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 800;
        }

        .stat strong {
            font-size: clamp(22px, 2.4vw, 30px);
            line-height: 1;
            font-weight: 950;
        }

        .stat.is-income strong { color: var(--success); }
        .stat.is-expense strong { color: var(--danger); }

        .report-card {
            padding: clamp(16px, 2vw, 22px);
        }

        .report-card h2 {
            margin-bottom: 6px;
            font-size: 20px;
            font-weight: 950;
        }

        .table-wrap {
            width: 100%;
            margin-top: 16px;
            overflow-x: auto;
            border: 1px solid var(--border);
            border-radius: 18px;
        }

        table {
            width: 100%;
            min-width: 960px;
            border-collapse: collapse;
            background: #fff;
        }

        th,
        td {
            padding: 13px 14px;
            border-bottom: 1px solid #e8eef3;
            text-align: start;
            vertical-align: middle;
            font-size: 13px;
            white-space: nowrap;
        }

        th {
            background: #eefcff;
            color: #0e7490;
            font-weight: 950;
        }

        tbody tr:hover {
            background: #f8fdff;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 10px;
            background: #ecfdf5;
            color: #047857;
            font-size: 12px;
            font-weight: 900;
        }

        .badge.is-expense {
            background: #fef2f2;
            color: #b91c1c;
        }

        .ltr-text {
            direction: ltr;
            unicode-bidi: plaintext;
            text-align: end;
        }

        .empty {
            padding: 18px;
            border-radius: 16px;
            background: #eefcff;
            color: var(--muted);
            text-align: center;
            font-weight: 800;
        }

        @media (max-width: 760px) {
            .report-hero {
                align-items: stretch;
                flex-direction: column;
            }

            .toolbar {
                justify-content: flex-start;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }

        @media print {
            body { background: #fff; }
            .report-shell { padding: 0; }
            .toolbar { display: none; }
            .report-hero,
            .report-card,
            .stat {
                box-shadow: none;
                border-color: #d1d5db;
            }
        }
    </style>
</head>
<body>
    @php
        use App\Support\Locale;

        $balance = (int) $incomeTotal - (int) $expenseTotal;
        $fromDate = $filters['date_from'] ?? 'همه تاریخ‌ها';
        $toDate = $filters['date_to'] ?? 'همه تاریخ‌ها';
    @endphp

    <main class="report-shell">
        <section class="report-hero">
            <div>
                <span class="kicker">گزارش مالی</span>
                <h1>گزارش مالی لیلیه فانوس</h1>
                <p>از تاریخ {{ Locale::number($fromDate) }} تا {{ Locale::number($toDate) }}</p>
            </div>
            <div class="toolbar">
                <button class="btn btn-primary" type="button" onclick="window.print()">چاپ / PDF</button>
                <a class="btn" href="{{ route('admin.finance.export', $filters) }}">خروجی Excel</a>
                <a class="btn" href="{{ route('admin.finance.index', $filters) }}">بازگشت</a>
            </div>
        </section>

        <section class="summary-grid" aria-label="خلاصه مالی">
            <article class="stat is-income">
                <span>مجموع درآمد</span>
                <strong>{{ Locale::money($incomeTotal) }}</strong>
                <small>پرداخت کارت، فیس مصارف و کمک‌های عمومی ثبت‌شده.</small>
            </article>
            <article class="stat is-expense">
                <span>مجموع مصارف</span>
                <strong>{{ Locale::money($expenseTotal) }}</strong>
                <small>معاشات، ترمیمات، خریدها و مصارف عمومی لیلیه.</small>
            </article>
            <article class="stat">
                <span>توازن مالی</span>
                <strong>{{ Locale::money($balance) }}</strong>
                <small>درآمد منهای مصارف در محدوده انتخاب‌شده.</small>
            </article>
        </section>

        <section class="report-card">
            <h2>جزئیات تراکنش‌ها</h2>
            <p>تمام ثبت‌های مالی مطابق فیلترهای انتخاب‌شده در این جدول نمایش داده می‌شود.</p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>نوع</th>
                            <th>دسته‌بندی</th>
                            <th>شخص / پروژه</th>
                            <th>مبلغ</th>
                            <th>روش پرداخت</th>
                            <th>وضعیت</th>
                            <th>شماره رسید</th>
                            <th>سند</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_date ? Locale::number($transaction->transaction_date->format('Y/m/d')) : 'ثبت نشده' }}</td>
                                <td>
                                    <span class="badge {{ $transaction->type === 'expense' ? 'is-expense' : '' }}">
                                        {{ $transaction->type === 'income' ? 'درآمد' : 'مصرف' }}
                                    </span>
                                </td>
                                <td>{{ $transaction->category?->name ?: 'بدون دسته' }}</td>
                                <td>{{ $transaction->displayPerson() ?: 'ثبت نشده' }}</td>
                                <td>{{ Locale::money((int) $transaction->amount) }}</td>
                                <td>{{ $paymentMethods[$transaction->payment_method] ?? $transaction->payment_method }}</td>
                                <td>{{ $statusLabels[$transaction->status] ?? $transaction->status }}</td>
                                <td class="ltr-text">{{ $transaction->receipt_number ?: 'ندارد' }}</td>
                                <td>{{ $transaction->attachments->isNotEmpty() ? 'دارد' : 'ندارد' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty">هیچ ثبت مالی در این گزارش وجود ندارد.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
