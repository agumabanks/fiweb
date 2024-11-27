@extends('layouts.admin.app')

@section('title', $pageTitle)

@push('css_or_js')
    <!-- Additional CSS if needed -->
@endsection

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $pageTitle }}</h1>
        <p class="welcome-msg">Generate and view various reports related to savings accounts.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.savings.generateReports') }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="report_type" class="form-label">Report Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="report_type" name="report_type" required>
                            <option value="">Select Report Type</option>
                            <option value="total_deposits">Total Deposits per Account</option>
                            <option value="total_withdrawals">Total Withdrawals per Account</option>
                            <option value="interest_earned">Interest Earned per Account Type</option>
                            <option value="monthly_growth">Monthly Savings Growth</option>
                            <option value="top_performing">Top Performing Accounts</option>
                        </select>
                        @error('report_type')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                        @error('start_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                        @error('end_date')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                </div>
            </form>

            <!-- Optional: Export Report Form -->
            <form action="{{ route('admin.savings.exportReport') }}" method="POST" class="mt-3">
                @csrf
                <input type="hidden" name="report_type" value="{{ old('report_type') }}">
                <input type="hidden" name="start_date" value="{{ old('start_date') }}">
                <input type="hidden" name="end_date" value="{{ old('end_date') }}">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="format" class="form-label">Export Format <span class="text-danger">*</span></label>
                        <select class="form-control" id="format" name="format" required>
                            <option value="">Select Format</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        @error('format')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-success w-100">Export Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush
