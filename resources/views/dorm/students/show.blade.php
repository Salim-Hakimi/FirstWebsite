@extends('admin.layout')

@section('title', 'Student Profile - Fanous Admin')

@section('content')
    @php
        $latestCard = $student->membershipCards->first();
        $statusNames = $statusLabels;
        $collectionNames = [
            'monthly_fee' => 'Monthly fee',
            'electricity' => 'Electricity',
            'fine' => 'Fine',
            'water' => 'Water',
            'expense' => 'Representative expense',
        ];
        $foodNames = [
            'contribution' => 'Food contribution',
            'weekly_food' => 'Weekly food',
            'monthly_fee' => 'Monthly fee',
            'electricity' => 'Electricity',
            'water' => 'Water',
            'expense' => 'Expense / purchase',
        ];
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">{{ $student->full_name }}</h3>
                            <p class="mb-0 text-white-50">Unified dorm student profile: identity, room, documents, card, representative account, and purchaser account.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            @if ($canEditStudent)
                                <a href="{{ route('dorm.students.edit', $student) }}" class="btn btn-outline-light btn-rounded mr-2">Edit profile</a>
                            @endif
                            <a href="{{ route('dorm.students.index') }}" class="btn btn-outline-light btn-rounded">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0 room-status-heading">{{ $statusNames[$student->status] ?? $student->status }}</h3><h6 class="text-muted font-weight-normal mt-3">Dorm status</h6><p class="text-muted mb-0">Joined: {{ $student->joined_at?->format('Y-m-d') ?: 'Not recorded' }}</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ $student->status === 'active' ? ($student->room?->room_number ?: ($student->room_number ?: 'N/A')) : 'Pending' }}</h3><h6 class="text-muted font-weight-normal mt-3">Room</h6><p class="text-muted mb-0">Bed: {{ $student->bed_number ?: 'Not recorded' }}</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ $student->eligibility_score ?? 'N/A' }}</h3><h6 class="text-muted font-weight-normal mt-3">Eligibility score</h6><p class="text-muted mb-0">Education: {{ $student->education_score ?? 'N/A' }}</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h3 class="mb-0">{{ $student->application_date?->format('Y-m-d') ?? 'N/A' }}</h3><h6 class="text-muted font-weight-normal mt-3">Application date</h6><p class="text-muted mb-0">Admitted: {{ $student->admitted_at?->format('Y-m-d') ?? 'Not yet' }}</p></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body student-profile-card">
                    @if ($student->profile_photo_path)
                        <img class="student-profile-photo" src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name }}">
                    @else
                        <div class="student-profile-photo student-profile-photo-empty">{{ strtoupper(substr($student->full_name ?: 'S', 0, 1)) }}</div>
                    @endif
                    <h4 class="mb-1">{{ $student->full_name }}</h4>
                    <p class="text-muted mb-3">{{ $student->education_place }}</p>
                    <div class="student-profile-actions">
                        @if ($canEditStudent)
                            <a class="btn btn-primary btn-sm" href="{{ route('dorm.students.edit', $student) }}">Edit profile</a>
                        @endif
                        @if ($latestCard)
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}">Print card</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Personal information</h4>
                    <div class="student-detail-grid">
                        <div><span>Father</span><strong>{{ $student->father_name }}</strong></div>
                        <div><span>Phone</span><strong>{{ $student->phone }}</strong></div>
                        <div><span>WhatsApp</span><strong>{{ $student->whatsapp ?: 'Not recorded' }}</strong></div>
                        <div><span>Email</span><strong>{{ $student->email ?: 'Not recorded' }}</strong></div>
                        <div><span>ID / Tazkira</span><strong>{{ $student->tazkira_number }}</strong></div>
                        <div><span>Province</span><strong>{{ $student->province ?: 'Not recorded' }}</strong></div>
                        <div><span>Education</span><strong>{{ $student->education_place }}</strong></div>
                        <div><span>Department / grade</span><strong>{{ $student->department_or_grade ?: 'Not recorded' }}</strong></div>
                        <div><span>Guarantor</span><strong>{{ $student->guarantor_name ?: 'Not recorded' }}</strong></div>
                        <div><span>Guarantor phone</span><strong>{{ $student->guarantor_phone ?: 'Not recorded' }}</strong></div>
                        <div><span>Application date</span><strong>{{ $student->application_date?->format('Y-m-d') ?: 'Not recorded' }}</strong></div>
                        <div><span>Education percentage</span><strong>{{ $student->education_score ?? 'Not recorded' }}</strong></div>
                        <div><span>Eligibility score</span><strong>{{ $student->eligibility_score ?? 'Not recorded' }}</strong></div>
                        <div><span>Decision by</span><strong>{{ $student->admissionDecisionBy?->name ?: 'Not decided' }}</strong></div>
                    </div>
                    @if ($student->eligibility_notes)
                        <div class="user-access-note mt-3">
                            <div class="preview-thumbnail"><div class="preview-icon bg-warning"><span>A</span></div></div>
                            <p class="text-muted mb-0">{{ $student->eligibility_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="card-title">Card and documents</h4>
                            <p class="card-description">Dorm card, uploaded files, and notes</p>
                        </div>
                        @if ($canEditStudent && $student->status === 'active')
                            <form method="POST" action="{{ route('dorm.students.card.issue', $student) }}">
                                @csrf
                                <button class="btn btn-primary btn-sm" type="submit">Issue card</button>
                            </form>
                        @endif
                    </div>

                    <div class="preview-list">
                        <div class="preview-item border-bottom">
                            <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>C</span></div></div>
                            <div class="preview-item-content">
                                @if ($latestCard)
                                    <p class="preview-subject mb-1">Card {{ $latestCard->card_number }}</p>
                                    <p class="text-muted mb-0">Valid until {{ $latestCard->expires_at?->format('Y-m-d') }} · {{ $latestCard->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</p>
                                @else
                                    <p class="preview-subject mb-1">No dorm card</p>
                                    <p class="text-muted mb-0">Issue a card from this panel.</p>
                                @endif
                            </div>
                            @if ($latestCard)
                                <a class="btn btn-outline-primary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}">Print</a>
                            @endif
                        </div>

                        <div class="preview-item border-bottom">
                            <div class="preview-thumbnail"><div class="preview-icon bg-dark"><span>N</span></div></div>
                            <div class="preview-item-content">
                                <p class="preview-subject mb-1">Notes</p>
                                <p class="text-muted mb-0">{{ $student->notes ?: 'No notes recorded.' }}</p>
                            </div>
                        </div>

                        @forelse ($student->document_names ?? [] as $document)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>D</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $document['name'] ?? 'Document' }}</p>
                                    <p class="text-muted mb-0">{{ $document['uploaded_at'] ?? 'Unknown upload date' }}</p>
                                </div>
                                @if (! empty($document['path']))
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ asset('storage/'.$document['path']) }}" target="_blank" rel="noopener">Open</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">No documents have been uploaded.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($canRecordPurchaser || $canRecordRepresentative)
        <div class="row">
            @if ($canRecordPurchaser)
                <div class="col-lg-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Record purchaser collection</h4>
                            <form method="POST" action="{{ route('purchaser.records.store') }}">
                                @csrf
                                <input name="dorm_student_id" type="hidden" value="{{ $student->id }}">
                                <div class="form-group"><label>Type</label><select class="form-control" name="type" required>@foreach ($foodNames as $value => $label)@if ($value !== 'expense')<option value="{{ $value }}">{{ $label }}</option>@endif @endforeach</select></div>
                                <div class="form-group"><label>Amount</label><input class="form-control" name="amount" type="number" min="1" value="600" required></div>
                                <div class="form-group"><label>Date</label><input class="form-control" name="recorded_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="form-group"><label>Period</label><input class="form-control" name="period" value="Week {{ now()->weekOfYear }}"></div>
                                <div class="form-group"><label>Note</label><textarea class="form-control" name="description"></textarea></div>
                                <button class="btn btn-primary" type="submit">Save collection</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if ($canRecordRepresentative)
                <div class="col-lg-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Record representative collection</h4>
                            <form method="POST" action="{{ route('representative.collections.store') }}">
                                @csrf
                                <input name="dorm_student_id" type="hidden" value="{{ $student->id }}">
                                <div class="form-group"><label>Type</label><select class="form-control" name="type" required>@foreach ($collectionNames as $value => $label)@if ($value !== 'expense')<option value="{{ $value }}">{{ $label }}</option>@endif @endforeach</select></div>
                                <div class="form-group"><label>Amount</label><input class="form-control" name="amount" type="number" min="1" required></div>
                                <div class="form-group"><label>Date</label><input class="form-control" name="collected_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                                <div class="form-group"><label>Period</label><input class="form-control" name="period" placeholder="Example: Hamal month"></div>
                                <div class="form-group"><label>Note</label><textarea class="form-control" name="notes"></textarea></div>
                                <button class="btn btn-primary" type="submit">Save collection</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Representative account history</h4>
                    <div class="preview-list">
                        @forelse ($student->collections->sortByDesc('collected_at') as $collection)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-primary"><span>R</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $collectionNames[$collection->type] ?? $collection->type }} · {{ number_format($collection->amount) }} AFN</p>
                                    <p class="text-muted mb-0">{{ $collection->collected_at?->format('Y-m-d') }} · {{ $collection->period ?: 'No period' }} · {{ $collection->notes ?: 'No note' }}</p>
                                </div>
                                @if (in_array(auth()->user()->role, \App\Models\User::studentRepresentativeRoles(), true))
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('representative.collections.receipt', $collection) }}">Receipt</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">No representative records yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Purchaser account history</h4>
                    <div class="preview-list">
                        @forelse ($student->foodFinances->sortByDesc('recorded_at') as $record)
                            <div class="preview-item border-bottom">
                                <div class="preview-thumbnail"><div class="preview-icon bg-success"><span>F</span></div></div>
                                <div class="preview-item-content">
                                    <p class="preview-subject mb-1">{{ $foodNames[$record->type] ?? $record->type }} · {{ number_format($record->amount) }} AFN</p>
                                    <p class="text-muted mb-0">{{ $record->recorded_at?->format('Y-m-d') }} · {{ $record->period ?: 'No period' }} · {{ $record->description ?: 'No description' }}</p>
                                </div>
                                @if (in_array(auth()->user()->role, \App\Models\User::purchaserRoles(), true))
                                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('purchaser.records.receipt', $record) }}">Receipt</a>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">No purchaser records yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
