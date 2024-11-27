@extends('layouts.admin.app')

@section('title', 'Agent Dashboard')

@section('content')
<div class="container-fluid py-5">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-muted">{{ ucfirst($period) }} Agent Dashboard</h1>
        <!-- Date Range Picker (Optional) -->
        <!--
        <div>
            <input type="text" id="dateRangePicker" class="form-control" placeholder="Select Date Range">
        </div>
        -->
    </div>
    <!-- End Page Header -->

    <!-- KPI Summary -->
    <div class="row text-center mb-5">
        <!-- Installments Collected -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm text-white" style="background-color: #007bff;">
                <div class="card-body">
                    <h5 class="text-uppercase">Installments Collected</h5>
                    <h3 id="totalInstallmentsCollected">{{ $totalInstallmentsCollected }}</h3>
                    <p>{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
                </div>
            </div>
        </div>
        <!-- Amount Collected -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm text-white bg-primary" >
                <div class="card-body">
                    <h5 class="text-uppercase text-white">Amount Collected</h5>
                    <h3 class="text-white">UGX <span id="totalAmountCollected">{{ number_format($totalAmountCollected, 0) }}</span></h3>
                    <p>{{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}</p>
                </div>
            </div>
        </div>
        <!-- Overdue Installments -->
        <div class="col-md-4 mb-3">
            <div class="card border-0 shadow-sm text-white" style="background-color: #ffc107;">
                <div class="card-body">
                    <h5 class="text-uppercase">Overdue Installments</h5>
                    <h3 id="totalOverdueInstallments">{{ $totalOverdueInstallments }}</h3>
                    <p>As of {{ $endDate->format('d M Y') }}</p>
                </div>
            </div>
        </div>
    </div>
    <!-- End KPI Summary -->

    <!-- Agents Performance Summary -->
    <div class="card mb-5 border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="text-secondary mb-0">Agents Performance</h2>
        </div>
        <div class="card-body">
            @if(!empty($agentReportData['agentPerformance']))
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="agentsPerformanceTable">
                    <thead class="table-light">
                        <tr>
                            <th>Agent Name</th>
                            <th>Client Count</th>
                            <th>Total Money Out (UGX)</th>
                            <th>Expected Daily Collection (UGX)</th>
                            <th>Amount Collected (UGX)</th>
                            <th>Performance (%)</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentReportData['agentPerformance'] as $agentData)
                        <tr>
                            <td>{{ $agentData['agent']->f_name }} {{ $agentData['agent']->l_name }}</td>
                            <td>{{ $agentData['client_count'] }}</td>
                            <td>{{ number_format($agentData['total_money_out'], 0) }}</td>
                            <td>{{ number_format($agentData['expected_daily'], 0) }}</td>
                            <td>{{ number_format($agentData['amount_collected'], 0) }}</td>
                            <td>{{ number_format($agentData['performance_percentage'], 2) }}%</td>
                            <td class="text-center">
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('admin.agent.report.performance', $agentData['agent']->id) }}"
                                   title="View Details">
                                    <i class="tio-visible"></i>
                                </a>
                                <!-- Additional actions can be added here -->
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th>{{ $agentReportData['totals']['total_clients'] }}</th>
                            <th>{{ number_format($agentReportData['totals']['total_money_out'], 0) }}</th>
                            <th>{{ number_format($agentReportData['totals']['total_expected_daily'], 0) }}</th>
                            <th>{{ number_format($agentReportData['totals']['total_amount_collected'], 0) }}</th>
                            <th>{{ number_format($agentReportData['totals']['total_performance'], 2) }}%</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p class="text-center text-muted">{{ translate('No agent performance data available.') }}</p>
            @endif
        </div>
    </div>
    <!-- End Agents Performance Summary -->

    <!-- Loans Overview -->
    <div class="row">
        <!-- Active Loans -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="text-center mb-0">Active Loans ({{ $totalActiveLoans }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($activeLoans->count() > 0)
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Amount (UGX)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($activeLoans as $loan)
                                <tr>
                                    <td>{{ $loan->id }}</td>
                                    <td>{{ $loan->client->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($loan->amount, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-center text-muted my-3">{{ translate('No active loans available.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Paid Loans -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="text-center mb-0">Paid Loans ({{ $totalPaidLoans }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($paidLoans->count() > 0)
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Amount (UGX)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paidLoans as $loan)
                                <tr>
                                    <td>{{ $loan->id }}</td>
                                    <td>{{ $loan->client->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($loan->amount, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-center text-muted my-3">{{ translate('No paid loans available.') }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Overdue Loans -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="text-center mb-0">Overdue Loans ({{ $totalOverdueLoans }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($overdueLoans->count() > 0)
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Client Name</th>
                                    <th>Amount (UGX)</th>
                                    <th>Overdue Since</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($overdueLoans as $loan)
                                <tr>
                                    <td>{{ $loan->id }}</td>
                                    <td>{{ $loan->client->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($loan->amount, 0) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($loan->next_installment_date)->format('d M Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-center text-muted my-3">{{ translate('No overdue loans available.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- End Loans Overview -->

    <!-- Recent Installment Payments -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h2 class="text-secondary mb-0">Recent Installment Payments</h2>
        </div>
        <div class="card-body">
            @if($installmentPayments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="recentPaymentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Payment ID</th>
                            <th>Client Name</th>
                            <th>Agent Name</th>
                            <th>Amount (UGX)</th>
                            <th>Payment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($installmentPayments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->client->name ?? 'N/A' }}</td>
                            <td>{{ $payment->agent->f_name ?? 'N/A' }} {{ $payment->agent->l_name ?? '' }}</td>
                            <td>{{ number_format($payment->amount, 0) }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-muted">{{ translate('No recent installment payments available.') }}</p>
            @endif
        </div>
    </div>
    <!-- End Recent Installment Payments -->
</div>
@endsection

@push('script_2')
<!-- Include any additional scripts if needed -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTables for enhanced table functionalities
        $('#agentsPerformanceTable').DataTable({
            paging: true,
            ordering: true,
            info: false,
            searching: true,
            order: [[5, 'desc']],
            columnDefs: [
                { orderable: false, targets: 6 } // Disable ordering on the Actions column
            ]
        });

        $('#recentPaymentsTable').DataTable({
            paging: true,
            ordering: true,
            info: false,
            searching: true,
            order: [[4, 'desc']],
        });

        Optional: Initialize date range picker for filtering data
        $('#dateRangePicker').daterangepicker({
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
    });
</script>
@endpush
