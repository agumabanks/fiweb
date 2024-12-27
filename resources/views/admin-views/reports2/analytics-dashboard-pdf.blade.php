<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Maslink (B) {{ ucfirst($period) }} Analytics Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .section h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #34495e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f7f7f7;
            font-weight: bold;
        }
        .totals-row th, .totals-row td {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .highlight {
            color: #d9534f;
            font-weight: bold;
        }
        .footer {
            position: fixed; 
            bottom: 0px; 
            left: 0px; 
            right: 0px;
            height: 50px; 
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 10px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="header">
        <h1>Maslink (B) Credit {{ ucfirst($period) }} Analytics Report</h1>
        <p>Report Date: {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
        <p>Period: {{ $startDate->format('F d, Y') }} to {{ $endDate->format('F d, Y') }}</p>
    </div>

    <!-- Financial Summary Section -->
    <div class="section">
        <h2>Financial Summary</h2>

        <!-- Cashflow Statement -->
        <h3>Cashflow Statement</h3>
        <table>
            <tr>
                <th>Capital Added</th>
                <td>{{ number_format($financialSummary['capitalAdded'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Cash In (Amount Paid)</th>
                <td>{{ number_format($financialSummary['CashIn'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Other Cash In's</th>
                <td>{{ number_format($financialSummary['OtherCashIn'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Total Expense</th>
                <td>{{ number_format($financialSummary['totalExpensesAmount'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Cash Out</th>
                <td>{{ number_format($financialSummary['loanDisbursements'] ?? 0, 0) }} /=</td>
            </tr>
        </table>

        <!-- Balances -->
        <h3>Balances</h3>
        <table>
            <tr>
                <th>Opening Balance</th>
                <td>{{ number_format($financialSummary['openingBalance'] ?? 0, 0) }} /=</td>
            </tr>
            {{-- <tr>
                <th>Total Cash Inflow</th>
                <td>{{ number_format($financialSummary['totalCashInflow'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Total Cash Outflow</th>
                <td>{{ number_format($financialSummary['cashOutflow'] ?? 0, 0) }} /=</td>
            </tr> --}}
            <tr>
                <th>Closing Balance</th>
                <td>{{ number_format($financialSummary['closingBalance'] ?? 0, 0) }} /=</td>
            </tr>
        </table>

        <!-- Loan Processing Fees -->
        {{-- <h3>Total Processing Fees</h3>
        <table>
            <tr>
                <th>Total Processing Fees</th>
                <td>{{ number_format($financialSummary['loanProcessingFees'] ?? 0, 0) }} /=</td>
            </tr>
        </table> --}}

        <!-- Other Cash In's -->
        {{-- <h3>Other Cash In's</h3>
        <table>
            <tr>
                <th>Total Shares</th>
                <td>{{ number_format($financialSummary['membershipShareFunds'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Total Savings</th>
                <td>{{ number_format($financialSummary['savingsDeposits'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Total Memberships</th>
                <td>{{ number_format($financialSummary['membershipFunds'] ?? 0, 0) }} /=</td>
            </tr>
        </table> --}}

        <!-- Safe Balance -->
        <h3>Safe Balance</h3>
        <table>
            <tr>
                <th>Actual Cash</th>
                <td>{{ number_format($financialSummary['actualCash'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Excess Funds</th>
                <td>{{ number_format($financialSummary['excussF'] ?? 0, 0) }} /=</td>
            </tr>
            <tr>
                <th>Total Shortages</th>
                <td>{{ number_format($financialSummary['shotage'] ?? 0, 0) }} /=</td>
            </tr>
        </table>

        <!-- Expenses Breakdown -->
        <h3>Expenses Breakdown</h3>
        @if(!empty($financialSummary['allExpenses']) && $financialSummary['allExpenses']->count() > 0)
        <table>
            <thead>
                <tr>
                    {{-- <th>Category</th> --}}
                    <th>Description</th>
                    <th>Date</th>
                    <th class="text-right">Amount (UGX)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($financialSummary['allExpenses'] as $expense)
                <tr>
                    {{-- <td>{{ ucfirst($expense->category) }}</td> --}}
                    {{-- <td>{{ $expense->description ?? 'N/A' }}</td>  --}}
                    <td>{{ ucfirst($expense->description ?? 'N/A') }}</td>
                    <td>{{ \Carbon\Carbon::parse($expense->created_at)->format('Y-m-d') }}</td>
                    <td class="text-left">{{ number_format($expense->amount, 0) }} /=</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <th colspan="2" class="text-right">Total Amount</th>
                    <th class="text-right">{{ number_format($financialSummary['totalExpensesAmount'] ?? 0, 0) }} /=</th>
                </tr>
            </tfoot>
        </table>
        @else
        <p>No expense data available for the selected period.</p>
        @endif

    </div>

  

     <!-- Loans Disbursed Table -->
     <div>
        <h3>Loans Disbursed</h3>
        @if(!empty($clientReportData['clientLoans']) && count($clientReportData['clientLoans']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>Agent Name</th>
                    <th>Amount Given (UGX)</th>
                    <th>Profit to be Made (UGX)</th>
                    <th>Loan Date</th>
                    {{-- <th>Due Date</th> --}}
                    <th>Client Phone</th>
                    {{-- <th>Status</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach($clientReportData['clientLoans'] as $loan)
                <tr>
                    <td>{{ $loan['client_name'] }}</td>
                    <td>{{ $loan['agent_name'] }}</td>
                    <td>{{ number_format($loan['amount_given'], 0) }} /=</td>
                    <td>{{ number_format($loan['profit_to_be_made'], 0) }} /=</td>
                    <td>{{ $loan['loan_date'] }}</td>
                    {{-- <td>{{ $loan['due_date'] }}</td> --}}
                    <td>{{ $loan['phone'] }}</td>
                    {{-- <td>{{ $loan['status'] }}</td> --}}
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No loans disbursed during the selected period.</p>
        @endif
    </div>


    <!-- Agents Report Section -->
    <div class="section">
        <h2>Agents Report</h2>
        @if(!empty($agentReportData['agentPerformance']) && count($agentReportData['agentPerformance']) > 0)
        <table>
            <thead>
                <tr>
                    <th>Agent Name</th>
                    <th>Client Count</th>
                    <th>Clients Paid Today</th>
                    <th>Expected Daily (UGX)</th>
                    <th>Amount Collected (UGX)</th>
                    <th>Performance (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($agentReportData['agentPerformance'] as $agentData)
                <tr>
                    <td>{{ $agentData['agent']->f_name }} {{ $agentData['agent']->l_name }}</td>
                    <td>{{ $agentData['client_count'] }}</td>
                    <td>{{ $agentData['clients_paid_today'] }}</td>
                    <td>{{ number_format($agentData['expected_daily'], 0) }} /=</td>
                    <td>{{ number_format($agentData['amount_collected'], 0) }} /=</td>
                    <td>{{ number_format($agentData['performance_percentage'], 0) }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <th>Total</th>
                    <th>{{ $agentReportData['totals']['total_clients'] ?? 0 }}</th>
                    <th>{{ $agentReportData['totals']['total_clients_paid_today'] ?? 0 }}</th>
                    <th>{{ number_format($agentReportData['totals']['total_expected_daily'] ?? 0, 0) }} /=</th>
                    <th>{{ number_format($agentReportData['totals']['total_amount_collected'] ?? 0, 0) }} /=</th>
                    <th>{{ number_format($agentReportData['totals']['total_performance'] ?? 0, 0) }}%</th>
                </tr>
            </tfoot>
        </table>
        @else
        <p>No agent performance data available for the selected period.</p>
        @endif
    </div>

      <!-- Loan Statistics Section -->
      <div class="section">
        <h2>Loan Statistics</h2>
        <div>
            <h3>Loan Metrics</h3>
            <table>
                <tr>
                    <th>Total Loans Disbursed</th>
                    <td>{{ number_format($loanStatistics['loansDisbursed'] ?? 0, 0) }} /=</td>
                </tr>
                <tr>
                    <th>Repayments Received</th>
                    <td>{{ number_format($loanStatistics['repaymentsReceived'] ?? 0, 0) }} /=</td>
                </tr>
                <tr>
                    <th>Interest Earned</th>
                    <td>{{ number_format($loanStatistics['interestEarned'] ?? 0, 0) }} /=</td>
                </tr>
            </table>
        </div>
        <div>
            <h3>Delinquency Metrics</h3>
            @if(isset($loanStatistics['delinquencyMetrics']))
            <table>
                <tr>
                    <th>Overdue Loans</th>
                    <td>{{ $loanStatistics['delinquencyMetrics']['overdueLoans'] }}</td>
                </tr>
                <tr>
                    <th>Delinquency Rate</th>
                    <td>{{ number_format($loanStatistics['delinquencyMetrics']['delinquencyRate'], 0) }}%</td>
                </tr>
                <tr>
                    <th>Aging 30 Days</th>
                    <td>{{ $loanStatistics['delinquencyMetrics']['aging30Days'] }}</td>
                </tr>
                <tr>
                    <th>Aging 60 Days</th>
                    <td>{{ $loanStatistics['delinquencyMetrics']['aging60Days'] }}</td>
                </tr>
                <tr>
                    <th>Aging 90 Days</th>
                    <td>{{ $loanStatistics['delinquencyMetrics']['aging90Days'] }}</td>
                </tr>
            </table>
            @else
            <p>No delinquency metrics available for the selected period.</p>
            @endif
        </div>
    </div>

    <!-- Key Highlights Section -->
    <div class="section">
        <h2>Key Highlights</h2>
        <div>
            <h3>Overview</h3>
            <table>
                <tr>
                    <th>New Clients Onboarded</th>
                    <td>{{ $keyHighlights['newClientsCount'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Total Repayments Received</th>
                    <td>{{ number_format($keyHighlights['totalRepayments'] ?? 0, 0) }} /=</td>
                </tr>
                <tr>
                    <th>Current Delinquency Rate</th>
                    <td>{{ number_format($keyHighlights['currentDelinquencyRate'] ?? 0, 0) }}%</td>
                </tr>
            </table>
        </div>

        <!-- Top Performing Agents -->
        <div>
            <h3>Top Performing Agents</h3>
            @if(!empty($keyHighlights['topAgents']) && count($keyHighlights['topAgents']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Agent Name</th>
                        <th>Total Collected (UGX)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($keyHighlights['topAgents'] as $agent)
                    <tr>
                        <td>{{ $agent->f_name }} {{ $agent->l_name }}</td>
                        <td>{{ number_format($agent->payments->sum('amount'), 0) }} /=</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No top performing agent data available for the selected period.</p>
            @endif
        </div>
    </div>

    <!-- Client Report Section -->
    <div class="section">
        <h2>Client Report</h2>
        <div>
            <h3>Client Metrics</h3>
            <table>
                <tr>
                    <th>Total Clients</th>
                    <td>{{ $clientReportData['totalClients'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Clients with Balance</th>
                    <td>{{ $clientReportData['clientsWithBalance'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Total Credit Balance</th>
                    <td>{{ number_format($clientReportData['totalCreditBalance'] ?? 0, 0) }} /=</td>
                </tr>
                <tr>
                    <th>Clients Paid</th>
                    <td>{{ $clientReportData['clientsPaid'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Clients Unpaid</th>
                    <td>{{ $clientReportData['clientsUnpaid'] ?? 0 }}</td>
                </tr>
                <tr>
                    <th>Clients Paid in Advance</th>
                    <td>{{ $clientReportData['clientsPaidAdvance'] ?? 0 }}</td>
                </tr>
            </table>
        </div>

       
    </div>

    <div class="footer">
        <p>Generated by Sanaa | {{ \Carbon\Carbon::now()->format('F d, Y') }}</p>
    </div>

</body>
</html>
