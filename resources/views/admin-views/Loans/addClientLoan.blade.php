@extends('layouts.admin.app')

@section('title', translate('Add Client Loan'))

@section('content')
<div class="container-fluid content">
    <div class="row g-3 mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pb-2 mb-2">
                    <h2 class="fw-bold">{{ translate('New Loan Application') }}</h2>
                    <p class="text-muted">{{ translate('Provide the necessary details to add a new loan for the client.') }}</p>
                </div>
                <div class="card-body">
                    {{-- Loan Form --}}
                    {{-- <h5 class="card-title text-muted mb-2">{{ translate('Add Loan for Client') }}</h5> --}}
                   
                    <form action="{{ route('admin.loans.storeClientLoan') }}" method="POST">
                        @csrf
                        <div class="row">
                            {{-- Client Name --}}
                            <div class="col-md-6 mb-3">
                                <label for="client_name" class="form-label">{{ translate('Client Name') }}</label>
                                <input type="text" class="form-control" id="client_name" value="{{ $client->name }}" disabled>
                            </div>

                            {{-- Client ID (Hidden Field) --}}
                            <input type="hidden" name="client_id" value="{{ $client->id }}">

                            {{-- Agent Selection --}}
                            <div class="col-md-6 mb-3">
                                <label for="agent_id" class="form-label">{{ translate('Loan Owner (Agent)') }}</label>
                                <select class="form-control" id="agent_id" name="agent_id" required>
                                    <option value="" disabled selected>{{ translate('Select Agent') }}</option>
                                    @foreach ($agents as $agent)
                                        <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->f_name }} {{ $agent->l_name }} ({{ translate('Clients Managed') }}: {{ $agent->client_count }}, {{ translate('Total Money Out') }}: {{ number_format($agent->total_money_out, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Plan Selection --}}
                            <div class="col-md-6 mb-3">
                                <label for="plan_id" class="form-label">{{ translate('Select Loan Plan') }}</label>
                                <select class="form-control" id="plan_id" name="plan_id" required>
                                    <option value="" disabled selected>{{ translate('Select Loan Plan') }}</option>
                                    @foreach ($loanPlans as $plan)
                                        <option value="{{ $plan->id }}" {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->plan_name }} ({{ translate('Min') }}: {{ number_format($plan->min_amount, 0) }}, {{ translate('Max') }}: {{ number_format($plan->max_amount, 0) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Loan Amount --}}
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">{{ translate('Loan Amount') }}</label>
                                <input type="number" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                                @error('amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Installment Interval --}}
                            <div class="col-md-6 mb-3">
                                <label for="installment_interval" class="form-label">{{ translate('Installment Interval (Days)') }}</label>
                                <input type="number" class="form-control" id="installment_interval" name="installment_interval" value="{{ old('installment_interval') }}" required>
                                @error('installment_interval')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Processing Fee Amount --}}
                            <div class="col-md-6 mb-3">
                                <label for="paid_amount" class="form-label">{{ translate('Processing Fee') }}</label>
                                <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount') }}">
                                @error('paid_amount')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Loan Taken Date --}}
                            <div class="col-md-6 mb-3">
                                <label for="taken_date" class="form-label">{{ translate('Loan Taken Date') }}</label>
                                <input type="date" class="form-control" id="taken_date" name="taken_date" value="{{ old('taken_date') }}">
                                @error('taken_date')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Guarantor Selection --}}
                            <div class="col-md-12 mb-3">
                                <label for="guarantors" class="form-label">{{ translate('Select Guarantors') }}</label>
                                <div class="d-flex align-items-center">
                                    <select class="form-control select2" id="guarantors" name="guarantors[]" multiple required style="width: 90%;">
                                        @foreach ($client->guarantors as $guarantor)
                                            <option value="{{ $guarantor->id }}" {{ in_array($guarantor->id, old('guarantors', [])) ? 'selected' : '' }}>
                                                {{ $guarantor->name }} - NIN: {{ $guarantor->nin }} ({{ $guarantor->phone_number }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn  btn-outline-primary ml-2 p-2" data-toggle="modal" data-target="#addGuarantorModal">
                                        <div class=" d-flex align-items-center">
                                            <i class="tio-user-add mr-1 "></i>{{ translate('Guarantor') }}
                                        </div>
                                        
                                    </button>
                                    
                                </div>
                                @error('guarantors')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Save Button --}}
                        {{-- <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-lg">{{ translate('Add Loan') }}</button>
                        </div> --}}
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg">{{ translate('Submit Loan Application') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Guarantor Modal -->
    <div class="modal fade" id="addGuarantorModal" tabindex="-1" role="dialog" aria-labelledby="addGuarantorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="addGuarantorModalLabel">{{ translate('Add Guarantor') }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="{{ translate('Close') }}" style="opacity: 1;">
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
                                    <label for="name">{{ translate('Guarantor Name') }}</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>

                                <!-- NIN -->
                                <div class="form-group">
                                    <label for="nin">{{ translate('NIN') }}</label>
                                    <input type="text" name="nin" class="form-control" required>
                                </div>

                                <!-- Phone Number -->
                                <div class="form-group">
                                    <label for="phone_number">{{ translate('Phone Number') }}</label>
                                    <input type="text" name="phone_number" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Address -->
                                <div class="form-group">
                                    <label for="address">{{ translate('Address') }}</label>
                                    <input type="text" name="address" class="form-control">
                                </div>

                                <!-- Relationship -->
                                <div class="form-group">
                                    <label for="client_relationship">{{ translate('Relationship to Client') }}</label>
                                    <input type="text" name="client_relationship" class="form-control" required>
                                </div>

                                <!-- Job -->
                                <div class="form-group">
                                    <label for="job">{{ translate('Job') }}</label>
                                    <input type="text" name="job" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Photo -->
                                <div class="form-group">
                                    <label for="photo">{{ translate('Guarantor Photo') }}</label>
                                    <input type="file" name="photo" class="form-control" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- National ID Photo -->
                                <div class="form-group">
                                    <label for="national_id_photo">{{ translate('National ID Photo') }}</label>
                                    <input type="file" name="national_id_photo" class="form-control" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Fields -->
                        <input type="hidden" name="client_id" value="{{ $client->id }}">
                        <input type="hidden" name="added_by" value="{{ auth()->user()->id }}">

                        <!-- Error Display -->
                        <div id="guarantorErrorMessages" class="alert alert-danger d-none"></div>

                        <div class="d-flex justify-content-end mt-3">
                            <button type="reset" class="btn btn-secondary mr-2">{{ translate('Reset') }}</button>
                            <button type="submit" class="btn btn-primary">{{ translate('Add Guarantor') }}</button>
                        </div>
                    </form>
                    <!-- Form Ends Here -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Initialize select2
    $('#guarantors').select2();

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

                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: '{{ translate('Guarantor added successfully.') }}',
                    showConfirmButton: false,
                    timer: 2000
                });

                // Update the Guarantors select list
                updateGuarantorsList(response.guarantors, response.newGuarantorId);
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
                    Swal.fire({
                        icon: 'error',
                        title: '{{ translate('An error occurred. Please try again.') }}',
                    });
                }
            }
        });
    });

    function updateGuarantorsList(guarantors, newGuarantorId) {
        // Clear the existing options
        $('#guarantors').empty();

        // Append new options
        $.each(guarantors, function(index, guarantor) {
            var optionText = guarantor.name + ' - NIN: ' + guarantor.nin + ' (' + guarantor.phone_number + ')';
            var option = new Option(optionText, guarantor.id, false, false);
            $('#guarantors').append(option);
        });

        // Update the selected options to include the new guarantor
        var selectedGuarantors = $('#guarantors').val() || [];
        selectedGuarantors.push(newGuarantorId.toString()); // Ensure it's a string
        $('#guarantors').val(selectedGuarantors).trigger('change.select2');
    }
});

</script>
@endpush
