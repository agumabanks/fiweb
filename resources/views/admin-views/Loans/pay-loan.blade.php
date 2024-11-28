@extends('layouts.admin.app')
@section('title', $client->name . ' Pay')

@section('content')
<div class="container mt-2 pt-2">
    <h1 class="mt-2 pt-2">Loan Payment for {{ $client->name }}</h1>

    <!-- Client and Loan Details -->
    <div class="">
        <div class="row h-100">
            <!-- Client Details Card -->
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">Client Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Name:</strong> {{ $client->name }}</p>
                        <p class="mb-2"><strong>Email:</strong> {{ $client->email }}</p>
                        <p class="mb-2"><strong>Phone:</strong> {{ $client->phone }}</p>
                        <p class="mb-0"><strong>Credit Balance:</strong> <span class="text-success">UGX {{ number_format($client->credit_balance, 0) }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Loan Details Card -->
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">Loan Details</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2"><strong>Daily Amount:</strong> UGX {{ number_format($loan->per_installment, 0) }}</p>
                        <p class="mb-2"><strong>Paid Amount:</strong> UGX {{ number_format($loan->paid_amount, 0) }}</p>
                        <p class="mb-2"><strong>Final Amount:</strong> UGX {{ number_format($loan->final_amount, 0) }}</p>
                        <p class="mb-0"><strong>Loan Status:</strong> 
                            <span class="{{ $loan->status == 2 ? 'text-success' : 'text-warning' }}">
                                {{ $loan->status == 2 ? 'Fully Paid' : 'Ongoing' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Make a Payment Card -->
            <div class="col-md-4 mb-3 d-flex">
                <div class="card shadow-sm flex-fill">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Make a Payment</h5>
                    </div>
                    <div class="card-body">
                        <form id="paymentForm" action="{{ route('admin.loans.updatePayment', $loan->id) }}" method="POST">
                            @csrf

                            <div class="form-group">
                                <label for="payment_amount">Payment Amount</label>
                                <input type="number" name="payment_amount" id="payment_amount" class="form-control" required min="1">
                            </div>

                            <div class="form-group">
                                <label for="note">Note (Optional)</label>
                                <textarea name="note" id="note" class="form-control"></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Payment Installments -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">Client Payment Installments</div>
        <div class="card-body">
            @if($loanInstallments->isEmpty())
                <p>No installments available.</p>
            @else
                <form id="installmentForm">
                    <div class="row">
                        @foreach($loanInstallments as $installment)
                            <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-3">
                                <div class="card border-0 shadow-sm 
                                    {{ $installment->status == 'paid' ? 'bg-primary text-white' : '' }}
                                    {{ $installment->status == 'withbalance' ? 'bg-warning text-dark' : '' }}">
                                    <div class="card-body d-flex align-items-center m-2">
                                        <input class="form-check-input me-2 installment-checkbox" 
                                               type="checkbox" 
                                               value="{{ $installment->date }}" 
                                               id="installment-{{ $installment->id }}" 
                                               name="payment_dates[]"
                                               {{ $installment->status == 'paid' || $installment->status == 'withbalance' ? 'disabled' : '' }}>
                                        <label class="form-check-label w-100" for="installment-{{ $installment->id }}">
                                            <strong>Installment #{{ $loop->iteration }} </strong><br>
                                            <strong>Date {{ $installment->date }}</strong><br>

                                            <span class="d-block small {{ $installment->status == 'paid' ? 'text-white' : 'text-muted' }}">
                                                UGX {{ number_format($installment->install_amount, 0) }}
                                            </span>
                                            @if($installment->status == 'withbalance')
                                                <span class="d-block small text-danger">
                                                    Balance: UGX {{ number_format($installment->installment_balance, 0) }}
                                                </span>
                                            @endif
                                            <span class="d-block 
                                                {{ $installment->status == 'paid' ? 'text-white' : 
                                                   ($installment->status == 'pending' ? 'text-warning' : 
                                                   ($installment->status == 'withbalance' ? 'text-dark' : 'text-danger')) }}">
                                                {{ ucfirst($installment->status) }}
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@push('script_2')
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const paymentForm = document.getElementById('paymentForm');
        const installmentForm = document.getElementById('installmentForm');
        const paymentDates = document.getElementsByName('payment_dates[]');

        paymentForm.addEventListener('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            // Gather selected dates
            let selectedDates = [];
            paymentDates.forEach(function(checkbox) {
                if (checkbox.checked) {
                    selectedDates.push(checkbox.value);
                }
            });

            // Show confirmation dialog
            Swal.fire({
                title: 'Confirm Payment',
                text: 'Are you sure you want to submit this payment?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, submit it!',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Disable the submit button to prevent multiple clicks
                    const submitButton = paymentForm.querySelector('button[type="submit"]');
                    submitButton.disabled = true;

                    // Add the selected dates to the form data
                    let formData = new FormData(paymentForm);
                    if (selectedDates.length > 0) {
                        formData.append('payment_dates', selectedDates.join(','));
                    }

                    // Submit the form via AJAX
                    fetch(paymentForm.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Check for errors
                        if (data.errors) {
                            // Handle validation errors
                            let errorMessages = '';
                            for (const [key, value] of Object.entries(data.errors)) {
                                errorMessages += `${value}\n`;
                            }
                            Swal.fire({
                                title: 'Error',
                                text: errorMessages,
                                icon: 'error',
                            });
                            submitButton.disabled = false; // Re-enable the submit button
                        } else {
                            // Show success message
                            Swal.fire({
                                title: 'Payment Successful',
                                text: 'The payment has been processed successfully.',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload the page to update the loan status
                                location.reload();
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'An error occurred while processing the payment.',
                            icon: 'error',
                        });
                        submitButton.disabled = false; // Re-enable the submit button
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
