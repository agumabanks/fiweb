@extends('layouts.admin.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid p-5">
    <h1 class="text-center mb-5">Admin Dashboard</h1>

    <!-- KPI Summary -->
    <div class="row text-center mb-4">
        <!-- Total Loans Disbursed -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <h5 class="text-uppercase text-white">Total Loans Disbursed</h5>
                    <h3 class=" text-white">UGX {{ number_format($totalLoansDisbursed, 0) }}</h3>
                </div>
            </div>
        </div>
        <!-- Total Repayments -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h5 class="text-uppercase">Total Repayments</h5>
                    <h3>UGX {{ number_format($totalRepayments, 0) }}</h3>
                </div>
            </div>
        </div>
        <!-- Total Overdue Loans -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <h5 class="text-uppercase">Total Overdue Loans</h5>
                    <h3>UGX {{ number_format($totalOverdueLoans, 0) }}</h3>
                </div>
            </div>
        </div>
        <!-- New Clients Today -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <h5 class="text-uppercase">New Clients Today</h5>
                    <h3>{{ $newClientsToday }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Information -->
    <div class="row text-center mb-4">
        <!-- Total Balance -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <h5 class="text-uppercase">Total Balance</h5>
                    <h3>UGX {{ number_format($balance['total_balance'], 0) }}</h3>
                </div>
            </div>
        </div>
        <!-- Used Balance -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-dark ">
                <div class="card-body text-white">
                    <h5 class="text-uppercase text-white">Used Balance</h5>
                    <h3 class=" text-white">UGX {{ number_format($balance['used_balance'], 0) }}</h3>
                </div>
            </div>
        </div>
        <!-- Unused Balance -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-light text-dark">
                <div class="card-body">
                    <h5 class="text-uppercase">Unused Balance</h5>
                    <h3>UGX {{ number_format($balance['unused_balance'], 0) }}</h3>
                </div>
            </div>
        </div>
        <!-- Total Earned -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <h5 class="text-uppercase">Total Earned</h5>
                    <h3>UGX {{ number_format($balance['total_earned'], 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Monthly Loan Data Chart -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5>Monthly Loan Data</h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyLoanChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Loan Aging Analysis Chart -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5>Loan Aging Analysis</h5>
                </div>
                <div class="card-body">
                    <canvas id="loanAgingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performing Agents and Customers -->
    <div class="row mb-4">
        <!-- Top Agents -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5>Top Performing Agents</h5>
                </div>
                <div class="card-body">
                    <canvas id="topAgentsChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Top Customers -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <h5>Top Customers</h5>
                </div>
                <div class="card-body">
                    <canvas id="topCustomersChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Agent Loan Collections -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5>Agent Loan Collections</h5>
        </div>
        <div class="card-body">
            @if($agentLoanCollections->count() > 0)
            <table class="table table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>Agent Name</th>
                        <th>Total Loan Amount (UGX)</th>
                        <th>Expected Daily Collection (UGX)</th>
                        <th>Total Collected (UGX)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agentLoanCollections as $collection)
                    <tr>
                        <td>{{ $collection->f_name }} {{ $collection->l_name }}</td>
                        <td>{{ number_format($collection->total_loan_amount, 0) }}</td>
                        <td>{{ number_format($collection->expected_daily_collection, 0) }}</td>
                        <td>{{ number_format($collection->total_collected, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No agent loan collection data available.</p>
            @endif
        </div>
    </div>

    <!-- Top Transactions -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5>Top Transactions</h5>
        </div>
        <div class="card-body">
            @if($topTransactions->count() > 0)
            <table class="table table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>Transaction ID</th>
                        <th>Client Name</th>
                        <th>Amount (UGX)</th>
                        <th>Payment Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->id }}</td>
                        <td>{{ $transaction->client->name ?? 'N/A' }}</td>
                        <td>{{ number_format($transaction->amount, 0) }}</td>
                        <td>{{ \Carbon\Carbon::parse($transaction->payment_date)->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p>No top transactions available.</p>
            @endif
        </div>
    </div>
    
    <!-- In your navigation bar or header -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <!-- Other nav items -->
        <ul class="navbar-nav ml-auto">
            <!-- Logout Button -->
            <li class="nav-item">
                <a href="#" class="nav-link admin-logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

</div>

<!-- Hidden Logout Form -->
<form id="logout-form" action="{{ route('admin.auth.logout') }}" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('script_2')


<!-- Your custom script -->
<script>
    $(document).ready(function () {
        $('.admin-logout-btn').on('click', function (e) {
            e.preventDefault();
            logOut();
        });

        function logOut(){
            Swal.fire({
                title: '{{ __('Do you want to logout?') }}',
                showDenyButton: true,
                confirmButtonColor: '#014F5B',
                cancelButtonColor: '#363636',
                confirmButtonText: 'Yes',
                denyButtonText: "Don't Logout",
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit the logout form
                    document.getElementById('logout-form').submit();
                } else if (result.isDenied) {
                    Swal.fire('Canceled', '', 'info');
                }
            });
        }
    });
</script>

<!-- Include Chart.js library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Monthly Loan Data Chart
    var monthlyLoanCtx = document.getElementById('monthlyLoanChart').getContext('2d');
    var monthlyLoanChart = new Chart(monthlyLoanCtx, {
        type: 'line',
        data: {
            labels: [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ],
            datasets: [
                {
                    label: 'Loans Disbursed',
                    data: {!! json_encode($monthlyLoanData['disbursed']) !!},
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    fill: false,
                },
                {
                    label: 'Repayments',
                    data: {!! json_encode($monthlyLoanData['repaid']) !!},
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    fill: false,
                }
            ]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Monthly Loan Data'
            }
        }
    });

    // Loan Aging Analysis Chart
    var loanAgingCtx = document.getElementById('loanAgingChart').getContext('2d');
    var loanAgingChart = new Chart(loanAgingCtx, {
        type: 'pie',
        data: {
            labels: ['30 Days Overdue', '60 Days Overdue', '90+ Days Overdue'],
            datasets: [{
                data: [
                    {{ $loanAging['30_days'] }},
                    {{ $loanAging['60_days'] }},
                    {{ $loanAging['90_days'] }}
                ],
                backgroundColor: [
                    'rgba(255, 193, 7, 0.6)',
                    'rgba(255, 87, 34, 0.6)',
                    'rgba(220, 53, 69, 0.6)'
                ],
                borderColor: [
                    'rgba(255, 193, 7, 1)',
                    'rgba(255, 87, 34, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Loan Aging Analysis'
            }
        }
    });

    // Top Performing Agents Chart
    var topAgentsCtx = document.getElementById('topAgentsChart').getContext('2d');
    var topAgentsChart = new Chart(topAgentsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topAgents->pluck('f_name')->map(function($name, $key) use ($topAgents) {
                return $name . ' ' . $topAgents[$key]->l_name;
            })) !!},
            datasets: [{
                label: 'Total Disbursed (UGX)',
                data: {!! json_encode($topAgents->pluck('total_disbursed')) !!},
                backgroundColor: 'rgba(0, 123, 255, 0.6)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Top Performing Agents'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    // Top Customers Chart
    var topCustomersCtx = document.getElementById('topCustomersChart').getContext('2d');
    var topCustomersChart = new Chart(topCustomersCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($topCustomers->pluck('name')) !!},
            datasets: [{
                label: 'Total Repaid (UGX)',
                data: {!! json_encode($topCustomers->pluck('total_repaid')) !!},
                backgroundColor: 'rgba(40, 167, 69, 0.6)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            title: {
                display: true,
                text: 'Top Customers'
            },
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
</script>
@endpush
