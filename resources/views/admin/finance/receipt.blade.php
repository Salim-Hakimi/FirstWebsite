<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>رسید مالی {{ $transaction->transaction_number }}</title>
    <style>
        body { font-family: Tahoma, Arial, sans-serif; margin: 32px; color: #111827; }
        .receipt { max-width: 760px; margin: auto; border: 1px solid #d1d5db; padding: 28px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #111827; padding-bottom: 16px; margin-bottom: 20px; }
        .title { font-size: 24px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 10px; text-align: right; }
        .signatures { display: flex; gap: 40px; margin-top: 48px; }
        .signature { flex: 1; border-top: 1px solid #111827; padding-top: 8px; text-align: center; }
        .actions { max-width: 760px; margin: 16px auto; display: flex; gap: 8px; }
        .btn { padding: 10px 14px; border: 1px solid #111827; color: #111827; text-decoration: none; background: #fff; cursor: pointer; }
        @media print { .actions { display: none; } body { margin: 0; } .receipt { border: 0; } }
    </style>
</head>
<body>
    <div class="actions">
        <button class="btn" onclick="window.print()">چاپ رسید</button>
        <a class="btn" href="{{ route('admin.finance.index') }}">برگشت</a>
    </div>
    <div class="receipt">
        <div class="header">
            <div>
                <div class="title">لیلیه فانوس</div>
                <div>رسید {{ $transaction->type === 'income' ? 'درآمد' : 'مصرف' }}</div>
            </div>
            <div>
                <div>شماره سند: {{ $transaction->transaction_number }}</div>
                <div>شماره رسید: {{ $transaction->receipt_number }}</div>
                <div>تاریخ: {{ $transaction->transaction_date?->format('Y-m-d') }}</div>
            </div>
        </div>

        <table>
            <tr><th>دسته</th><td>{{ $transaction->category?->name ?: 'بدون دسته' }}</td></tr>
            <tr><th>شخص / پروژه</th><td>{{ $transaction->displayPerson() }}</td></tr>
            <tr><th>مبلغ</th><td>{{ number_format((int) $transaction->amount) }} افغانی</td></tr>
            <tr><th>باقی‌مانده</th><td>{{ number_format($transaction->remainingAmount()) }} افغانی</td></tr>
            <tr><th>روش پرداخت</th><td>{{ $paymentMethods[$transaction->payment_method] ?? $transaction->payment_method }}</td></tr>
            <tr><th>وضعیت</th><td>{{ $statusLabels[$transaction->status] ?? $transaction->status }}</td></tr>
            <tr><th>توضیحات</th><td>{{ $transaction->description ?: $transaction->notes ?: 'بدون توضیح' }}</td></tr>
            <tr><th>ثبت‌کننده</th><td>{{ $transaction->recordedBy?->name ?: 'نامعلوم' }}</td></tr>
            <tr><th>سند ضمیمه</th><td>{{ $transaction->attachments->isNotEmpty() ? 'دارد' : 'ندارد' }}</td></tr>
        </table>

        <div class="signatures">
            <div class="signature">امضای پرداخت‌کننده / دریافت‌کننده</div>
            <div class="signature">امضای ثبت‌کننده</div>
            <div class="signature">تایید مدیریت</div>
        </div>
    </div>
</body>
</html>
