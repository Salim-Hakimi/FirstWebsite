@extends('admin.layout')

@section('title', 'Dorm Finance - Fanous Admin')

@section('content')
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Dorm Finance</h3>
                            <p class="mb-0 text-white-50">Track dorm income, student payments, donor support, construction, salaries, library repairs, and daily expenses.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <button class="btn btn-outline-light btn-rounded" type="button" onclick="window.print()">Print report</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($incomeTotal) }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">I</span></div></div></div><h6 class="text-muted font-weight-normal">Income AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($expenseTotal) }}</h3></div><div class="col-3"><div class="icon icon-box-danger"><span class="metric-icon">E</span></div></div></div><h6 class="text-muted font-weight-normal">Expenses AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($balance) }}</h3></div><div class="col-3"><div class="icon icon-box-warning"><span class="metric-icon">B</span></div></div></div><h6 class="text-muted font-weight-normal">Balance AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($studentDebt) }}</h3></div><div class="col-3"><div class="icon icon-box-primary"><span class="metric-icon">D</span></div></div></div><h6 class="text-muted font-weight-normal">Student debt AFN</h6></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">This month</h4>
                    <div class="admin-finance-stack">
                        <span><strong>{{ number_format($monthIncome) }}</strong>Income AFN</span>
                        <span><strong>{{ number_format($monthExpense) }}</strong>Expense AFN</span>
                        <span class="{{ $monthIncome - $monthExpense >= 0 ? 'is-positive' : '' }}"><strong>{{ number_format($monthIncome - $monthExpense) }}</strong>Monthly balance</span>
                    </div>

                    <hr class="border-secondary my-4">

                    <h4 class="card-title">Monthly summary</h4>
                    <div class="preview-list">
                        @forelse ($monthlyRows as $row)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>M</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $row['month'] }}</p>
                                    <p class="text-muted mb-0">Income {{ number_format($row['income']) }} - Expense {{ number_format($row['expense']) }} - Balance {{ number_format($row['balance']) }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No monthly finance activity yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Search and filters</h4>
                    <form method="GET" action="{{ route('admin.finance.index') }}" class="representative-filter-row">
                        <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Receipt, student, donor, project, payee, or note">
                        <select class="form-control" name="type">
                            <option value="">All types</option>
                            <option value="income" @selected(($filters['type'] ?? '') === 'income')>Income</option>
                            <option value="expense" @selected(($filters['type'] ?? '') === 'expense')>Expense</option>
                        </select>
                        <select class="form-control" name="category">
                            <option value="">All categories</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ ucfirst($category->type) }} - {{ $category->name }}</option>
                            @endforeach
                        </select>
                        <select class="form-control" name="payment_status">
                            <option value="">All statuses</option>
                            @foreach ($paymentStatuses as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input class="form-control" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                        <input class="form-control" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                        <input class="form-control representative-period" name="period" value="{{ $filters['period'] ?? '' }}" placeholder="Month, salary period, or project phase">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <a class="btn btn-dark" href="{{ route('admin.finance.index') }}">Clear</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Record income</h4>
                    <p class="card-description">Use this for student fees, registration, donor support, and organization support.</p>

                    <form method="POST" action="{{ route('admin.finance.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input name="type" type="hidden" value="income">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Income category</label>
                                <select class="form-control @error('finance_category_id') is-invalid @enderror" name="finance_category_id" required>
                                    <option value="">Select category</option>
                                    @foreach ($incomeCategories as $category)
                                        <option value="{{ $category->id }}" @selected(old('type') === 'income' && (string) old('finance_category_id') === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('finance_category_id') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Student</label>
                                <select class="form-control" name="dorm_student_id">
                                    <option value="">No student / general income</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" @selected((string) old('dorm_student_id') === (string) $student->id)>{{ $student->full_name }} - Room {{ $student->room?->room_number ?: 'N/A' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 form-group"><label>Amount paid</label><input class="form-control" name="amount" type="number" min="1" value="{{ old('type') === 'income' ? old('amount') : '' }}" required></div>
                            <div class="col-md-4 form-group"><label>Expected amount</label><input class="form-control" name="expected_amount" type="number" min="1" value="{{ old('type') === 'income' ? old('expected_amount') : '' }}" placeholder="Optional"></div>
                            <div class="col-md-4 form-group"><label>Date</label><input class="form-control" name="transaction_date" type="date" value="{{ old('type') === 'income' ? old('transaction_date', now()->format('Y-m-d')) : now()->format('Y-m-d') }}" required></div>
                            <div class="col-md-6 form-group"><label>Payer name</label><input class="form-control" name="payer_name" value="{{ old('type') === 'income' ? old('payer_name') : '' }}" placeholder="Student, parent, donor, or organization"></div>
                            <div class="col-md-6 form-group"><label>Period</label><input class="form-control" name="period" value="{{ old('type') === 'income' ? old('period') : '' }}" placeholder="May 2026, Hamal, registration, etc."></div>
                            <div class="col-md-6 form-group"><label>Donor name</label><input class="form-control" name="donor_name" value="{{ old('type') === 'income' ? old('donor_name') : '' }}" placeholder="Optional"></div>
                            <div class="col-md-6 form-group"><label>Donor phone</label><input class="form-control" name="donor_phone" value="{{ old('type') === 'income' ? old('donor_phone') : '' }}" placeholder="Optional"></div>
                            <div class="col-md-6 form-group">
                                <label>Payment method</label>
                                <select class="form-control" name="payment_method" required>
                                    @foreach ($paymentMethods as $value => $label)
                                        <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Payment status</label>
                                <select class="form-control" name="payment_status" required>
                                    @foreach ($paymentStatuses as $value => $label)
                                        <option value="{{ $value }}" @selected(old('payment_status', 'completed') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 form-group"><label>Attachment</label><input class="form-control" name="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf,.webp"></div>
                            <div class="col-12 form-group"><label>Description</label><textarea class="form-control" name="description" rows="4" placeholder="Receipt note, donor purpose, remaining balance, or other details">{{ old('type') === 'income' ? old('description') : '' }}</textarea></div>
                            <div class="col-12"><button class="btn btn-primary" type="submit">Save income</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Record expense</h4>
                    <p class="card-description">Use this for construction, guard salaries, staff salaries, library repairs, food, utilities, and other expenses.</p>

                    <form method="POST" action="{{ route('admin.finance.store') }}" enctype="multipart/form-data">
                        @csrf
                        <input name="type" type="hidden" value="expense">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Expense category</label>
                                <select class="form-control" name="finance_category_id" required>
                                    <option value="">Select category</option>
                                    @foreach ($expenseCategories as $category)
                                        <option value="{{ $category->id }}" @selected(old('type') === 'expense' && (string) old('finance_category_id') === (string) $category->id)>{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group"><label>Amount</label><input class="form-control" name="amount" type="number" min="1" value="{{ old('type') === 'expense' ? old('amount') : '' }}" required></div>
                            <div class="col-md-6 form-group"><label>Date</label><input class="form-control" name="transaction_date" type="date" value="{{ old('type') === 'expense' ? old('transaction_date', now()->format('Y-m-d')) : now()->format('Y-m-d') }}" required></div>
                            <div class="col-md-6 form-group"><label>Period</label><input class="form-control" name="period" value="{{ old('type') === 'expense' ? old('period') : '' }}" placeholder="Salary month, project phase, etc."></div>
                            <div class="col-md-6 form-group"><label>Paid to</label><input class="form-control" name="payee_name" value="{{ old('type') === 'expense' ? old('payee_name') : '' }}" placeholder="Guard, worker, shop, supplier"></div>
                            <div class="col-md-6 form-group"><label>Project / purpose</label><input class="form-control" name="project_name" value="{{ old('type') === 'expense' ? old('project_name') : '' }}" placeholder="Library repair, room 3, guard salary"></div>
                            <div class="col-md-6 form-group">
                                <label>Payment method</label>
                                <select class="form-control" name="payment_method" required>
                                    @foreach ($paymentMethods as $value => $label)
                                        <option value="{{ $value }}" @selected(old('payment_method', 'cash') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input name="payment_status" type="hidden" value="completed">
                            <div class="col-md-6 form-group"><label>Attachment</label><input class="form-control" name="attachment" type="file" accept=".jpg,.jpeg,.png,.pdf,.webp"></div>
                            <div class="col-12 form-group"><label>Description</label><textarea class="form-control" name="description" rows="4" placeholder="Items purchased, construction work, salary note, repair detail, etc.">{{ old('type') === 'expense' ? old('description') : '' }}</textarea></div>
                            <div class="col-12"><button class="btn btn-danger" type="submit">Save expense</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Finance ledger</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Receipt</th>
                                    <th>Category</th>
                                    <th>Person / Project</th>
                                    <th>Status</th>
                                    <th>Income</th>
                                    <th>Expense</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td>{{ $transaction->transaction_date?->format('Y-m-d') }}</td>
                                        <td>{{ $transaction->transaction_number }}</td>
                                        <td>{{ $transaction->category?->name ?: ucfirst($transaction->type) }}</td>
                                        <td>
                                            {{ $transaction->student?->full_name ?: ($transaction->payer_name ?: ($transaction->payee_name ?: ($transaction->donor_name ?: 'General'))) }}
                                            <div class="text-muted small">{{ $transaction->project_name ?: ($transaction->period ?: 'No period/project') }}</div>
                                        </td>
                                        <td>{{ $paymentStatuses[$transaction->payment_status] ?? $transaction->payment_status }}</td>
                                        <td class="text-success">{{ $transaction->type === 'income' ? number_format($transaction->amount).' AFN' : '-' }}</td>
                                        <td class="text-danger">{{ $transaction->type === 'expense' ? number_format($transaction->amount).' AFN' : '-' }}</td>
                                        <td><a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.finance.receipt', $transaction) }}">Receipt</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No finance transactions found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
