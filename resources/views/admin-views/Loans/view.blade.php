@extends('layouts.admin.app')

@section('title', $client->name)

@section('content')
<div class="container-fluid content">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 mt-3">
        <!-- Client Information and Navigation -->
        <div class="d-flex align-items-center gap-3">
            <img width="24" src="{{ asset('public/assets/admin/img/media/lending.png') }}" alt="{{ translate('transaction') }}">
            <a href="{{ route('admin.clients.profile', $client->id) }}" class="text-decoration-none text-dark">
                <h1 class="page-header-title m-0">{{ $client->name }}</h1>
            </a>
        </div>

        <!-- Action Buttons (Conditional on Loan Status) -->
        <div class="d-flex justify-content-center gap-2">
            @if($loan->status == 0)
                <a href="{{ route('admin.loans.loanedit', $loan->id) }}" class="btn btn-warning mb-3">
                    <i class="tio-edit mr-1"></i> {{ translate('Edit Loan') }}
                </a>
                <button type="button" class="btn btn-success mb-3" id="approveLoanBtn">
                    <i class="tio-checkmark-circle mr-1"></i> {{ translate('Approve') }}
                </button>
            @else
                <a href="{{ route('admin.loans.admin.pay', $client->id) }}" class="btn btn-primary mb-3">
                    <i class="tio-money mr-1"></i> {{ translate('Pay Loan') }}
                </a>
            @endif
        </div>
    </div>

    <!-- Loan and Client Information -->
    <div class="row g-3 mt-4">
        <div class="col-md-12">
            @if($loan)
                <div class="card shadow-sm">
                    <div class="card-body">
                        <!-- Loan Status and Client Details -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="card-title mb-0">{{ translate('Loan Details') }}</h3>
                            <span class="badge 
                                @switch($loan->status)
                                    @case(0) badge-warning @break
                                    @case(1) badge-primary @break
                                    @case(2) badge-success @break
                                    @case(3) badge-danger @break
                                @endswitch
                                p-2">
                                {{ translate($loan->status == 0 ? 'Pending' : ($loan->status == 1 ? 'Running' : ($loan->status == 2 ? 'Paid' : 'Rejected'))) }}
                            </span>
                        </div>

                        <div class="row">
                            <!-- Client Details -->
                            <div class="col-md-6">
                                <div class="text-center mb-4">
                                    @if ($client->client_photo)
                                        <img src="{{ asset('storage/' . $client->client_photo) }}" class="img-thumbnail rounded-circle mb-2" alt="Client Photo" style="width: 120px; height: 120px; object-fit: cover;">
                                    @else
                                        <img src="https://maslink.sanaa.co/public/assets/admin/img/160x160/img1.jpg" class="img-thumbnail rounded-circle mb-2" alt="Default Profile Image" style="width: 120px; height: 120px; object-fit: cover;">
                                    @endif
                                </div>

                                <h4>{{ translate('Client Details') }}</h4>
                                <dl class="row">
                                    <dt class="col-sm-5">{{ translate('NIN') }}:</dt>
                                    <dd class="col-sm-7">{{ $client->nin }}</dd>

                                    <dt class="col-sm-5">{{ translate('Phone') }}:</dt>
                                    <dd class="col-sm-7">{{ $client->phone }}</dd>

                                    <dt class="col-sm-5">{{ translate('Address') }}:</dt>
                                    <dd class="col-sm-7">{{ $client->address }}</dd>

                                    <dt class="col-sm-5">{{ translate('Credit Balance') }}:</dt>
                                    <dd class="col-sm-7">{{ number_format($client->credit_balance, 0) }} /=</dd>

                                    <dt class="col-sm-5">{{ translate('Savings Balance') }}:</dt>
                                    <dd class="col-sm-7">{{ number_format($client->savings_balance, 0) }} /=</dd>
                                </dl>
                            </div>

                            <!-- Loan Details -->
                            <div class="col-md-6">
                                <h4>{{ translate('Loan Details') }}</h4>
                                <dl class="row">
                                    @if($loanPlan)
                                        <dt class="col-sm-5">{{ translate('Loan Plan') }}:</dt>
                                        <dd class="col-sm-7">{{ $loanPlan->plan_name }}</dd>
                                    @endif

                                    <dt class="col-sm-5">{{ translate('Field Agent') }}:</dt>
                                    <dd class="col-sm-7">{{ $agent->f_name }} {{ $agent->l_name }}</dd>

                                    <dt class="col-sm-5">{{ translate('Loan Amount') }}:</dt>
                                    <dd class="col-sm-7"><h5>{{ number_format($loan->amount, 0) }} /=</h5></dd>

                                    <dt class="col-sm-5">{{ translate('Installment Amount') }}:</dt>
                                    <dd class="col-sm-7">{{ number_format($loan->per_installment, 0) }} /=</dd>

                                    <dt class="col-sm-5">{{ translate('Installment Interval (Days)') }}:</dt>
                                    <dd class="col-sm-7">{{ $loan->installment_interval }}</dd>

                                    <dt class="col-sm-5">{{ translate('Date Taken') }}:</dt>
                                    <dd class="col-sm-7">{{ \Carbon\Carbon::parse($loan->loan_taken_date)->format('Y-m-d') }}</dd>

                                    <dt class="col-sm-5">{{ translate('Due Date') }}:</dt>
                                    <dd class="col-sm-7">{{ \Carbon\Carbon::parse($loan->due_date)->format('Y-m-d') }}</dd>

                                    <dt class="col-sm-5">{{ translate('Paid Amount') }}:</dt>
                                    <dd class="col-sm-7">{{ number_format($loan->paid_amount, 0) }} /=</dd>

                                    <dt class="col-sm-5">{{ translate('Final Amount (with Interest)') }}:</dt>
                                    <dd class="col-sm-7"><h5>{{ number_format($loan->final_amount, 0) }} /=</h5></dd>

                                    <dt class="col-sm-5">{{ translate('Processing Fee') }}:</dt>
                                    <dd class="col-sm-7">{{ number_format($loan->processing_fee, 0) }} /=</dd>
                                </dl>
                            </div>
                        </div>

                        <!-- Loan Guarantors and Plan Details -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card @if ($clientGuarantors->isEmpty()) border-danger @else border-secondary @endif mt-4">
                                    <div class="card-header @if ($clientGuarantors->isEmpty()) bg-danger text-white @else bg-secondary text-white @endif">
                                        <h4 class="mb-0">{{ translate('Guarantor Details') }}</h4>
                                    </div>
                                    <div class="card-body">
                                        @if ($clientGuarantors->isNotEmpty())
                                            @foreach ($clientGuarantors as $guarantor)
                                                <div class="mb-3">
                                                    <h5>{{ $guarantor->name }}</h5>
                                                    <p class="mb-0"><strong>{{ translate('NIN') }}:</strong> {{ $guarantor->nin }}</p>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-danger mb-0">{{ translate('No guarantors found for this loan.') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                @if($loanPlan)
                                    <div class="card mt-4">
                                        <div class="card-header bg-secondary text-white">
                                            <h4 class="mb-0">{{ translate('Loan Plan Details') }}</h4>
                                        </div>
                                        <div class="card-body">
                                            <dl class="row">
                                                <dt class="col-sm-5">{{ translate('Loan Plan') }}:</dt>
                                                <dd class="col-sm-7">{{ $loanPlan->plan_name }}</dd>

                                                <dt class="col-sm-5">{{ translate('Min Amount') }}:</dt>
                                                <dd class="col-sm-7">{{ number_format($loanPlan->min_amount, 0) }} /=</dd>
                                            </dl>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Client Pay Slots -->
                        @if($loanSlots)
                            <div class="mt-5">
                                <h4 class="text-primary">{{ translate('Client Pay Slots') }}</h4>
                                <div class="row mt-4">
                                    @foreach($loanSlots as $slot)
                                        @php
                                            $statusColor = $slot->status == 'paid' ? 'border-success' : ($slot->status == 'pending' && now()->greaterThan(\Carbon\Carbon::parse($slot->date)) ? 'border-danger' : 'border-secondary');
                                            $statusBadge = $slot->status == 'paid' ? 'badge-success' : ($slot->status == 'pending' && now()->greaterThan(\Carbon\Carbon::parse($slot->date)) ? 'badge-danger' : 'badge-secondary');
                                        @endphp
                                        <div class="col-md-3 mb-4">
                                            <div class="card {{ $statusColor }} shadow-sm h-100">
                                                <div class="card-body d-flex flex-column justify-content-between">
                                                    <div>
                                                        <h5 class="card-title">{{ translate('Installment') }} {{ $loop->iteration }}</h5>
                                                        <p class="mb-2">
                                                            <strong>{{ translate('Amount') }}:</strong> {{ number_format($slot->install_amount, 0) }} /=
                                                        </p>
                                                        <p class="mb-2">
                                                            <strong>{{ translate('Date') }}:</strong> {{ \Carbon\Carbon::parse($slot->date)->format('Y-m-d') }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <span class="badge {{ $statusBadge }}">{{ ucfirst($slot->status) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <p>{{ translate('No loan details found.') }}</p>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script_2')
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
$(document).ready(function() {
    // Approve Loan Button Click Event
    $('#approveLoanBtn').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: '{{ translate("Are you sure you want to approve this loan?") }}',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: '{{ translate("Yes, approve it!") }}',
            cancelButtonText: '{{ translate("Cancel") }}',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Submit the form via AJAX
                $.ajax({
                    url: '{{ route("admin.loans.approve", $loan->id) }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        Swal.fire({
                            title: '{{ translate("Approved!") }}',
                            text: '{{ translate("The loan has been approved.") }}',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload the page to update the loan status
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: '{{ translate("Error") }}',
                            text: '{{ translate("An error occurred while approving the loan.") }}',
                            icon: 'error',
                        });
                        console.error(xhr);
                    }
                });
            }
        });
    });
});
</script>
@endpush
