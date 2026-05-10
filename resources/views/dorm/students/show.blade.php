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
        $badgeClasses = [
            'active' => 'badge-outline-success',
            'waiting' => 'badge-outline-warning',
            'on_hold' => 'badge-outline-secondary',
            'rejected' => 'badge-outline-danger',
            'suspended' => 'badge-outline-warning',
            'graduated' => 'badge-outline-primary',
            'left' => 'badge-outline-danger',
        ];
        $statusClass = $badgeClasses[$student->status] ?? 'badge-outline-secondary';
        $roomLabel = $student->status === 'active' ? ($student->room?->room_number ?: ($student->room_number ?: 'N/A')) : 'Pending';
        $registrationTotal = (int) ($student->guarantee_deposit_amount ?? 1000) + (int) ($student->dorm_expense_fee_amount ?? 1000) + (int) ($student->registration_card_fee_amount ?? 50);
        $registrationPaymentStatus = $student->registration_payment_status ?? 'paid';
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="student-profile-hero">
        <div class="student-profile-identity">
            @if ($student->profile_photo_path)
                <img class="student-profile-photo" src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name }}">
            @else
                <div class="student-profile-photo student-profile-photo-empty">{{ strtoupper(substr($student->full_name ?: 'S', 0, 1)) }}</div>
            @endif

            <div>
                <span class="badge {{ $statusClass }}">{{ $statusNames[$student->status] ?? $student->status }}</span>
                <h1>{{ $student->full_name }}</h1>
                <p>{{ $student->education_place }} · {{ $student->department_or_grade ?: 'Department / grade not recorded' }}</p>
                <div class="student-profile-actions">
                    @if ($canEditStudent)
                        <a class="btn btn-primary btn-sm" href="{{ route('dorm.students.edit', $student) }}">Edit profile</a>
                    @endif
                    @if ($latestCard)
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}">Print card</a>
                    @endif
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.registration.receipt', $student) }}">Registration receipt</a>
                    <a href="{{ route('dorm.students.index') }}" class="btn btn-dark btn-sm">Back</a>
                </div>
            </div>
        </div>

        <div class="student-profile-snapshot">
            <span><strong>{{ $roomLabel }}</strong>Room</span>
            <span><strong>{{ $student->bed_number ?: 'N/A' }}</strong>Bed</span>
            <span><strong>{{ $student->eligibility_score ?? 'N/A' }}</strong>Eligibility</span>
            <span><strong>{{ count($student->document_names ?? []) }}</strong>Documents</span>
        </div>
    </section>

    <section class="student-insight-grid">
        <article class="student-insight-card is-primary">
            <span>Dorm status</span>
            <strong>{{ $statusNames[$student->status] ?? $student->status }}</strong>
            <p>Joined: {{ $student->joined_at?->format('Y-m-d') ?: 'Not recorded' }}</p>
        </article>
        <article class="student-insight-card">
            <span>Application</span>
            <strong>{{ $student->application_date?->format('Y-m-d') ?? 'N/A' }}</strong>
            <p>Admitted: {{ $student->admitted_at?->format('Y-m-d') ?? 'Not yet' }}</p>
        </article>
        <article class="student-insight-card">
            <span>Registration payment</span>
            <strong>{{ number_format($registrationTotal) }} AFN</strong>
            <p>{{ ucfirst($registrationPaymentStatus) }}{{ $student->registration_paid_at ? ' on '.$student->registration_paid_at->format('Y-m-d') : '' }}</p>
        </article>
        <article class="student-insight-card">
            <span>Dorm card</span>
            <strong>{{ $latestCard?->card_number ?: 'None' }}</strong>
            <p>{{ $latestCard ? number_format((float) $latestCard->fee_amount, 0).' AFN card fee' : 'No active card recorded' }}</p>
        </article>
    </section>

    <section class="student-profile-layout">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Identity</span>
                    <h2>Personal information</h2>
                    <p>Core profile, contacts, guarantor, and admission decision.</p>
                </div>
            </div>

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
                <div><span>Decision by</span><strong>{{ $student->admissionDecisionBy?->name ?: 'Not decided' }}</strong></div>
            </div>

            <div class="student-detail-grid mt-3">
                <div><span>Guarantee deposit</span><strong>{{ number_format((int) ($student->guarantee_deposit_amount ?? 1000)) }} AFN</strong></div>
                <div><span>Dorm expenses</span><strong>{{ number_format((int) ($student->dorm_expense_fee_amount ?? 1000)) }} AFN</strong></div>
                <div><span>Dorm card fee</span><strong>{{ number_format((int) ($student->registration_card_fee_amount ?? 50)) }} AFN</strong></div>
                <div><span>Registration total</span><strong>{{ number_format($registrationTotal) }} AFN</strong></div>
                <div><span>Payment status</span><strong>{{ ucfirst($registrationPaymentStatus) }}</strong></div>
            </div>
            <div class="student-profile-actions mt-3">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.registration.receipt', $student) }}">Print registration receipt</a>
            </div>

            @if ($student->eligibility_notes)
                <div class="user-access-note mt-3">
                    <div class="preview-thumbnail"><div class="preview-icon bg-warning"><span>N</span></div></div>
                    <p class="text-muted mb-0">{{ $student->eligibility_notes }}</p>
                </div>
            @endif
        </div>

        <aside class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">Files</span>
                    <h2>Card and documents</h2>
                    <p>Dorm card, uploaded files, and profile notes.</p>
                </div>
                @if ($canEditStudent && $student->status === 'active')
                    <form method="POST" action="{{ route('dorm.students.card.issue', $student) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm" type="submit">Issue card</button>
                    </form>
                @endif
            </div>

            <div class="student-timeline-list">
                <div class="student-timeline-item">
                    <span class="student-timeline-icon">C</span>
                    <div>
                        @if ($latestCard)
                            <strong>Card {{ $latestCard->card_number }}</strong>
                            <p>Valid until {{ $latestCard->expires_at?->format('Y-m-d') }} - {{ number_format((float) $latestCard->fee_amount, 0) }} AFN - {{ $latestCard->payment_status === 'paid' ? 'Paid' : 'Unpaid' }}</p>
                        @else
                            <strong>No dorm card</strong>
                            <p>Issue a card from this panel when the student is active.</p>
                        @endif
                    </div>
                    @if ($latestCard)
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}">Print</a>
                    @endif
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">N</span>
                    <div>
                        <strong>Notes</strong>
                        <p>{{ $student->notes ?: 'No notes recorded.' }}</p>
                    </div>
                </div>

                @forelse ($student->document_names ?? [] as $document)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">D</span>
                        <div>
                            <strong>{{ $document['name'] ?? 'Document' }}</strong>
                            <p>{{ $document['uploaded_at'] ?? 'Unknown upload date' }}</p>
                        </div>
                        @if (! empty($document['path']))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ asset('storage/'.$document['path']) }}" target="_blank" rel="noopener">Open</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">No documents have been uploaded.</div>
                @endforelse
            </div>
        </aside>
    </section>

    @if ($canRecordPurchaser || $canRecordRepresentative)
        <section class="student-profile-layout">
            @if ($canRecordPurchaser)
                <div class="student-workspace-panel">
                    <div class="student-panel-head"><div><span class="student-panel-label">Purchaser</span><h2>Record collection</h2></div></div>
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
            @endif

            @if ($canRecordRepresentative)
                <div class="student-workspace-panel">
                    <div class="student-panel-head"><div><span class="student-panel-label">Representative</span><h2>Record collection</h2></div></div>
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
            @endif
        </section>
    @endif

    <section class="student-profile-layout">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div><span class="student-panel-label">Representative</span><h2>Account history</h2></div>
            </div>
            <div class="student-timeline-list">
                @forelse ($student->collections->sortByDesc('collected_at') as $collection)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">R</span>
                        <div>
                            <strong>{{ $collectionNames[$collection->type] ?? $collection->type }} · {{ number_format($collection->amount) }} AFN</strong>
                            <p>{{ $collection->collected_at?->format('Y-m-d') }} · {{ $collection->period ?: 'No period' }} · {{ $collection->notes ?: 'No note' }}</p>
                        </div>
                        @if (in_array(auth()->user()->role, \App\Models\User::studentRepresentativeRoles(), true))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('representative.collections.receipt', $collection) }}">Receipt</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">No representative records yet.</div>
                @endforelse
            </div>
        </div>

        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div><span class="student-panel-label">Purchaser</span><h2>Account history</h2></div>
            </div>
            <div class="student-timeline-list">
                @forelse ($student->foodFinances->sortByDesc('recorded_at') as $record)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">F</span>
                        <div>
                            <strong>{{ $foodNames[$record->type] ?? $record->type }} · {{ number_format($record->amount) }} AFN</strong>
                            <p>{{ $record->recorded_at?->format('Y-m-d') }} · {{ $record->period ?: 'No period' }} · {{ $record->description ?: 'No description' }}</p>
                        </div>
                        @if (in_array(auth()->user()->role, \App\Models\User::purchaserRoles(), true))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('purchaser.records.receipt', $record) }}">Receipt</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">No purchaser records yet.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
