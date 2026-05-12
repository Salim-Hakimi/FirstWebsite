@extends('admin.layout')

@section('title', 'Finance Receipt - Fanous Admin')

@section('content')
    <div class="row">
        <div class="col-lg-8 grid-margin stretch-card mx-auto">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h3 class="mb-1">Fanous Dormitory</h3>
                            <p class="text-muted mb-0">{{ ucfirst($transaction->type) }} receipt</p>
                        </div>
                        <div class="text-right">
                            <h4 class="mb-1">{{ $transaction->transaction_number }}</h4>
                            <p class="text-muted mb-0">{{ $transaction->transaction_date?->format('Y-m-d') }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Category:</strong> {{ $transaction->category?->name ?: ucfirst($transaction->type) }}</p>
                            <p><strong>Amount:</strong> {{ number_format($transaction->amount) }} AFN</p>
                            <p><strong>Payment method:</strong> {{ $paymentMethods[$transaction->payment_method] ?? $transaction->payment_method }}</p>
                            <p><strong>Status:</strong> {{ $paymentStatuses[$transaction->payment_status] ?? $transaction->payment_status }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Student:</strong> {{ $transaction->student?->full_name ?: 'N/A' }}</p>
                            <p><strong>Payer:</strong> {{ $transaction->payer_name ?: 'N/A' }}</p>
                            <p><strong>Paid to:</strong> {{ $transaction->payee_name ?: 'N/A' }}</p>
                            <p><strong>Project:</strong> {{ $transaction->project_name ?: 'N/A' }}</p>
                        </div>
                    </div>

                    @if ($transaction->expected_amount)
                        <div class="alert alert-secondary mt-3">
                            Expected amount: {{ number_format($transaction->expected_amount) }} AFN.
                            Remaining balance: {{ number_format($transaction->balance) }} AFN.
                        </div>
                    @endif

                    <div class="mt-4">
                        <h5>Description</h5>
                        <p class="text-muted">{{ $transaction->description ?: 'No description recorded.' }}</p>
                    </div>

                    @if ($transaction->attachment_path)
                        <p class="mt-3"><strong>Attachment:</strong> <a href="{{ asset('storage/'.$transaction->attachment_path) }}" target="_blank" rel="noopener">Open document</a></p>
                    @endif

                    <div class="row mt-5">
                        <div class="col-md-6">
                            <p class="border-top pt-3">Recorded by: {{ $transaction->recordedBy?->name ?: 'Unknown' }}</p>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <p class="border-top pt-3">Manager signature</p>
                        </div>
                    </div>

                    <div class="d-print-none mt-4">
                        <button class="btn btn-primary mr-2" type="button" onclick="window.print()">Print receipt</button>
                        <a class="btn btn-dark" href="{{ route('admin.finance.index') }}">Back to finance</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
