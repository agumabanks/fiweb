@extends('layouts.admin.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid my-4">

    <!-- Filter Form -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $pageTitle }}</h1>
        <div class="d-flex">
            <!-- Filter Form -->
            <form action="{{ route('admin.report.index') }}" method="GET" class="form-inline">
                <!-- Period Selection -->
                <div class="form-group mr-2">
                    <label for="period" class="mr-2">Select Period:</label>
                    <select name="period" id="period" class="form-control">
                        <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>

                <!-- Date Range Selection -->
                <div class="form-group mr-2" id="date-range" style="display: none;">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="text" name="start_date" id="start_date" class="form-control datepicker" value="{{ isset($startDate) ? (is_string($startDate) ? $startDate : $startDate->format('Y-m-d')) : '' }}">
                </div>

                <div class="form-group mr-2" id="end-date" style="display: none;">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="text" name="end_date" id="end_date" class="form-control datepicker" value="{{ isset($endDate) ? (is_string($endDate) ? $endDate : $endDate->format('Y-m-d')) : '' }}">
                </div>

                <!-- Buttons -->
                <button type="submit" class="btn btn-primary">Filter</button>

                {{-- Export PDF Button --}}
                <button type="submit" formaction="{{ route('admin.reports.exportDailyAnalyticsPDF') }}" class="btn btn-primary ml-2">Export to PDF</button>
            </form>
        </div>
    </div>

    <!-- Financial Summary Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h2 class="h5 m-0 font-weight-bold text-primary">Financial Summary</h2>
        </div>
        <div class="card-body">
            <!-- Cashflow Statement -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="text-secondary">Cashflow Statement</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Capital Added
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['capitalAdded'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cash In (Amount Paid)
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['CashIn'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Other Cash In's
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['OtherCashIn'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Expense
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['totalExpensesAmount'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cash Out
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['loanDisbursements'] ?? 0, 0) }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="text-secondary">Balances</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Opening Balance
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['openingBalance'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Cash Inflow
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['totalCashInflow'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Cash Outflow
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['cashOutflow'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Closing Balance
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['closingBalance'] ?? 0, 0) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Loan Processing Fees -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Processing Fees
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['loanProcessingFees'] ?? 0, 0) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Other Cash In's and Safe Balance -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="text-secondary">Other Cash In's</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Shares
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['membershipShareFunds'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Savings
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['savingsDeposits'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Memberships
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['membershipFunds'] ?? 0, 0) }}</span>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5 class="text-secondary">Safe Balance</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Actual Cash
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['actualCash'] ?? 0, 0) }}</span>
                            <!-- Button to Open Modal -->
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#actualCashModal">
                                Add Actual Cash
                            </button>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Excess Funds
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['excessFunds'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Shortages
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['totalShortages'] ?? 0, 0) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Expenses Breakdown -->
            <div class="mb-4">
                <h5 class="text-secondary">Expenses Breakdown</h5>
                @if(!empty($financialSummary['allExpenses']) && $financialSummary['allExpenses']->count() > 0)
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th class="text-right">Amount (UGX)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($financialSummary['allExpenses'] as $expense)
                        <tr>
                            <td>{{ ucfirst($expense->category) }}</td>
                            <td>{{ $expense->description ?? 'N/A' }}</td>
                            <td>{{ $expense->created_at->format('Y-m-d') }}</td>
                            <td class="text-right">UGX {{ number_format($expense->amount, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total Amount</th>
                            <th class="text-right">UGX {{ number_format($financialSummary['totalExpensesAmount'] ?? 0, 0) }}</th>
                        </tr>
                    </tfoot>
                </table>
                @else
                <p>No expense data available for the selected period.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Agents Report Section -->
    <!-- ... (Rest of your content remains unchanged) -->

</div>

<!-- Actual Cash Modal -->
<div class="modal fade" id="actualCashModal" tabindex="-1" role="dialog" aria-labelledby="actualCashModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.actual-cash.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Actual Cash</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="amount">Amount (UGX)</label>
                            <input type="number" name="amount" id="amount" class="form-control" required min="0" step="0.01">
                        </div>
                    </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Actual Cash</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('script_2')
<!-- Include jQuery and Bootstrap JS for modal functionality -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<!-- Bootstrap JS for modal -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- Include a datepicker library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.standalone.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

<script>
    $(document).ready(function() {
        function toggleDateRange() {
            if ($('#period').val() === 'custom') {
                $('#date-range').show();
                $('#end-date').show();
            } else {
                $('#date-range').hide();
                $('#end-date').hide();
            }
        }

        toggleDateRange();

        $('#period').change(function() {
            toggleDateRange();
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
        });
    });
</script>
@endpush
