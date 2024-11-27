@extends('layouts.admin.app')

@section('title', $client->name . ' Profile')

@section('content')
<div class="container my-5">
    <div class="card border-0 shadow-lg rounded-lg">
        {{-- Client Profile Header --}}
        <div class="card-header bg-primary text-white text-center py-5 position-relative" style="margin-bottom: 120px;">
            {{-- Client Photo --}}
            <img src="{{ asset('storage/' . $client->client_photo) }}" 
                onerror="this.src='https://maslink.sanaa.co/public/assets/admin/img/160x160/img1.jpg';" 
                alt="Client Photo" 
                class="rounded-circle shadow-sm position-absolute" 
                style="
                    width: 200px; 
                    height: 200px; 
                    object-fit: cover; 
                    top: 100%; 
                    left: 50%; 
                    transform: translate(-50%, -50%); 
                    border: 5px solid #fff;
                ">
            {{-- Client Name and Phone --}}
            <div class="mt-5" style="margin-top: 130px;">
                <h1 class="font-weight-bold mb-0 text-white">{{ $client->name }}</h1>
                @if($client->phone)
                <p class="mb-0 text-white">{{ $client->phone }}</p>
                @endif
            </div>
            
            {{-- Client Balance --}}
            <div class="mt-3">
                <h4 class="font-weight-bold mb-0 text-white">
                    <span class="credit-balance">{{ number_format($client->credit_balance, 0) }}/=</span>
                </h4>
                <small class="text-white">Loan Balance</small>
            </div>
        </div>

        <div class="card-body" style="padding-top: 100px;">
            {{-- Action Buttons --}}
            <div class="d-flex justify-content-center mb-4 flex-wrap">
                <a href="{{ route('admin.loans.updateClientLoan', $client->id) }}" class="btn btn-primary m-2">
                    <i class="tio-add mr-1"></i> New Loan
                </a>
                <a href="{{ route('admin.print-statment', $client->id) }}" class="btn btn-secondary m-2">
                    <i class="tio-print mr-1"></i> Print Statement
                </a>
                <a href="{{ route('admin.loans.admin.pay', $client->id) }}" class="btn btn-secondary m-2">
                    <i class="tio-money mr-1"></i> Pay Loan
                </a>
                <!-- Updated Top Up Button -->
                <button type="button" class="btn btn-secondary m-2" data-toggle="modal" data-target="#topUpModal">
                    <i class="tio-money mr-1"></i> Top Up
                </button>
                <!-- Updated Add Guarantor Button -->
                <button type="button" class="btn btn-secondary m-2" data-toggle="modal" data-target="#addGuarantorModal">
                    <i class="tio-user-add mr-1 "></i> Add Guarantor
                </button>
                <a href="{{ route('admin.loans.admin.pay', $client->id) }}" class="btn btn-secondary m-2">
                    <i class="tio-money mr-1"></i> Add Fine
                </a>
                <a href="{{ route('admin.savings.index') }}" class="btn btn-secondary m-2">
                    <i class="tio-money mr-1"></i> Savings
                </a>
            </div>

            {{-- Tabs Navigation --}}
            <ul class="nav nav-tabs" id="clientProfileTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="basic-info-tab" data-toggle="tab" href="#basic-info" role="tab" aria-controls="basic-info" aria-selected="true">Basic Information</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="transaction-history-tab" data-toggle="tab" href="#transaction-history" role="tab" aria-controls="transaction-history" aria-selected="false">Payment History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="loan-history-tab" data-toggle="tab" href="#loan-history" role="tab" aria-controls="loan-history" aria-selected="false">Loan History</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($guarantors && count($guarantors) > 0) ? '' : 'd-none' }}" id="guarantors-tab" data-toggle="tab" href="#guarantors" role="tab" aria-controls="guarantors" aria-selected="false">Guarantors</a>
                </li>
                @if ($agent)
                <li class="nav-item">
                    <a class="nav-link" id="agent-info-tab" data-toggle="tab" href="#agent-info" role="tab" aria-controls="agent-info" aria-selected="false">Agent Information</a>
                </li>
                @endif
            </ul>

            {{-- Tabs Content --}}
            <div class="tab-content mt-4" id="clientProfileTabsContent">
                {{-- Basic Information Tab --}}
                <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-info-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>Phone:</strong>
                                    <span class="ml-2">{{ preg_replace("/(\d{4})(\d{3})(\d{3})/", "$1 $2 $3", $client->phone) }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Address:</strong>
                                    <span class="ml-2">{{ $client->address }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Business:</strong>
                                    <span class="ml-2">{{ $client->business }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <strong>National ID Number:</strong>
                                    <span class="ml-2">{{ $client->nin }}</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Savings Balance:</strong>
                                    <span class="ml-2">{{ number_format($client->savings_balance, 0) }}/=</span>
                                </li>
                                <li class="list-group-item">
                                    <strong>Branch:</strong>
                                    <span class="ml-2">{{ $branch ? $branch->branch_name : 'No Branch Assigned' }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Transaction History Tab --}}
                <!-- Transaction History Tab -->
<div class="tab-pane fade" id="transaction-history" role="tabpanel" aria-labelledby="transaction-history-tab">
    <div id="transactionHistoryContent">
        @include('admin-views.clients.partials.transaction-history', ['clientLoanPayHistroy' => $clientLoanPayHistroy])
    </div>
</div>


                {{-- Loan History Tab --}}
                <div class="tab-pane fade" id="loan-history" role="tabpanel" aria-labelledby="loan-history-tab">
                    @if ($clientLoans && count($clientLoans) > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Loan ID</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Taken Date</th>
                                    <th>Due Date</th>
                                    <th>Payable in</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clientLoans as $loan)
                                <tr>
                                    <td>{{ $loan->id }}</td>
                                    <td>{{ number_format($loan->amount, 0) }} /=</td>
                                    <td>
                                        @switch($loan->status)
                                            @case(0) <span class="badge badge-warning">Pending</span> @break
                                            @case(1) <span class="badge badge-info">Running</span> @break
                                            @case(2) <span class="badge badge-success">Paid</span> @break
                                            @case(3) <span class="badge badge-danger">Rejected</span> @break
                                            @default <span class="badge badge-secondary">Unknown</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $loan->loan_taken_date }}</td>
                                    <td>{{ $loan->due_date }}</td>
                                    <td>{{ $loan->installment_interval }} days</td>
                                    <td>
                                        <a href="{{ route('admin.loans.show', $loan->id) }}" class="btn btn-primary btn-sm" title="View Loan">
                                            <i class="tio-visible"></i> 
                                        </a>
                                        <a href="{{ route('admin.loans.show', $loan->id) }}" class="btn btn-secondary btn-sm" title="Print Statement">
                                            <i class="tio-print"></i> Print
                                        </a>
                                        @if($loan->status == 0)
                                        <form action="{{ route('admin.loan.delete', $loan->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this loan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete Loan">
                                                <i class="fas fa-trash-alt"></i>
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted">No loans found.</p>
                    @endif
                </div>

                {{-- Guarantors Tab --}}
                <div class="tab-pane fade" id="guarantors" role="tabpanel" aria-labelledby="guarantors-tab">
                    <div id="guarantorsList">
                        @if ($guarantors && count($guarantors) > 0)
                            @include('admin-views.clients.partials.guarantors-list', ['guarantors' => $guarantors])
                        @else
                            <p class="text-muted">No guarantors found.</p>
                        @endif
                    </div>
                </div>

                {{-- Agent Information Tab --}}
                @if ($agent)
                <div class="tab-pane fade" id="agent-info" role="tabpanel" aria-labelledby="agent-info-tab">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm p-3">
                                <div class="card-body d-flex align-items-center">
                                    {{-- Agent Photo --}}
                                    <img src="{{ asset('storage/agents/photos/' . $agent->photo) }}" 
                                         onerror="this.src='https://lendsup.sanaa.co/public/assets/admin/img/160x160/img1.jpg';" 
                                         alt="{{ $agent->f_name }} {{ $agent->l_name }}" 
                                         class="rounded-circle shadow-sm" 
                                         style="width: 70px; height: 70px; object-fit: cover;">
                                    
                                    {{-- Agent Details --}}
                                    <div class="ml-4">
                                        <h5 class="fw-bold mb-1">{{ $agent->f_name }} {{ $agent->l_name }}</h5>
                                        <p class="text-muted mb-1">{{ $agent->occupation }}</p>
                                        <p class="text-muted mb-0">
                                            {{ preg_replace("/(\d{4})(\d{3})(\d{3})/", "$1 $2 $3", $agent->phone) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="text-muted mb-3">Account Activity</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                            <strong>Created At:</strong>
                                            <span class="ml-2 text-muted">{{ $client->created_at->format('F d, Y h:i A') }}</span>
                                        </li>
                                        <li class="list-group-item">
                                            <strong>Updated At:</strong>
                                            <span class="ml-2 text-muted">{{ $client->updated_at->format('F d, Y h:i A') }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div> {{-- End of Tab Content --}}
        </div> {{-- End of Card Body --}}
    </div>
</div>

<!-- Add Guarantor Modal -->
<div class="modal fade" id="addGuarantorModal" tabindex="-1" role="dialog" aria-labelledby="addGuarantorModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white" id="addGuarantorModalLabel">Add Guarantor</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Form Starts Here -->
        <form id="addGuarantorForm" enctype="multipart/form-data">
          @csrf

          <div class="row">
            <div class="col-md-6">
              <!-- Name -->
              <div class="form-group">
                <label for="name">Guarantor Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>

              <!-- NIN -->
              <div class="form-group">
                <label for="nin">NIN</label>
                <input type="text" name="nin" class="form-control" required>
              </div>

              <!-- Phone Number -->
              <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" class="form-control" required>
              </div>
            </div>

            <div class="col-md-6">
              <!-- Address -->
              <div class="form-group">
                <label for="address">Address</label>
                <input type="text" name="address" class="form-control">
              </div>

              <!-- Relationship -->
              <div class="form-group">
                <label for="client_relationship">Relationship to Client</label>
                <input type="text" name="client_relationship" class="form-control" required>
              </div>

              <!-- Job -->
              <div class="form-group">
                <label for="job">Job</label>
                <input type="text" name="job" class="form-control">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6">
              <!-- Photo -->
              <div class="form-group">
                <label for="photo">Guarantor Photo</label>
                <input type="file" name="photo" class="form-control" accept="image/*">
              </div>
            </div>
            <div class="col-md-6">
              <!-- National ID Photo -->
              <div class="form-group">
                <label for="national_id_photo">National ID Photo</label>
                <input type="file" name="national_id_photo" class="form-control" accept="image/*">
              </div>
            </div>
          </div>

          <!-- Hidden Field for Added By -->
          <input type="hidden" name="added_by" value="{{ auth()->user()->id }}">

          <!-- Error Display -->
          <div id="guarantorErrorMessages" class="alert alert-danger d-none"></div>

          <div class="d-flex justify-content-end mt-3">
            <button type="reset" class="btn btn-secondary mr-2">Reset</button>
            <button type="submit" class="btn btn-primary">Add Guarantor</button>
          </div>
        </form>
        <!-- Form Ends Here -->
      </div>
    </div>
  </div>
</div>

<!-- Top Up Modal -->
<div class="modal fade" id="topUpModal" tabindex="-1" role="dialog" aria-labelledby="topUpModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title text-white" id="topUpModalLabel">Top Up Payment</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 1;">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Top Up Form Starts Here -->
        <form id="topUpForm">
          @csrf

          <!-- Amount -->
          <div class="form-group">
            <label for="topup-amount">Amount to Pay</label>
            <input type="number" name="amount" id="topup-amount" class="form-control" required min="1" step="0.01" value="{{ number_format($client->credit_balance, 2, '.', '') }}" readonly>
          </div>

          <!-- Note (Optional) -->
          <div class="form-group">
            <label for="topup-note">Note (Optional)</label>
            <textarea name="note" id="topup-note" class="form-control" rows="3"></textarea>
          </div>

          <!-- Error Display -->
          <div id="topUpErrorMessages" class="alert alert-danger d-none"></div>

          <div class="d-flex justify-content-end mt-3">
            <button type="reset" class="btn btn-secondary mr-2">Reset</button>
            <button type="submit" class="btn btn-primary">Make Payment</button>
          </div>
        </form>
        <!-- Top Up Form Ends Here -->
      </div>
    </div>
  </div>
</div>

@endsection

@push('script_2')
<script>
$(document).ready(function() {
    // Handle Add Guarantor Form Submission
    $('#addGuarantorForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('#guarantorErrorMessages').addClass('d-none').html('');

        // Prepare form data
        var formData = new FormData(this);

        // AJAX request
        $.ajax({
            url: '{{ route('admin.clients.addClientGuarantorWeb', $client->id) }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                // Disable submit button to prevent multiple clicks
                $('#addGuarantorForm button[type="submit"]').prop('disabled', true);
            },
            success: function(response) {
                // Close the modal
                $('#addGuarantorModal').modal('hide');
                // Reset the form
                $('#addGuarantorForm')[0].reset();
                // Re-enable the submit button
                $('#addGuarantorForm button[type="submit"]').prop('disabled', false);

                // Show success message (optional)
                alert('Guarantor added successfully.');

                // Update the Guarantors tab
                updateGuarantorsList(response.guarantorsHtml);

                // Activate the Guarantors tab if it's not already active
                $('#guarantors-tab').removeClass('d-none');
                $('#guarantors-tab').tab('show');
            },
            error: function(xhr) {
                // Re-enable the submit button
                $('#addGuarantorForm button[type="submit"]').prop('disabled', false);

                if (xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = '<ul>';
                    $.each(errors, function(key, value) {
                        errorMessages += '<li>' + value[0] + '</li>';
                    });
                    errorMessages += '</ul>';
                    $('#guarantorErrorMessages').removeClass('d-none').html(errorMessages);
                } else {
                    // Other errors
                    alert('An error occurred. Please try again.');
                }
            }
        });
    });

    function updateGuarantorsList(html) {
        $('#guarantorsList').html(html);
    }

    // Handle Top Up Form Submission
    $('#topUpForm').on('submit', function(e) {
        e.preventDefault();

        // Clear previous errors
        $('#topUpErrorMessages').addClass('d-none').html('');

        // Prepare form data
        var formData = $(this).serialize();

        // AJAX request
        $.ajax({
            url: '{{ route('admin.clients.topup', $client->id) }}', // Define this route
            type: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                // Disable submit button to prevent multiple clicks
                $('#topUpForm button[type="submit"]').prop('disabled', true);
            },
            success: function(response) {
                // Close the modal
                $('#topUpModal').modal('hide');
                // Reset the form
                $('#topUpForm')[0].reset();
                // Re-enable the submit button
                $('#topUpForm button[type="submit"]').prop('disabled', false);

                // Show success message (optional)
                alert('Top-up payment successful.');

                // Update the client's credit balance on the page
                updateCreditBalance(response.remaining_balance);
                 // Update the transaction history
                updateTransactionHistory();

                // Activate the Transaction History tab
                $('#transaction-history-tab').tab('show');

                // Optionally, update other sections like transaction history
                // updateTransactionHistory(response.transactionHistoryHtml);

            },
            error: function(xhr) {
                // Re-enable the submit button
                $('#topUpForm button[type="submit"]').prop('disabled', false);

                if (xhr.status === 422) {
                    // Validation errors
                    var errors = xhr.responseJSON.errors;
                    var errorMessages = '<ul>';
                    $.each(errors, function(key, value) {
                        errorMessages += '<li>' + value[0] + '</li>';
                    });
                    errorMessages += '</ul>';
                    $('#topUpErrorMessages').removeClass('d-none').html(errorMessages);
                } else {
                    // Other errors
                    alert('An error occurred. Please try again.');
                }
            }
        });
    });

    function updateTransactionHistory() {
    $.ajax({
        url: '{{ route('admin.clients.transactionHistory', $client->id) }}',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            // Replace the content of the transaction history tab
            $('#transactionHistoryContent').html(response.html);
        },
        error: function(xhr) {
            console.error('Failed to fetch transaction history.');
            // Optionally display an error message to the user
        }
    });
}


    function updateCreditBalance(newBalance) {
        // Update the credit balance displayed on the page
        $('.credit-balance').text(parseFloat(newBalance).toLocaleString() + '/=');
    }

});
</script>
@endpush
