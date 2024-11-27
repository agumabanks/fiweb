@extends('layouts.admin.app')

@section('title', ucfirst($reportPeriod) . ' Agent Report - Summary')

@section('content')
<div class="container my-4">
   
    <!-- Download PDF Button -->
    <div class="d-flex justify-content-end mb-3">
        <div class="mr-2">
             <!-- Date Range Selection Form -->
            <form method="GET" action="{{ route('admin.agent.report.performance', ['agentId' => $agent->id]) }}" class="form-inline mb-4">
                <div class="form-group mr-2">
                    <label for="period" class="mr-2">Select Period:</label>
                    <select name="period" id="period" class="form-control ">
                        <option value="daily" {{ $reportPeriod == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $reportPeriod == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $reportPeriod == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="custom" {{ $reportPeriod == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
                <div class="form-group mr-2" id="customDateInputs" style="display: none;">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control mr-2" value="{{ $startDateInput }}">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDateInput }}">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>

        </div>
        <div class="mr-0">
            <a href="{{ route('admin.agent.report-pdf', ['agentId' => $agent->id]) }}?period={{ $reportPeriod }}&start_date={{ $startDateInput }}&end_date={{ $endDateInput }}" class="btn btn-primary">
                Download PDF
            </a>
        </div>
        
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>{{ ucfirst($reportPeriod) }} Report for Agent: {{ $agent->f_name }} {{ $agent->l_name }}</h3>
            <p>Date: {{ $reportDateRange }}</p>
        </div>
        <div class="card-body">
            <!-- Summary Section -->
            <div class="row mb-4">
                <!-- Total Clients -->
                <div class="col-md-3">
                    <div class="card text-center bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Total Clients</h5>
                            <p class="card-text display-4 text-white">{{ $totalClients }}</p>
                        </div>
                    </div>
                </div>
                <!-- Total Loan Amount -->
                <div class="col-md-3">
                    <div class="card text-center bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Total Loan Amount</h5>
                            <p class="card-text display-4 text-white">{{ number_format($totalLoanAmount, 0) }} /=</p>
                        </div>
                    </div>
                </div>
                <!-- Total Collected -->
                <div class="col-md-3">
                    <div class="card text-center bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Total Collected</h5>
                            <p class="card-text display-4 text-white">{{ number_format($totalCollected, 0) }} /=</p>
                        </div>
                    </div>
                </div>
                <!-- Total Outstanding Amount -->
                <div class="col-md-3">
                    <div class="card text-center bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title text-white">Total Outstanding Amount</h5>
                            <p class="card-text display-4 text-white">UGX {{ number_format($totalOutstandingAmount, 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Client Payment Status Summary -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success">Clients Who Paid</h5>
                            <p class="card-text display-4">{{ $totalClientsPaid }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <h5 class="card-title text-danger">Clients Unpaid</h5>
                            <p class="card-text display-4">{{ $totalClientsUnpaid }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h5 class="card-title text-info">Clients with Advance Payment</h5>
                            <p class="card-text display-4">{{ $totalClientsAdvancePaid }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning">Clients with Partial Payment</h5>
                            <p class="card-text display-4">{{ $totalClientsPartialPaid }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <canvas id="paymentStatusChart" style="max-height: 400px;"></canvas>
                </div>
            </div>

            <!-- Profit/Loss Chart -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <h4 class="text-center">Agent Profit & Loss Overview</h4>
                    <canvas id="profitLossChart" style="max-height: 400px;"></canvas>
                </div>
            </div>

            <!-- Expenses Section -->
            <h4 class="mt-4">Agent Expenses</h4>
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Expense Description</th>
                            <th>Amount (UGX)</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentExpenses as $expense)
                        <tr>
                            <td>{{ $expense->description }}</td>
                            <td>UGX {{ number_format($expense->amount, 0) }}</td>
                            <td>{{ $expense->created_at->format('Y-m-d') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total Expenses</th>
                            <th>UGX {{ number_format($totalAgentExpenses, 0) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Client Details Table -->
            <h4 class="mt-4">Client Details</h4>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Client Name</th>
                            <th>Loan Amount (UGX)</th>
                            <th>Expected Payment (UGX)</th>
                            <th>Amount Collected (UGX)</th>
                            <th>Outstanding Amount (UGX)</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientDetails as $client)
                        <tr>
                            <td>{{ $client['name'] }}</td>
                            <td>UGX {{ number_format($client['loan_amount'], 0) }}</td>
                            <td>UGX {{ number_format($client['expected_payment'], 0) }}</td>
                            <td>UGX {{ number_format($client['amount_collected'], 0) }}</td>
                            <td>UGX {{ number_format($client['outstanding_amount'], 0) }}</td>
                            <td>
                                <span class="badge 
                                    @if($client['payment_status'] == 'Paid') badge-success
                                    @elseif($client['payment_status'] == 'Advance Paid') badge-info
                                    @elseif($client['payment_status'] == 'Partial Payment') badge-warning
                                    @else badge-danger @endif">
                                    {{ $client['payment_status'] }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Totals</th>
                            <th>UGX {{ number_format($totalLoanAmount, 0) }}</th>
                            <th>UGX {{ number_format($totalExpectedPayments, 0) }}</th>
                            <th>UGX {{ number_format($totalCollected, 0) }}</th>
                            <th>UGX {{ number_format($totalOutstandingAmount, 0) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var periodSelect = document.getElementById('period');
        var customDateInputs = document.getElementById('customDateInputs');

        function toggleCustomDateInputs() {
            if (periodSelect.value === 'custom') {
                customDateInputs.style.display = 'flex';
            } else {
                customDateInputs.style.display = 'none';
            }
        }

        periodSelect.addEventListener('change', toggleCustomDateInputs);

        // Initialize on page load
        toggleCustomDateInputs();

        // Client Payment Status Pie Chart
        var paymentStatusData = {
            labels: ['Paid', 'Unpaid', 'Advance Paid', 'Partial Payment'],
            datasets: [{
                data: [{{ $totalClientsPaid }}, {{ $totalClientsUnpaid }}, {{ $totalClientsAdvancePaid }}, {{ $totalClientsPartialPaid }}],
                backgroundColor: ['#28a745', '#dc3545', '#17a2b8', '#ffc107'],
            }]
        };

        var chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Client Payment Status Distribution'
            },
            legend: {
                position: 'bottom'
            }
        };

        var ctx = document.getElementById('paymentStatusChart').getContext('2d');
        var paymentStatusChart = new Chart(ctx, {
            type: 'pie',
            data: paymentStatusData,
            options: chartOptions
        });

        // Profit/Loss Bar Chart
        var profitLossData = {
            labels: ['Revenue', 'Expenses', 'Net Profit'],
            datasets: [{
                label: 'Amount (UGX)',
                data: [{{ $totalRevenue }}, {{ $totalAgentExpenses }}, {{ $netProfitLoss }}],
                backgroundColor: ['#28a745', '#dc3545', '#17a2b8']
            }]
        };

        var profitLossOptions = {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Agent Profit & Loss Overview'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return 'UGX ' + value.toLocaleString();
                        }
                    }
                }]
            },
            legend: {
                display: false
            }
        };

        var profitLossCtx = document.getElementById('profitLossChart').getContext('2d');
        var profitLossChart = new Chart(profitLossCtx, {
            type: 'bar',
            data: profitLossData,
            options: profitLossOptions
        });
    });
</script>
@endpush
