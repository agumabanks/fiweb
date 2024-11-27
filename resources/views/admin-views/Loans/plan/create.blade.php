@extends('layouts.admin.app')

@section('title', translate('dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header pb-2">
            <h1 class="page-header-title text-primary mb-1">{{ translate('welcome') }} , {{ auth('user')->user()->f_name }}.</h1>
            <p class="welcome-msg">{{ translate('Add Loans Plan') }}</p>
        </div>

        <!-- Create New Loan Plan Form -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="mb-4">{{ translate('Create New Plan') }}</h2>
                
                <!-- Form -->
                <form action="{{ route('admin.create-loan-plan') }}" method="POST">
                    @csrf
                    <div class="row">
                        <!-- Plan Name -->
                        <div class="form-group col-md-6">
                            <label for="plan_name">{{ translate("Plan Name") }} <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="{{ translate("Enter Plan's Name") }}" required>
                        </div>

                        <!-- Minimum Amount -->
                        <div class="form-group col-md-6">
                            <label for="min_amount">{{ translate("Minimum Amount") }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="min_amount" name="min_amount" placeholder="{{ translate('Enter Minimum Amount') }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Maximum Amount -->
                        <div class="form-group col-md-6">
                            <label for="max_amount">{{ translate("Maximum Amount") }} <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_amount" name="max_amount" placeholder="{{ translate('Enter Maximum Amount') }}" required>
                        </div>

                        <!-- Per Installment -->
                        <div class="form-group col-md-6">
                            <label for="installment_value">{{ translate("Per Installment") }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="installment_value" name="installment_value" placeholder="{{ translate("Enter Installment's Value") }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Installment Interval -->
                        <div class="form-group col-md-6">
                            <label for="installment_interval">{{ translate("Installment Interval") }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="installment_interval" name="installment_interval" placeholder="{{ translate('Enter Installment Interval') }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ translate('Days') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Total Installments -->
                        <div class="form-group col-md-6">
                            <label for="total_installments">{{ translate("Total Installments") }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="total_installments" name="total_installments" placeholder="{{ translate('Enter Total Installments Count') }}" required>
                                <div class="input-group-append">
                                    <span class="input-group-text">{{ translate('Times') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="form-group">
                        <label for="instructions">{{ translate("Instructions") }}</label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="4" placeholder="{{ translate('Enter any additional instructions (optional)') }}"></textarea>
                    </div>

                    <!-- Submit Button and Go Back -->
                    <div class="d-flex justify-content-end gap-3">
                        <button type="submit" class="btn btn-primary">{{ translate('Create Plan') }}</button>
                        <a href="{{ route('admin.loan-plans') }}" class="btn btn-secondary">{{ translate('Go Back') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<!-- Add any custom scripts here if needed -->
@endpush

@push('script_2')
<!-- Add any secondary custom scripts here if needed -->
@endpush
