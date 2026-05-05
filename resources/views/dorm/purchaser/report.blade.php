@extends('admin.layout')

@section('title', 'Finance Report - Fanous Admin')

@section('content')
    @php
        $groupNames = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
        ];
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Finance Report</h3>
                            <p class="mb-0 text-white-50">Review collections, expenses, student balances, reporting periods, and recorded-by details.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('purchaser.index') }}" class="btn btn-outline-light btn-rounded">Back to finance</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ number_format($totalCollected) }}</h3><h6 class="text-muted font-weight-normal mt-3">Total collected AFN</h6></div></div>
        </div>
        <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ number_format($totalExpenses) }}</h3><h6 class="text-muted font-weight-normal mt-3">Total expenses AFN</h6></div></div>
        </div>
        <div class="col-xl-4 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ number_format($balance) }}</h3><h6 class="text-muted font-weight-normal mt-3">Balance AFN</h6></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Report filters</h4>
                    <form method="GET" action="{{ route('purchaser.report') }}">
                        <div class="form-group"><label>From date</label><input class="form-control" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"></div>
                        <div class="form-group"><label>To date</label><input class="form-control" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></div>
                        <div class="form-group">
                            <label>Record type</label>
                            <select class="form-control" name="type">
                                <option value="">All types</option>
                                @foreach ($typeLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Group by</label>
                            <select class="form-control" name="group">
                                @foreach ($groupNames as $value => $label)
                                    <option value="{{ $value }}" @selected($group === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Period</label><input class="form-control" name="period" value="{{ $filters['period'] ?? '' }}" placeholder="Example: Week 1 or Hamal month"></div>
                        <button class="btn btn-primary mr-2" type="submit">Show report</button>
                        <a class="btn btn-dark" href="{{ route('purchaser.report') }}">Clear</a>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ $groupNames[$group] ?? 'Daily' }} summary</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Income</th>
                                    <th>Expense</th>
                                    <th>Balance</th>
                                    <th>Records</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($summaryRows as $row)
                                    <tr>
                                        <td>{{ $row['label'] }}</td>
                                        <td>{{ number_format($row['income']) }} AFN</td>
                                        <td>{{ number_format($row['expense']) }} AFN</td>
                                        <td>{{ number_format($row['balance']) }} AFN</td>
                                        <td>{{ $row['count'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No finance records exist in this range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Student totals</h4>
                    <div class="preview-list">
                        @forelse ($studentTotals as $student)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>S</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $student->full_name }}</p>
                                    <p class="text-muted mb-0">Room {{ $student->room?->room_number ?: ($student->room_number ?: 'N/A') }}</p>
                                    <p class="text-muted mb-0">{{ number_format((int) $student->report_food_paid_total) }} AFN collected</p>
                                </div>
                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.show', $student) }}">Profile</a>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No active students found.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Record details</h4>
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
                                    <th>Action</th>
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
                                        <td>
                                            @if ($record->student)
                                                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.show', $record->student) }}">Profile</a>
                                            @else
                                                <span class="text-muted">General</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No finance records exist in this range.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
