@extends('admin.layout')

@section('title', 'ویرایش ثبت مالی - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $typeLabels = ['income' => 'درآمد', 'expense' => 'مصرف'];
        $selectedType = old('type', $transaction->type);
        $selectedCategory = old('finance_category_id', $transaction->finance_category_id);
    @endphp

    <div class="fanous-finance-page" dir="rtl">
        <section class="fanous-page-header fanous-finance-hero">
            <div>
                <span class="dashboard-section-kicker">ویرایش ثبت مالی</span>
                <h1>ویرایش {{ $transaction->receipt_number ?: $transaction->transaction_number }}</h1>
                <p>معلومات ثبت مالی را اصلاح کنید؛ تغییرات در سابقه مالی ذخیره می‌شود.</p>
            </div>

            <div class="fanous-page-actions">
                <x-ds.button variant="outline" :href="route('admin.finance.transactions.receipt', $transaction)">رسید</x-ds.button>
                <x-ds.button variant="outline" :href="route('admin.finance.index')">بازگشت</x-ds.button>
            </div>
        </section>

        @if ($errors->any())
            <section class="dashboard-panel">
                <div class="dashboard-empty">
                    لطفاً موارد لازم را بررسی کنید و دوباره ذخیره نمایید.
                </div>
            </section>
        @endif

        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">جزئیات ثبت</span>
                    <h2>فورم ویرایش</h2>
                    <p>نوع، دسته‌بندی، مبلغ، تاریخ، روش پرداخت و سند ضمیمه قابل تغییر است.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.finance.transactions.update', $transaction) }}" enctype="multipart/form-data" class="fanous-finance-form" data-finance-edit-form>
                @csrf
                @method('PUT')

                <label>
                    <span>نوع ثبت</span>
                    <select class="form-control" name="type" data-finance-type>
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>دسته‌بندی</span>
                    <select class="form-control" name="finance_category_id" required data-finance-category>
                        <option value="">انتخاب دسته‌بندی</option>
                        @foreach ($allCategories as $category)
                            <option value="{{ $category->id }}" data-type="{{ $category->type }}" @selected((string) $selectedCategory === (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>مبلغ</span>
                    <input class="form-control" name="amount" type="number" min="1" value="{{ old('amount', $transaction->amount) }}" required>
                </label>

                <label>
                    <span>تاریخ</span>
                    <input class="form-control" name="transaction_date" type="date" value="{{ old('transaction_date', $transaction->transaction_date?->format('Y-m-d')) }}" required>
                </label>

                <label>
                    <span>روش پرداخت</span>
                    <select class="form-control" name="payment_method">
                        @foreach ($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', $transaction->payment_method) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>وضعیت</span>
                    <select class="form-control" name="status">
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $transaction->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>کمک‌کننده / پرداخت‌کننده</span>
                    <input class="form-control" name="payer_name" value="{{ old('payer_name', $transaction->payer_name) }}" placeholder="نام شخص یا منبع پرداخت">
                </label>

                <label>
                    <span>دریافت‌کننده / عنوان مصرف</span>
                    <input class="form-control" name="payee_name" list="finance-staff-payees" value="{{ old('payee_name', $transaction->payee_name) }}" placeholder="نام کارمند، فروشنده یا عنوان مصرف">
                    <datalist id="finance-staff-payees">
                        @foreach ($staffUsers as $staffUser)
                            <option value="{{ $staffUser->name }}">{{ $staffUser->role }}</option>
                        @endforeach
                    </datalist>
                </label>

                <label>
                    <span>منبع / شخص</span>
                    <input class="form-control" name="source_or_payee" value="{{ old('source_or_payee', $transaction->source_or_payee) }}" placeholder="در صورت نیاز منبع یا شخص را وارد کنید">
                </label>

                <label>
                    <span>خیر</span>
                    <select class="form-control" name="finance_donor_id">
                        <option value="">بدون خیر مشخص</option>
                        @foreach ($donors as $donor)
                            <option value="{{ $donor->id }}" @selected((string) old('finance_donor_id', $transaction->finance_donor_id) === (string) $donor->id)>{{ $donor->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>پروژه / کار</span>
                    <select class="form-control" name="finance_project_id">
                        <option value="">بدون پروژه</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project->id }}" @selected((string) old('finance_project_id', $transaction->finance_project_id) === (string) $project->id)>{{ $project->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>شماره رسید</span>
                    <input class="form-control ltr-text" name="receipt_number" value="{{ old('receipt_number', $transaction->receipt_number) }}">
                </label>

                <label>
                    <span>ضرورت سند</span>
                    <select class="form-control" name="attachment_required">
                        <option value="0" @selected((string) old('attachment_required', $transaction->attachment_required ? '1' : '0') === '0')>ضروری نیست</option>
                        <option value="1" @selected((string) old('attachment_required', $transaction->attachment_required ? '1' : '0') === '1')>سند لازم دارد</option>
                    </select>
                </label>

                <label class="fanous-file-upload">
                    <span>ضمیمه سند جدید</span>
                    <input class="fanous-file-input" name="attachment" type="file">
                    <span class="fanous-file-control">
                        <b>انتخاب سند</b>
                        <small>
                            @if ($transaction->attachments->isNotEmpty())
                                {{ Locale::number($transaction->attachments->count()) }} سند فعلاً ثبت است؛ در صورت نیاز سند جدید اضافه کنید.
                            @else
                                هنوز سندی ضمیمه نشده است.
                            @endif
                        </small>
                    </span>
                </label>

                <label class="fanous-form-wide">
                    <span>توضیحات</span>
                    <textarea class="form-control" name="description" rows="3">{{ old('description', $transaction->description) }}</textarea>
                </label>

                <label class="fanous-form-wide">
                    <span>یادداشت</span>
                    <textarea class="form-control" name="notes" rows="2">{{ old('notes', $transaction->notes) }}</textarea>
                </label>

                <div class="fanous-form-actions">
                    <x-ds.button variant="outline" :href="route('admin.finance.index')">لغو</x-ds.button>
                    <x-ds.button type="submit">ذخیره ویرایش</x-ds.button>
                </div>
            </form>
        </section>
    </div>

    <script>
        document.querySelectorAll('[data-finance-edit-form]').forEach((form) => {
            const typeInput = form.querySelector('[data-finance-type]');
            const categoryInput = form.querySelector('[data-finance-category]');

            const syncCategories = () => {
                const selectedType = typeInput.value;

                categoryInput.querySelectorAll('option[data-type]').forEach((option) => {
                    const isVisible = option.dataset.type === selectedType;
                    option.hidden = ! isVisible;
                    option.disabled = ! isVisible;
                });

                const selectedOption = categoryInput.selectedOptions[0];
                if (selectedOption && selectedOption.dataset.type && selectedOption.dataset.type !== selectedType) {
                    categoryInput.value = '';
                }
            };

            typeInput.addEventListener('change', syncCategories);
            syncCategories();
        });
    </script>
@endsection
