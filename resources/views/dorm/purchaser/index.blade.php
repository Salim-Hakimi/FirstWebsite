@extends('admin.layout')

@section('title', 'Finance - Fanous Admin')

@section('content')
    @php
        $balance = $totalCollected - $totalExpenses;
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Finance</h3>
                            <p class="mb-0 text-white-50">Record food collections, utilities, monthly fees, purchases, and expenses in one financial ledger.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('purchaser.report', $filters) }}" class="btn btn-outline-light btn-rounded">Open report</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($totalCollected) }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">C</span></div></div></div><h6 class="text-muted font-weight-normal">Collected AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($totalExpenses) }}</h3></div><div class="col-3"><div class="icon icon-box-danger"><span class="metric-icon">E</span></div></div></div><h6 class="text-muted font-weight-normal">Expenses AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($balance) }}</h3></div><div class="col-3"><div class="icon icon-box-warning"><span class="metric-icon">B</span></div></div></div><h6 class="text-muted font-weight-normal">Balance AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $students->count() }}</h3></div><div class="col-3"><div class="icon icon-box-primary"><span class="metric-icon">S</span></div></div></div><h6 class="text-muted font-weight-normal">Active students</h6></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Finance types</h4>
                    <div class="preview-list">
                        @foreach ($typeLabels as $value => $label)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon {{ $value === 'expense' ? 'bg-danger' : 'bg-primary' }}"><span>{{ strtoupper(substr($label, 0, 1)) }}</span></div>
                                </div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $label }}</p>
                                    <p class="text-muted mb-0">{{ $value === 'expense' ? 'Outflow record' : 'Student collection' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Search and filters</h4>
                            <p class="text-muted mb-0">Find student payments, expenses, dates, and reporting periods quickly.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('purchaser.index') }}" class="representative-filter-row">
                        <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Student name, father, phone, ID, room, vendor, or note">
                        <select class="form-control" name="type">
                            <option value="">All types</option>
                            @foreach ($typeLabels as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input class="form-control" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                        <input class="form-control" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                        <input class="form-control representative-period" name="period" value="{{ $filters['period'] ?? '' }}" placeholder="Period, e.g. Week 1 or Hamal month">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <a class="btn btn-dark" href="{{ route('purchaser.index') }}">Clear</a>
                        <a class="btn btn-outline-secondary" href="{{ route('purchaser.report', $filters) }}">Report</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Record finance activity</h4>
                    <p class="card-description">Direct recording is available only for the Purchaser account.</p>

                    @if ($canRecord)
                        <form method="POST" action="{{ route('purchaser.records.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Student</label>
                                    <select class="form-control" name="dorm_student_id" required>
                                        <option value="">Select student</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}">{{ $student->full_name }} - Room {{ $student->room?->room_number ?: ($student->room_number ?: 'N/A') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Collection type</label>
                                    <select class="form-control" name="type" required>
                                        @foreach ($incomeTypeLabels as $value => $label)
                                            <option value="{{ $value }}" @selected($value === 'weekly_food')>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group"><label>Amount</label><input class="form-control" name="amount" type="number" min="1" value="600" required></div>
                                <div class="col-md-4 form-group"><label>Date</label><input class="form-control" name="recorded_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="col-md-4 form-group"><label>Period</label><input class="form-control" name="period" value="Week {{ now()->weekOfYear }}"></div>
                                <div class="col-md-6 form-group"><label>Source / receipt</label><input class="form-control" name="vendor_or_source" placeholder="Receipt number or source"></div>
                                <div class="col-12 form-group"><label>Note</label><textarea class="form-control" name="description" rows="4" placeholder="Full payment, partial payment, remaining balance, or other note"></textarea></div>
                                <div class="col-12">
                                    <button class="btn btn-primary mr-2" type="submit">Save collection</button>
                                    <a class="btn btn-dark" href="{{ route('purchaser.report') }}">View report</a>
                                </div>
                            </div>
                        </form>

                        <hr class="border-secondary my-4">

                        <h4 class="card-title">Record expense or purchase</h4>
                        <form method="POST" action="{{ route('purchaser.records.store') }}">
                            @csrf
                            <input name="type" type="hidden" value="expense">
                            <div class="row">
                                <div class="col-md-4 form-group"><label>Expense amount</label><input class="form-control" name="amount" type="number" min="1" required></div>
                                <div class="col-md-4 form-group"><label>Date</label><input class="form-control" name="recorded_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="col-md-4 form-group"><label>Period</label><input class="form-control" name="period" placeholder="Example: Week 1"></div>
                                <div class="col-md-6 form-group"><label>Vendor / source</label><input class="form-control" name="vendor_or_source" placeholder="Shop, market, or supplier"></div>
                                <div class="col-12 form-group"><label>Purchase details</label><textarea class="form-control" name="description" rows="4" placeholder="Rice, oil, vegetables, bread, repair, transport, etc."></textarea></div>
                                <div class="col-12"><button class="btn btn-danger" type="submit">Save expense</button></div>
                            </div>
                        </form>
                    @else
                        <div class="user-access-note">
                            <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>i</span></div></div>
                            <p class="text-muted mb-0">You are viewing this module as management. Direct recording is reserved for the Purchaser account.</p>
                        </div>
                        <a class="btn btn-primary mt-4" href="{{ route('purchaser.report') }}">Open finance report</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Student payment totals</h4>
                    <div class="preview-list">
                        @forelse ($students as $student)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>S</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $student->full_name }}</p>
                                    <p class="text-muted mb-0">Room {{ $student->room?->room_number ?: ($student->room_number ?: 'N/A') }}</p>
                                    <p class="text-muted mb-0">{{ number_format((int) $student->food_paid_total) }} AFN collected</p>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.show', $student) }}">Profile</a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No students match the current search.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Latest finance records</h4>
                    <div
                        data-vue-app="purchaser-records-table"
                        data-title="Quick finance records"
                        data-endpoint="{{ route('api.purchaser.records') }}"
                    ></div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Student / Source</th>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Amount</th>
                                    <th>Recorded by</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $record)
                                    <tr>
                                        <td>{{ $record->recorded_at?->format('Y-m-d') }}</td>
                                        <td>
                                            {{ $record->student?->full_name ?: 'General expense' }}
                                            <div class="text-muted small">{{ $record->vendor_or_source ?: 'No vendor/source' }} - {{ $record->description ?: 'No note' }}</div>
                                        </td>
                                        <td>{{ $typeLabels[$record->type] ?? $record->type }}</td>
                                        <td>{{ $record->period ?: 'No period' }}</td>
                                        <td>{{ number_format($record->amount) }} AFN</td>
                                        <td>{{ $record->recordedBy?->name ?: 'Unknown' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No finance records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
