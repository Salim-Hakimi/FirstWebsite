@extends('admin.layout')

@section('title', 'Representative - Fanous Admin')

@section('content')
    @php
        $balance = $totalIncome - $totalExpenses;
        $typeNames = [
            'monthly_fee' => 'Monthly fee',
            'electricity' => 'Electricity',
            'fine' => 'Fine',
            'water' => 'Water',
            'expense' => 'Representative expense',
        ];
        $incomeTypeNames = collect($typeNames)->except('expense')->all();
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Student Representative</h3>
                            <p class="mb-0 text-white-50">Record monthly fees, electricity, water, fines, and representative expenses for dorm students.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('representative.report', $filters) }}" class="btn btn-outline-light btn-rounded">Open report</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($totalIncome) }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">I</span></div></div></div><h6 class="text-muted font-weight-normal">Total income AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($totalExpenses) }}</h3></div><div class="col-3"><div class="icon icon-box-danger"><span class="metric-icon">E</span></div></div></div><h6 class="text-muted font-weight-normal">Total expenses AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ number_format($balance) }}</h3></div><div class="col-3"><div class="icon icon-box-warning"><span class="metric-icon">B</span></div></div></div><h6 class="text-muted font-weight-normal">Balance AFN</h6></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><div class="row"><div class="col-9"><h3 class="mb-0">{{ $students->count() }}</h3></div><div class="col-3"><div class="icon icon-box-success"><span class="metric-icon">S</span></div></div></div><h6 class="text-muted font-weight-normal">Active students</h6></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Collection summary</h4>
                    <div class="preview-list">
                        <div class="preview-item border-bottom"><div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>M</span></div></div><div class="preview-item-content"><p class="preview-subject mb-1">Monthly fee</p><p class="text-muted mb-0">{{ number_format($totalMonthly) }} AFN</p></div></div>
                        <div class="preview-item border-bottom"><div class="preview-thumbnail"><div class="preview-icon bg-info"><span>E</span></div></div><div class="preview-item-content"><p class="preview-subject mb-1">Electricity</p><p class="text-muted mb-0">{{ number_format($totalElectricity) }} AFN</p></div></div>
                        <div class="preview-item border-bottom"><div class="preview-thumbnail"><div class="preview-icon bg-success"><span>W</span></div></div><div class="preview-item-content"><p class="preview-subject mb-1">Water</p><p class="text-muted mb-0">{{ number_format($totalWater) }} AFN</p></div></div>
                        <div class="preview-item border-bottom"><div class="preview-thumbnail"><div class="preview-icon bg-warning"><span>F</span></div></div><div class="preview-item-content"><p class="preview-subject mb-1">Fines</p><p class="text-muted mb-0">{{ number_format($totalFines) }} AFN</p></div></div>
                        <div class="preview-item"><div class="preview-thumbnail"><div class="preview-icon bg-danger"><span>X</span></div></div><div class="preview-item-content"><p class="preview-subject mb-1">Expenses</p><p class="text-muted mb-0">{{ number_format($totalExpenses) }} AFN</p></div></div>
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
                            <p class="text-muted mb-0">Filter representative records by student, type, date, or period.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('representative.index') }}" class="representative-filter-row">
                        <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Student name, father, phone, ID, or room">
                        <select class="form-control" name="type">
                            <option value="">All types</option>
                            @foreach ($typeNames as $value => $label)
                                <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input class="form-control" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                        <input class="form-control" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                        <input class="form-control representative-period" name="period" value="{{ $filters['period'] ?? '' }}" placeholder="Period, e.g. Hamal month">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <a class="btn btn-dark" href="{{ route('representative.index') }}">Clear</a>
                        <a class="btn btn-outline-secondary" href="{{ route('representative.report', $filters) }}">Report</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Record representative account</h4>
                    <p class="card-description">Direct recording is available only for the Student Representative role.</p>

                    @if ($canRecord)
                        <form method="POST" action="{{ route('representative.collections.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>Student</label>
                                    <select class="form-control" name="dorm_student_id" required>
                                        <option value="">Select student</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}">{{ $student->full_name }} · Room {{ $student->room?->room_number ?: ($student->room_number ?: 'N/A') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Collection type</label>
                                    <select class="form-control" name="type" required>
                                        @foreach ($incomeTypeNames as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 form-group"><label>Amount</label><input class="form-control" name="amount" type="number" min="1" required></div>
                                <div class="col-md-4 form-group"><label>Date</label><input class="form-control" name="collected_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="col-md-4 form-group"><label>Period</label><input class="form-control" name="period" placeholder="Hamal 1405"></div>
                                <div class="col-12 form-group"><label>Note</label><textarea class="form-control" name="notes" rows="4"></textarea></div>
                                <div class="col-12"><button class="btn btn-primary" type="submit">Save collection</button></div>
                            </div>
                        </form>

                        <hr class="border-secondary my-4">

                        <h4 class="card-title">Record representative expense</h4>
                        <form method="POST" action="{{ route('representative.collections.store') }}">
                            @csrf
                            <input name="type" type="hidden" value="expense">
                            <div class="row">
                                <div class="col-md-4 form-group"><label>Amount</label><input class="form-control" name="amount" type="number" min="1" required></div>
                                <div class="col-md-4 form-group"><label>Date</label><input class="form-control" name="collected_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="col-md-4 form-group"><label>Period</label><input class="form-control" name="period" placeholder="Hamal month"></div>
                                <div class="col-12 form-group"><label>Expense note</label><textarea class="form-control" name="notes" rows="4" placeholder="Printing, shared supplies, small repairs, etc."></textarea></div>
                                <div class="col-12"><button class="btn btn-danger" type="submit">Save expense</button></div>
                            </div>
                        </form>
                    @else
                        <div class="user-access-note">
                            <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>i</span></div></div>
                            <p class="text-muted mb-0">You are viewing this module as management. Direct recording is reserved for the Student Representative account.</p>
                        </div>
                        <a class="btn btn-primary mt-4" href="{{ route('representative.report') }}">Open representative report</a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Latest representative records</h4>
                    <div class="preview-list">
                        @forelse ($collections as $collection)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon {{ $collection->type === 'expense' ? 'bg-danger' : 'bg-success' }}"><span>{{ $collection->type === 'expense' ? 'X' : 'R' }}</span></div>
                                </div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $collection->student?->full_name ?: 'Representative general expense' }}</p>
                                    <p class="text-muted mb-0">{{ $typeNames[$collection->type] ?? $collection->type }} · {{ number_format($collection->amount) }} AFN</p>
                                    <p class="text-muted mb-0">{{ $collection->collected_at?->format('Y-m-d') }} · {{ $collection->period ?: 'No period' }}</p>
                                </div>
                                @if ($collection->student)
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.show', $collection->student) }}">Profile</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">No representative records found.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
