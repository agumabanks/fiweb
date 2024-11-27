@extends('layouts.admin.app')

@section('title', translate('Edit Loan Plan'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="text-muted">{{ translate('Edit Loan Plan') }}</h1>
        </div>

        <!-- Edit Loan Plan Form -->
        <div class="card shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.loan-plans.update', $loanPlan->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Plan Name -->
                    <div class="mb-3">
                        <label for="plan_name" class="form-label">{{ translate('Plan Name') }}</label>
                        <input type="text" class="form-control" id="plan_name" name="plan_name" value="{{ $loanPlan->plan_name }}" placeholder="{{ translate('Enter plan name') }}" required>
                    </div>

                    <!-- Minimum Amount -->
                    <div class="mb-3">
                        <label for="min_amount" class="form-label">{{ translate('Minimum Amount') }}</label>
                        <input type="number" class="form-control" id="min_amount" name="min_amount" value="{{ $loanPlan->min_amount }}" placeholder="{{ translate('Enter minimum loan amount') }}" required>
                    </div>

                    <!-- Maximum Amount -->
                    <div class="mb-3">
                        <label for="max_amount" class="form-label">{{ translate('Maximum Amount') }}</label>
                        <input type="number" class="form-control" id="max_amount" name="max_amount" value="{{ $loanPlan->max_amount }}" placeholder="{{ translate('Enter maximum loan amount') }}" required>
                    </div>

                    <!-- Installment Value -->
                    <div class="mb-3">
                        <label for="installment_value" class="form-label">{{ translate('Installment Value (%)') }}</label>
                        <input type="number" class="form-control" id="installment_value" name="installment_value" value="{{ $loanPlan->installment_value }}" placeholder="{{ translate('Enter installment percentage') }}" required>
                    </div>

                    <!-- Installment Interval -->
                    <div class="mb-3">
                        <label for="installment_interval" class="form-label">{{ translate('Installment Interval (Days)') }}</label>
                        <input type="number" class="form-control" id="installment_interval" name="installment_interval" value="{{ $loanPlan->installment_interval }}" placeholder="{{ translate('Enter interval between installments in days') }}" required>
                    </div>

                    <!-- Total Installments -->
                    <div class="mb-3">
                        <label for="total_installments" class="form-label">{{ translate('Total Installments') }}</label>
                        <input type="number" class="form-control" id="total_installments" name="total_installments" value="{{ $loanPlan->total_installments }}" placeholder="{{ translate('Enter total number of installments') }}" required>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-3">
                        <label for="instructions" class="form-label">{{ translate('Instructions') }}</label>
                        <textarea class="form-control" id="instructions" name="instructions" rows="4" placeholder="{{ translate('Enter any additional instructions (optional)') }}">{{ $loanPlan->instructions }}</textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{ translate('Update Plan') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<!-- Custom Scripts (if needed) -->
@endpush

@push('script_2')
<!-- Secondary Scripts (if needed) -->
@endpush
