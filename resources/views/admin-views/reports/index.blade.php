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
                <div class="form-group mr-2">
                    <label for="period" class="mr-2">Select Period:</label>
                    <select name="period" id="period" class="form-control">
                        <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
            
                <div class="form-group mr-2" id="date-range" style="display: none;">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="text" name="start_date" id="start_date" class="form-control datepicker" value="{{ isset($startDate) ? (is_string($startDate) ? $startDate : $startDate->format('Y-m-d')) : '' }}">
                </div>
            
                <div class="form-group mr-2" id="end-date" style="display: none;">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="text" name="end_date" id="end_date" class="form-control datepicker" value="{{ isset($endDate) ? (is_string($endDate) ? $endDate : $endDate->format('Y-m-d')) : '' }}">
                </div>
            
                <button type="submit" class="btn btn-primary">Filter</button>
               
            
                {{-- Export PDF Button --}}
                <button type="submit" formaction="{{ route('admin.reports.exportDailyAnalyticsPDF') }}" class="btn btn-primary ml-2">Export to PDF </button>

                 <!-- go to agents -->
                {{-- <button type="button" class="btn btn-primary ml-2" data-toggle="modal" data-target="#actualCashModal">
                    Agent Reports
                </button> --}}
                <a class="btn btn-primary ml-2" href="{{route('admin.agent.report')}}"
                                   title="{{translate('Agent Report')}}">
                                    <span class="text-truncate">{{translate('Agent Reports')}}</span>
                                </a>
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
                          Other  Cash In's 
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
                        
                      
                        {{-- <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Cash Flow
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['totalCashFlow'] ?? 0, 0) }}</span>
                        </li> --}}
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
                            Total Cash Intflow
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

            <!-- Loan Processing Fees and Cashouts -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5 class="text-secondary">Other  Cash In's</h5>
                    <ul class="list-group">
                        {{-- <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Processing Fees
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['loanProcessingFees'] ?? 0, 0) }}</span>
                        </li> --}}
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
                    {{-- <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Processing Fees
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['loanProcessingFees'] ?? 0, 0) }}</span>
                        </li>
                    </ul> --}}
                </div>
                <div class="col-md-6">
                    <h5 class="text-secondary">Safe Balance</h5>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Actual Cash
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['actualCash'] ?? 0, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Excess Funds
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['excussF'] ?? 20, 0) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Shortages
                            <span class="font-weight-bold">UGX {{ number_format($financialSummary['shotage'] ?? 0, 0) }}</span>
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
                 <th>Description</th>
                <th>Date</th>
                <th class="text-right">Amount (UGX)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($financialSummary['allExpenses'] as $expense)
            <tr>
                 <td>{{ $expense->description ?? 'N/A' }}</td>
                <td>{{ $expense->created_at->format('Y-m-d') }}</td>
                <td class="text-right">UGX {{ number_format($expense->amount, 0) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total Amount</th>
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
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h2 class="h5 m-0 font-weight-bold text-primary">Agents Report</h2>
        </div>
        <div class="card-body">
            @if(!empty($agentReportData['agentPerformance']) && count($agentReportData['agentPerformance']) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Agent Name</th>
                            <th class="text-center">Client Count</th>
                            <th class="text-center">Clients Paid Today</th>
                            {{-- <th class="text-right">Total Money Out (UGX)</th> --}}
                            <th class="text-right">Expected Daily (UGX)</th>
                            <th class="text-right">Amount Collected (UGX)</th>
                            <th class="text-right">Performance (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agentReportData['agentPerformance'] as $agentData)
                        <tr>
                            <td>{{ $agentData['agent']->f_name }} {{ $agentData['agent']->l_name }}</td>
                            <td class="text-center">{{ $agentData['client_count'] }}</td>
                            <td class="text-center">{{ $agentData['clients_paid_today'] }}</td>
                            {{-- <td class="text-right">UGX {{ number_format($agentData['total_money_out'], 0) }}</td> --}}
                            <td class="text-right">UGX {{ number_format($agentData['expected_daily'], 0) }}</td>
                            <td class="text-right">UGX {{ number_format($agentData['amount_collected'], 0) }}</td>
                            <td class="text-right">{{ number_format($agentData['performance_percentage'], 2) }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="thead-light">
                        <tr>
                            <th>Total</th>
                            <th class="text-center">{{ $agentReportData['totals']['total_clients'] ?? 0 }}</th>
                            <th class="text-center">{{ $agentReportData['totals']['total_clients_paid_today'] ?? 0 }}</th>
                            {{-- <th class="text-right">UGX {{ number_format($agentReportData['totals']['total_money_out'] ?? 0, 0) }}</th> --}}
                            <th class="text-right">UGX {{ number_format($agentReportData['totals']['total_expected_daily'] ?? 0, 0) }}</th>
                            <th class="text-right">UGX {{ number_format($agentReportData['totals']['total_amount_collected'] ?? 0, 0) }}</th>
                            <th class="text-right">{{ number_format($agentReportData['totals']['total_performance'] ?? 0, 2) }}%</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <p>No agent performance data available for the selected period.</p>
            @endif
        </div>
    </div>

    <!-- Loan Statistics Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h2 class="h5 m-0 font-weight-bold text-primary">Loan Statistics</h2>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <!-- Loans Disbursed -->
                <div class="col-md-4">
                    <h5 class="text-secondary">Total Loans Disbursed</h5>
                    <p class="font-weight-bold">UGX {{ number_format($loanStatistics['loansDisbursed'] ?? 0, 0) }}</p>
                </div>
                <!-- Repayments Received -->
                <div class="col-md-4">
                    <h5 class="text-secondary">Repayments Received</h5>
                    <p class="font-weight-bold">UGX {{ number_format($loanStatistics['repaymentsReceived'] ?? 0, 0) }}</p>
                </div>
                <!-- Interest Earned -->
                <div class="col-md-4">
                    <h5 class="text-secondary">Interest Expected</h5>
                    <p class="font-weight-bold">UGX {{ number_format($loanStatistics['interestEarned'] ?? 0, 0) }}</p>
                </div>
            </div>

            <!-- Delinquency Metrics -->
            <div class="mb-4">
                <h5 class="text-secondary">Delinquency Metrics</h5>
                @if(isset($loanStatistics['delinquencyMetrics']))
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Metric</th>
                            <th class="text-right">Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Overdue Loans</td>
                            <td class="text-right">{{ $loanStatistics['delinquencyMetrics']['overdueLoans'] }}</td>
                        </tr>
                        <tr>
                            <td>Delinquency Rate</td>
                            <td class="text-right">{{ number_format($loanStatistics['delinquencyMetrics']['delinquencyRate'], 2) }}%</td>
                        </tr>
                        <tr>
                            <td>Aging 30 Days</td>
                            <td class="text-right">{{ $loanStatistics['delinquencyMetrics']['aging30Days'] }}</td>
                        </tr>
                        <tr>
                            <td>Aging 60 Days</td>
                            <td class="text-right">{{ $loanStatistics['delinquencyMetrics']['aging60Days'] }}</td>
                        </tr>
                        <tr>
                            <td>Aging 90 Days</td>
                            <td class="text-right">{{ $loanStatistics['delinquencyMetrics']['aging90Days'] }}</td>
                        </tr>
                    </tbody>
                </table>
                @else
                <p>No delinquency metrics available for the selected period.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Key Highlights Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h2 class="h5 m-0 font-weight-bold text-primary">Key Highlights</h2>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <!-- New Clients Onboarded -->
                <div class="col-md-4">
                    <h5 class="text-secondary">New Clients Onboarded</h5>
                    <p class="font-weight-bold">{{ $keyHighlights['newClientsCount'] ?? 0 }}</p>
                </div>
                <!-- Total Repayments Received -->
                <div class="col-md-4">
                    <h5 class="text-secondary">Total Repayments Received</h5>
                    <p class="font-weight-bold">UGX {{ number_format($keyHighlights['totalRepayments'] ?? 0, 0) }}</p>
                </div>
                <!-- Current Delinquency Rate -->
                <div class="col-md-4">
                    <h5 class="text-secondary">Current Delinquency Rate</h5>
                    <p class="font-weight-bold">{{ number_format($keyHighlights['currentDelinquencyRate'] ?? 0, 2) }}%</p>
                </div>
            </div>

            <!-- Top Performing Agents -->
            <div class="mb-4">
                <h5 class="text-secondary">Top Performing Agents</h5>
                @if(!empty($keyHighlights['topAgents']) && count($keyHighlights['topAgents']) > 0)
                <table class="table table-bordered table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th>Agent Name</th>
                            <th class="text-right">Total Collected (UGX)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($keyHighlights['topAgents'] as $agent)
                        <tr>
                            <td>{{ $agent->f_name }} {{ $agent->l_name }}</td>
                            <td class="text-right">
                                UGX {{ number_format($agent->payments->sum('amount'), 0) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p>No top performing agent data available for the selected period.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Client Report Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h2 class="h5 m-0 font-weight-bold text-primary">Client Report</h2>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <!-- Total Clients -->
                <div class="col-md-3">
                    <h5 class="text-secondary">Total Clients</h5>
                    <p class="font-weight-bold">{{ $clientReportData['totalClients'] ?? 0 }}</p>
                </div>
                <!-- Clients with Balance -->
                <div class="col-md-3">
                    <h5 class="text-secondary">Clients with Balance</h5>
                    <p class="font-weight-bold">{{ $clientReportData['clientsWithBalance'] ?? 0 }}</p>
                </div>
                <!-- Total Credit Balance -->
                <div class="col-md-3">
                    <h5 class="text-secondary">Total Credit Balance</h5>
                    <p class="font-weight-bold">UGX {{ number_format($clientReportData['totalCreditBalance'] ?? 0, 0) }}</p>
                </div>
                <!-- Clients Paid -->
                <div class="col-md-3">
                    <h5 class="text-secondary">Clients Paid</h5>
                    <p class="font-weight-bold">{{ $clientReportData['clientsPaid'] ?? 0 }}</p>
                </div>
            </div>
            <div class="row mb-4">
                <!-- Clients Unpaid -->
                <div class="col-md-6">
                    <h5 class="text-secondary">Clients Unpaid</h5>
                    <p class="font-weight-bold">{{ $clientReportData['clientsUnpaid'] ?? 0 }}</p>
                </div>
                <!-- Clients Paid in Advance -->
                <div class="col-md-6">
                    <h5 class="text-secondary">Clients Paid in Advance</h5>
                    <p class="font-weight-bold">{{ $clientReportData['clientsPaidAdvance'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Loans Disbursed Table -->
            <div class="mb-4">
                <h5 class="text-secondary">Loans Disbursed</h5>
                @if(!empty($clientReportData['clientLoans']) && count($clientReportData['clientLoans']) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Client Name</th>
                                <th>Agent </th>
                                <th class="text-right">Amount Given (UGX)</th>
                                <th class="text-right">Profit to be Made (UGX)</th>
                                <th>Loan Date</th>
                                <th>Due Date</th>
                                <th class="text-right">Client Phone</th>
                                <!-- Add other headers as needed -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientReportData['clientLoans'] as $loan)
                            <tr>
                                <td>{{ $loan['client_name'] }}</td>
                                <td>{{ $loan['agent_name'] }}</td>
                                <td class="text-right">{{ number_format($loan['amount_given'], 2) }}</td>
                                <td class="text-right">{{ number_format($loan['profit_to_be_made'], 2) }}</td>
                                <td>{{ $loan['loan_date'] }}</td>
                                <td>{{ $loan['due_date'] }}</td>
                                <td class="text-right">{{ $loan['phone'] }}</td>
                                <!-- Add other fields as needed -->
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <p>No loans disbursed during the selected period.</p>
                @endif
            </div>
            <!-- End of Loans Disbursed Table -->
        </div>
    </div>

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
