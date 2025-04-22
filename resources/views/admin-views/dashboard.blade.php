@extends('layouts.admin.app')

@section('title', 'Admin Dashboard')

@section('content')

 

<!-- Refined Dashboard Header -->
<div class="bg-dark text-white mb-4 py-4">
    <div class="container-fluid px-4">
        <div class="row align-items-center gx-4">
            <div class="col-lg-8 col-md-7">
                <div class="d-flex align-items-center">
                    
                    <div>
                         <h1 class="fw-light text-white mb-2">{{ \Carbon\Carbon::now()->format('F d, Y') }}</h1>
                        <p class="fs-6 mb-1">Welcome back, <span class="fw-semibold">{{ auth('user')->user()->f_name }}</span>. Here's what's happening today.</p>
                        <div class="d-flex align-items-center gap-3 mt-2">
                            <!-- Dynamic System Status Indicator -->
                                <div class="d-inline-block me-3 ">
                                    <div class="system-status-indicator" id="systemStatusIndicator">
                                        <span class="status-badge online">
                                            <i class="tio-checkmark-circle me-1"></i> System Online 
                                        </span>
                                        <span class="status-badge offline" style="display: none;">
                                            <i class="tio-warning-outlined me-1"></i> System Offline
                                        </span>
                                    </div>
                                </div>
                            <div class="badge   bg-opacity-25 text-white py-1 px-2">
                                <!-- <i class="tio-time me-1"></i> {{ \Carbon\Carbon::now()->format('h:i A') }} -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-5 text-end mt-4 mt-md-0">
                <div class="d-flex justify-content-end align-items-center">
                    <div class="dropdown header-action-dropdown me-3">
                        <button type="button" class="btn btn-outline-light btn-action-glow dropdown-toggle" id="quickActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="tio-settings me-1"></i> Quick Actions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="quickActionsDropdown">
                            <li><h6 class="dropdown-header">Client Management</h6></li>
                            <li>
                                <a class="dropdown-item quick-action-item" href="{{ route('admin.client.create') }}">
                                    <i class="tio-user-add action-icon"></i>
                                    <div>
                                        <span class="action-title">Add New Client</span>
                                        <span class="action-desc">Register a new client in the system</span>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item quick-action-item" href="#" id="openSmsModal">
                                    <i class="tio-messages action-icon"></i>
                                    <div>
                                        <span class="action-title">Send Bulk SMS</span>
                                        <span class="action-desc">Message multiple clients at once</span>
                                    </div>
                                </a>
                            </li>
                            <li><h6 class="dropdown-header mt-3">Other Actions</h6></li>
                            <li>
                                <a class="dropdown-item quick-action-item" href="{{ route('admin.expense.expenses') }}">
                                    <i class="tio-money-vs action-icon"></i>
                                    <div>
                                        <span class="action-title">Create New Expense</span>
                                        <span class="action-desc">Quicly enter new expense</span>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <a class="dropdown-item quick-action-item" href="{{ route('admin.report.index') }}">
                                    <i class="tio-file-text action-icon"></i>
                                    <div>
                                        <span class="action-title">Generate Daily Report</span>
                                        <span class="action-desc">Create summary report for today</span>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-primary btn-refresh-glow ml-2" id="refreshDashboard">
                        <i class="tio-refresh me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
    
<div class="container-fluid p-0">
    <!-- Steve Jobs Inspired Header Section -->
    <!-- <div class="bg-dark text-white mb-4 py-4">
        <div class="container-fluid px-4">
            <div class="row align-items-center">
                <div class="col-8">
                    <h1 class="fw-light text-white">{{ \Carbon\Carbon::now()->format('F d, Y h:i A') }}</h1>
                    <p class="fs-6 mb-0">Welcome back, {{ auth('user')->user()->f_name }}. Here's what's happening today.</p>
                </div>
                <div class="col-4 text-end">
                    <div class="d-inline-block me-3">
                        <div class="dropdown">
                            <button type="button" class="btn btn-outline-light dropdown-toggle" id="quickActionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="tio-settings"></i> Quick Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionsDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.client.create') }}">Add New Client</a></li>
                                <li><a class="dropdown-item" href="#" id="openSmsModal">Send Bulk SMS</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.loans.updateClientLoan', 1) }}">Create New Loan</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="{{ route('admin.daily-report') }}">Generate Daily Report</a></li>
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary" id="refreshDashboard">
                        <i class="tio-refresh"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div> -->

    

    <!-- Main Content Area with Metrics -->
    <div class="container-fluid px-4">
        <!-- Top KPI Cards in Minimalist Style -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-gradient-primary text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-uppercase fw-normal mb-0">Total Loans</h6>
                            <div class="icon-bg rounded-circle p-2 bg-opacity-25 bg-white">
                                <i class="tio-money-vs"></i>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($totalLoansDisbursed, 0) }}</h3>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 70%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-gradient-success text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-uppercase fw-normal mb-0">Repayments</h6>
                            <div class="icon-bg rounded-circle p-2 bg-opacity-25 bg-white">
                                <i class="tio-checkmark-circle"></i>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($totalRepayments, 0) }}</h3>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-gradient-warning text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-uppercase fw-normal mb-0">Overdue Loans</h6>
                            <div class="icon-bg rounded-circle p-2 bg-opacity-25 bg-white">
                                <i class="tio-warning-outlined"></i>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ number_format($totalOverdueLoans, 0) }}</h3>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 40%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100 bg-gradient-info text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-uppercase fw-normal mb-0">New Clients</h6>
                            <div class="icon-bg rounded-circle p-2 bg-opacity-25 bg-white">
                                <i class="tio-person-add"></i>
                            </div>
                        </div>
                        <h3 class="mb-0">{{ $newClientsToday }}</h3>
                        <div class="progress mt-2" style="height: 4px;">
                            <div class="progress-bar bg-white" style="width: 55%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Income vs Expenses Section -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Income vs Expenses</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="incomeTimeRange" data-bs-toggle="dropdown" aria-expanded="false">
                                Last 30 Days
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="incomeTimeRange">
                                <li><a class="dropdown-item" href="#">Last Week</a></li>
                                <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                                <li><a class="dropdown-item" href="#">Last Quarter</a></li>
                                <li><a class="dropdown-item" href="#">This Year</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <canvas id="incomeExpensesChart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Financial Overview</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Balance</span>
                                <span class="fw-bold">{{ number_format($balance['total_balance'], 0) }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-primary" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Used Balance</span>
                                <span class="fw-bold">{{ number_format($balance['used_balance'], 0) }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($balance['used_balance'] / $balance['total_balance']) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Unused Balance</span>
                                <span class="fw-bold">{{ number_format($balance['unused_balance'], 0) }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: {{ ($balance['unused_balance'] / $balance['total_balance']) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">Total Earned</span>
                                <span class="fw-bold">{{ number_format($balance['total_earned'], 0) }}</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: {{ ($balance['total_earned'] / $balance['total_balance']) * 100 }}%"></div>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.expense.expenses') }}" class="btn btn-sm btn-primary">Manage Finances</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Loan Performance & Client Management -->
        <div class="row g-3 mb-4">
            <!-- Loan Performance Chart -->
            <div class="col-md-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Monthly Loan Performance</h5>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showCollectionRates" checked>
                            <label class="form-check-label" for="showCollectionRates">Show Collection Rates</label>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <canvas id="monthlyLoanChart" height="260"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Top Performers -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Top Performing Agents</h5>
                        <button class="btn btn-sm btn-outline-primary">View All</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Agent</th>
                                        <th>Total Disbursed</th>
                                        <th>Collection Rate</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topAgents->take(5) as $agent)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2 bg-soft-primary text-primary rounded">
                                                    {{ substr($agent->f_name, 0, 1) }}{{ substr($agent->l_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $agent->f_name }} {{ $agent->l_name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ number_format($agent->total_disbursed, 0) }}</td>
                                        <td>
                                            @php
                                                $performance = isset($agent->performance_percentage) ? $agent->performance_percentage : rand(70, 95);
                                                $colorClass = $performance > 90 ? 'success' : ($performance > 70 ? 'info' : 'warning');
                                            @endphp
                                            <div class="progress" style="height: 5px; width: 80px">
                                                <div class="progress-bar bg-{{ $colorClass }}" role="progressbar" style="width: {{ $performance }}%"></div>
                                            </div>
                                            <small>{{ number_format($performance, 1) }}%</small>
                                        </td>
                                        <td>
                                            @if($performance >= 90)
                                                <span class="badge bg-soft-success text-success">Excellent</span>
                                            @elseif($performance >= 70)
                                                <span class="badge bg-soft-info text-info">Good</span>
                                            @else
                                                <span class="badge bg-soft-warning text-warning">Average</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Risk Overview and Loan Aging -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0">Loan Aging Analysis</h5>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-3 text-center">
                            <div id="loanAgingGauge"></div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <i class="fas fa-circle text-warning me-1" style="font-size: 8px;"></i>
                                    <span>30 Days Overdue</span>
                                </div>
                                <span class="badge bg-soft-warning text-warning">{{ $loanAging['30_days'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <i class="fas fa-circle text-orange me-1" style="font-size: 8px;"></i>
                                    <span>60 Days Overdue</span>
                                </div>
                                <span class="badge bg-soft-danger text-danger">{{ $loanAging['60_days'] }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-circle text-danger me-1" style="font-size: 8px;"></i>
                                    <span>90+ Days Overdue</span>
                                </div>
                                <span class="badge bg-danger">{{ $loanAging['90_days'] }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <a href="{{ route('admin.loan-arrears.index') }}" class="btn btn-sm btn-outline-danger">View At-Risk Loans</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Transactions</h5>
                        <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Transaction ID</th>
                                        <th>Client</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($topTransactions) > 0)
                                        @foreach($topTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <span class="text-primary">#{{ substr($transaction->id, 0, 8) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2 bg-soft-info text-info rounded">
                                                        {{ substr($transaction->client->name ?? 'NA', 0, 1) }}
                                                    </div>
                                                    <div>{{ $transaction->client->name ?? 'N/A' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-medium">{{ number_format($transaction->amount, 0) }}</span>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($transaction->payment_date)->format('d M Y') }}
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-success text-success">Completed</span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center py-3">No transactions found</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enhanced SMS Messaging System -->
        <div class="row g-3 mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Quick Client Messaging</h5>
                        <a href="{{ route('admin.sms.dashboard') }}" class="btn btn-sm btn-outline-primary">
                            <i class="tio-dashboard-outlined me-1"></i> SMS Dashboard
                        </a>
                    </div>
                    <div class="card-body p-3">
                        <form id="smsForm" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Clients</label>
                                <select class="form-control select2-ajax" id="clientSelect" multiple>
                                    <!-- Options will be loaded via AJAX -->
                                </select>
                                <div class="form-text">Search by name, phone or ID</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Or Select Group</label>
                                <div class="d-flex">
                                    <select class="form-control me-2" id="clientGroupSelect">
                                        <option value="">Select a group</option>
                                        <option value="all">All Clients</option>
                                        <option value="active">Active Clients</option>
                                        <option value="overdue">Overdue Clients</option>
                                        <option value="new">New Clients (Last 7 days)</option>
                                    </select>
                                    <button type="button" id="selectGroupBtn" class="btn btn-outline-primary">Select</button>
                                </div>
                            </div>
                            
                            <!-- Client Details Section (will be shown when a client is selected) -->
                            <div class="col-md-12 mt-2" id="clientDetailsSection" style="display: none;">
                                <div class="bg-light p-3 rounded">
                                    <div class="row">
                                        <div class="col-md-6 border-end">
                                            <h6 class="mb-2">Client Information</h6>
                                            <div class="d-flex mb-2">
                                                <div class="avatar-md me-3 bg-soft-primary text-primary rounded-circle d-flex align-items-center justify-content-center" id="clientInitials"></div>
                                                <div>
                                                    <h5 class="mb-0" id="clientName"></h5>
                                                    <p class="mb-0 text-muted" id="clientPhone"></p>
                                                    <p class="mb-0 small text-muted" id="clientEmail"></p>
                                                </div>
                                            </div>
                                            <div id="clientAddress" class="small text-muted mb-2"></div>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-2">Loan Information</h6>
                                            <div id="noLoanInfo" style="display: none;">
                                                <p class="text-muted">No active loans</p>
                                            </div>
                                            <div id="loanInfo" style="display: none;">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Loan Status:</span>
                                                    <span id="loanStatus" class="badge bg-soft-success text-success"></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Total Due:</span>
                                                    <span id="totalDue" class="fw-bold"></span>
                                                </div>
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span>Next Payment:</span>
                                                    <span id="nextDueDate"></span>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Amount Due:</span>
                                                    <span id="nextDueAmount" class="fw-bold"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2" id="smsHistorySection" style="display: none;">
                                        <div class="col-12">
                                            <h6 class="mb-2 border-top pt-2">Recent SMS History</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Date</th>
                                                            <th>Message</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="smsHistoryTable">
                                                        <!-- SMS history will be loaded here -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label mb-0">Message</label>
                                    <div>
                                        <select class="form-select form-select-sm" id="templateSelect">
                                            <option value="">Load Template</option>
                                        </select>
                                    </div>
                                </div>
                                <textarea class="form-control" id="smsContent" rows="3" placeholder="Type your message here..."></textarea>
                                <div class="d-flex justify-content-between mt-1">
                                    <div class="form-text" id="smsCounter">0/160 characters</div>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" id="insertNameBtn">Insert Name</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" id="insertLoanBtn">Insert Loan Amount</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary me-1" id="insertDateBtn">Insert Due Date</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" id="insertAmountBtn">Insert Due Amount</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-light me-2" id="saveTemplateBtn">Save as Template</button>
                                <button type="submit" class="btn btn-primary" id="sendSmsBtn">
                                    <i class="tio-send me-1"></i> Send Message
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SMS Template Modal -->
<div class="modal fade" id="smsTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save SMS Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Template Name</label>
                    <input type="text" class="form-control" id="templateName" placeholder="Enter a name for this template">
                </div>
                <div class="mb-3">
                    <label class="form-label">Template Content</label>
                    <textarea class="form-control" id="templateContent" rows="4" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveTemplateConfirmBtn">Save Template</button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden Logout Form -->
<form id="logout-form" action="{{ route('admin.auth.logout') }}" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('script_2')
<!-- Include Chart.js and ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Dashboard Scripts -->
<script>
    $(document).ready(function () {
        // Setup logout button handling
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
                    document.getElementById('logout-form').submit();
                } else if (result.isDenied) {
                    Swal.fire('Canceled', '', 'info');
                }
            });
        }
        
        // Initialize Charts
        initIncomeExpensesChart();
        initMonthlyLoanChart();
        initLoanAgingGauge();
        
        // Initialize SMS features
        initSmsFeatures();
        
        // Dashboard refresh button
        $('#refreshDashboard').click(function() {
            location.reload();
        });
    });
    
    // Income vs Expenses Chart
    function initIncomeExpensesChart() {
        const ctx = document.getElementById('incomeExpensesChart').getContext('2d');
        
        // Example data - you would typically get this from the server
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const income = [
            {{ implode(', ', array_map(function($val) { return $val/1000; }, $monthlyLoanData['repaid'])) }}
        ];
        const expenses = [
            6500, 5900, 8000, 8100, 5600, 5500, 4000, 4800, 5800, 6900, 7900, 8900
        ].map(val => val/1000); // Convert to thousands for better display
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Income (thousands)',
                    data: income,
                    backgroundColor: 'rgba(52, 152, 219, 0.6)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }, {
                    label: 'Expenses (thousands)',
                    data: expenses,
                    backgroundColor: 'rgba(231, 76, 60, 0.6)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (thousands)'
                        }
                    }
                }
            }
        });
    }
    
    // Monthly Loan Chart
    function initMonthlyLoanChart() {
        const ctx = document.getElementById('monthlyLoanChart').getContext('2d');
        
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const disbursed = {!! json_encode($monthlyLoanData['disbursed'] ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!};
        const repaid = {!! json_encode($monthlyLoanData['repaid'] ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!};
        
        // Calculate collection rates
        const collectionRates = [];
        for (let i = 0; i < disbursed.length; i++) {
            collectionRates.push(disbursed[i] > 0 ? (repaid[i] / disbursed[i] * 100) : 0);
        }
        
        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Loans Disbursed',
                    data: disbursed,
                    backgroundColor: 'rgba(52, 152, 219, 0.6)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1,
                    order: 2
                }, {
                    label: 'Repayments',
                    data: repaid,
                    backgroundColor: 'rgba(46, 204, 113, 0.6)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1,
                    order: 2
                }, {
                    label: 'Collection Rate (%)',
                    data: collectionRates,
                    type: 'line',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'y1',
                    order: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount'
                        }
                    },
                    y1: {
                        position: 'right',
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Collection Rate (%)'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
        
        // Toggle collection rates visibility
        $('#showCollectionRates').on('change', function() {
            const isVisible = $(this).prop('checked');
            chart.data.datasets[2].hidden = !isVisible;
            chart.update();
        });
    }
    
    // Loan Aging Gauge
    function initLoanAgingGauge() {
        const totalOverdue = {{ $loanAging['total_overdue'] ?? ($loanAging['30_days'] + $loanAging['60_days'] + $loanAging['90_days']) }};
        const riskPercentage = {{ $loanAging['overdue_percentage'] ?? 0 }};
            
        const options = {
            chart: {
                height: 200,
                type: 'radialBar',
            },
            series: [Math.round(riskPercentage)],
            colors: [riskPercentage > 70 ? '#e74c3c' : (riskPercentage > 40 ? '#f39c12' : '#2ecc71')],
            plotOptions: {
                radialBar: {
                    hollow: {
                        margin: 15,
                        size: '60%'
                    },
                    dataLabels: {
                        name: {
                            fontSize: '14px',
                            color: '#626262',
                            offsetY: 20
                        },
                        value: {
                            fontSize: '22px',
                            color: '#333',
                            formatter: function (val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: 'horizontal',
                    shadeIntensity: 0.1,
                    gradientToColors: [riskPercentage > 70 ? '#c0392b' : (riskPercentage > 40 ? '#e67e22' : '#27ae60')],
                    inverseColors: true,
                    opacityFrom: 1,
                    opacityTo: 1,
                    stops: [0, 100]
                }
            },
            stroke: {
                lineCap: 'round'
            },
            labels: ['Risk Score'],
        };

        const chart = new ApexCharts(document.querySelector("#loanAgingGauge"), options);
        chart.render();
    }
    
    // Enhanced SMS Features
    function initSmsFeatures() {
        // Initialize Select2 for client selection with AJAX
        $('#clientSelect').select2({
            placeholder: 'Search for clients',
            minimumInputLength: 2,
            ajax: {
                url: '{{ route("admin.clients.search") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.clients.map(function(client) {
                            return {
                                id: client.id,
                                text: client.name + ' - ' + client.phone
                            };
                        })
                    };
                },
                cache: true
            }
        });
        
        // Load templates from server
        $.ajax({
            url: '{{ route("admin.sms.get-templates") }}',
            type: 'GET',
            data: { type: 'all' },
            success: function(data) {
                if (data && data.length) {
                    const $select = $('#templateSelect');
                    $select.empty().append('<option value="">Load Template</option>');
                    
                    data.forEach(function(template) {
                        $select.append(`<option value="${template.id}" data-content="${template.content}">${template.name}</option>`);
                    });
                }
            }
        });
        
        // Handle template selection
        $('#templateSelect').on('change', function() {
            const $option = $(this).find('option:selected');
            const content = $option.data('content');
            
            if (content) {
                $('#smsContent').val(content).trigger('input');
            }
        });
        
        // Client selection event to fetch details
        $('#clientSelect').on('select2:select', function(e) {
            const clientId = e.params.data.id;
            
            // Only fetch details if only one client is selected
            if ($('#clientSelect').val().length === 1) {
                fetchAndDisplayClientDetails(clientId);
            } else {
                // Hide details if multiple clients are selected
                $('#clientDetailsSection').hide();
            }
        });
        
        // Handle client unselect
        $('#clientSelect').on('select2:unselect', function(e) {
            if ($('#clientSelect').val() && $('#clientSelect').val().length === 1) {
                // If only one client remains, show that client's details
                const remainingClientId = $('#clientSelect').val()[0];
                fetchAndDisplayClientDetails(remainingClientId);
            } else {
                // Hide details if no clients or multiple clients are selected
                $('#clientDetailsSection').hide();
            }
        });
        
        // Function to fetch and display client details
        function fetchAndDisplayClientDetails(clientId) {
            $.ajax({
                url: '{{ route("admin.sms.get-client-details") }}',
                type: 'GET',
                data: { client_id: clientId },
                beforeSend: function() {
                    // Show loading indicator or disable interface
                    $('#clientDetailsSection').html('<div class="text-center p-3"><i class="tio-loading spin"></i> Loading client details...</div>').show();
                },
                success: function(response) {
                    // Set client information
                    $('#clientName').text(response.client.name);
                    $('#clientPhone').text(response.client.phone);
                    $('#clientEmail').text(response.client.email || 'No email');
                    $('#clientAddress').text(response.client.address || 'No address');
                    
                    // Set client initials
                    const nameParts = response.client.name.split(' ');
                    const initials = nameParts.length > 1 
                        ? nameParts[0].charAt(0) + nameParts[1].charAt(0) 
                        : nameParts[0].charAt(0);
                    $('#clientInitials').text(initials);
                    
                    // Set loan information if available
                    if (response.loan.status === 'none') {
                        $('#noLoanInfo').show();
                        $('#loanInfo').hide();
                    } else {
                        $('#loanInfo').show();
                        $('#noLoanInfo').hide();
                        
                        // Set status with appropriate styling
                        const status = response.loan.status;
                        let statusClass = 'bg-soft-success text-success';
                        
                        if (status === 'overdue') {
                            statusClass = 'bg-soft-danger text-danger';
                        } else if (status === 'pending') {
                            statusClass = 'bg-soft-warning text-warning';
                        }
                        
                        $('#loanStatus').text(status.charAt(0).toUpperCase() + status.slice(1))
                            .removeClass()
                            .addClass('badge ' + statusClass);
                        
                        // Set other loan details
                        $('#totalDue').text(formatCurrency(response.loan.total_due));
                        $('#nextDueDate').text(response.loan.next_due_date || 'No upcoming payment');
                        $('#nextDueAmount').text(formatCurrency(response.loan.next_due_amount));
                    }
                    
                    // Set SMS history
                    const $historyTable = $('#smsHistoryTable');
                    $historyTable.empty();
                    
                    if (response.sms_history && response.sms_history.length) {
                        response.sms_history.forEach(function(sms) {
                            let statusBadge = '';
                            if (sms.status === 'success') {
                                statusBadge = '<span class="badge bg-soft-success text-success">Delivered</span>';
                            } else if (sms.status === 'error') {
                                statusBadge = '<span class="badge bg-soft-danger text-danger">Failed</span>';
                            } else {
                                statusBadge = '<span class="badge bg-soft-warning text-warning">Pending</span>';
                            }
                            
                            $historyTable.append(`
                                <tr>
                                    <td><small>${sms.sent_at}</small></td>
                                    <td><small>${truncateText(sms.message, 50)}</small></td>
                                    <td>${statusBadge}</td>
                                </tr>
                            `);
                        });
                        $('#smsHistorySection').show();
                    } else {
                        $historyTable.append('<tr><td colspan="3" class="text-center">No SMS history found</td></tr>');
                        $('#smsHistorySection').show();
                    }
                    
                    // Show the entire section
                    $('#clientDetailsSection').show();
                },
                error: function(xhr) {
                    console.error('Error fetching client details:', xhr);
                    $('#clientDetailsSection').html(`
                        <div class="alert alert-danger m-3">
                            <i class="tio-error-outlined me-2"></i>
                            Failed to load client details. Please try again.
                        </div>
                    `).show();
                }
            });
        }
        
        // Helper function to format currency
        function formatCurrency(amount) {
            if (!amount) return '0';
            return new Intl.NumberFormat('en-KE', { 
                style: 'currency',
                currency: 'KES',
                minimumFractionDigits: 0
            }).format(amount);
        }
        
        // Helper function to truncate text
        function truncateText(text, maxLength) {
            if (!text) return '';
            if (text.length <= maxLength) return text;
            return text.substr(0, maxLength) + '...';
        }
        
        // SMS character counter
        $('#smsContent').on('input', function() {
            const length = $(this).val().length;
            const smsCount = Math.ceil(length / 160);
            $('#smsCounter').text(length + '/160 characters' + (smsCount > 1 ? ' (' + smsCount + ' SMS)' : ''));
            
            if (length > 160) {
                $('#smsCounter').addClass('text-warning');
            } else {
                $('#smsCounter').removeClass('text-warning text-danger');
            }
            
            if (length > 320) {
                $('#smsCounter').removeClass('text-warning').addClass('text-danger');
            }
        });
        
        // Insert template variables
        $('#insertNameBtn').click(function() {
            insertAtCursor('#smsContent', '{client_name}');
        });
        
        $('#insertLoanBtn').click(function() {
            insertAtCursor('#smsContent', '{loan_amount}');
        });
        
        $('#insertDateBtn').click(function() {
            insertAtCursor('#smsContent', '{due_date}');
        });
        
        $('#insertAmountBtn').click(function() {
            insertAtCursor('#smsContent', '{due_amount}');
        });
        
        // Save template button
        $('#saveTemplateBtn').click(function() {
            const content = $('#smsContent').val();
            if (!content) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please write a message first',
                    icon: 'error',
                    confirmButtonColor: '#014F5B'
                });
                return;
            }
            
            $('#templateContent').val(content);
            $('#smsTemplateModal').modal('show');
        });
        
        // Save template confirmation
        $('#saveTemplateConfirmBtn').click(function() {
            const name = $('#templateName').val();
            const content = $('#templateContent').val();
            
            if (!name) {
                alert('Please enter a template name');
                return;
            }
            
            // Save the template to the server
            $.ajax({
                url: '{{ route("admin.sms.save-template") }}',
                type: 'POST',
                data: {
                    name: name,
                    content: content,
                    type: 'general',
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#smsTemplateModal').modal('hide');
                    
                    // Add the new template to the dropdown
                    $('#templateSelect').append(`
                        <option value="${response.template.id}" data-content="${response.template.content}">${response.template.name}</option>
                    `);
                    
                    Swal.fire({
                        title: 'Success!',
                        text: 'Your template has been saved.',
                        icon: 'success',
                        confirmButtonColor: '#014F5B'
                    });
                },
                error: function(xhr) {
                    console.error('Error saving template:', xhr);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to save template. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#014F5B'
                    });
                }
            });
        });
        
        // Select client group button
        $('#selectGroupBtn').click(function() {
            const group = $('#clientGroupSelect').val();
            if (!group) {
                alert('Please select a client group');
                return;
            }
            
            // Show loading indicator
            Swal.fire({
                title: 'Loading Clients',
                text: 'Please wait while we fetch the client list...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Fetch clients based on the selected group
            $.ajax({
                url: '{{ route("admin.clients.search") }}',
                type: 'GET',
                data: { group: group },
                success: function(response) {
                    if (response.clients && response.clients.length) {
                        // Clear existing selection
                        $('#clientSelect').val(null).trigger('change');
                        
                        // Add each client to the selection
                        response.clients.forEach(function(client) {
                            const option = new Option(client.name + ' - ' + client.phone, client.id, true, true);
                            $('#clientSelect').append(option);
                        });
                        
                        $('#clientSelect').trigger('change');
                        
                        Swal.fire({
                            title: 'Clients Selected',
                            text: 'Selected ' + response.clients.length + ' clients for messaging.',
                            icon: 'success',
                            confirmButtonColor: '#014F5B'
                        });
                    } else {
                        Swal.fire({
                            title: 'No Clients Found',
                            text: 'No clients found in the selected group.',
                            icon: 'info',
                            confirmButtonColor: '#014F5B'
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching clients:', xhr);
                    Swal.fire({
                        title: 'Error',
                        text: 'Failed to fetch clients. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#014F5B'
                    });
                }
            });
        });
        
        // Send SMS form submission
        $('#smsForm').on('submit', function(e) {
            e.preventDefault();
            
            const clientIds = $('#clientSelect').val();
            const message = $('#smsContent').val();
            
            if (!clientIds || !clientIds.length) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please select at least one client',
                    icon: 'error',
                    confirmButtonColor: '#014F5B'
                });
                return;
            }
            
            if (!message) {
                Swal.fire({
                    title: 'Error',
                    text: 'Please enter a message',
                    icon: 'error',
                    confirmButtonColor: '#014F5B'
                });
                return;
            }
            
            // Confirm sending SMS
            Swal.fire({
                title: 'Send SMS?',
                text: 'You are about to send SMS to ' + clientIds.length + ' clients. Continue?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#014F5B',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, send it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading indicator
                    const loadingAlert = Swal.fire({
                        title: 'Sending Messages',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Send the SMS via AJAX
                    $.ajax({
                        url: '{{ route("admin.sms.send") }}',
                        type: 'POST',
                        data: {
                            client_ids: clientIds,
                            message: message,
                            message_type: 'notification',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            loadingAlert.close();
                            
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#014F5B'
                            });
                            
                            // Reset form
                            $('#clientSelect').val(null).trigger('change');
                            $('#smsContent').val('');
                            $('#smsCounter').text('0/160 characters');
                            $('#clientDetailsSection').hide();
                        },
                        error: function(xhr) {
                            loadingAlert.close();
                            
                            console.error('Error sending SMS:', xhr);
                            Swal.fire({
                                title: 'Error',
                                text: 'Failed to send messages. Please try again.',
                                icon: 'error',
                                confirmButtonColor: '#014F5B'
                            });
                        }
                    });
                }
            });
        });
    }
    
    // Utility function to insert text at cursor position
    function insertAtCursor(selector, text) {
        const textarea = $(selector)[0];
        const startPos = textarea.selectionStart;
        const endPos = textarea.selectionEnd;
        textarea.value = textarea.value.substring(0, startPos) + text + textarea.value.substring(endPos);
        textarea.selectionStart = startPos + text.length;
        textarea.selectionEnd = startPos + text.length;
        textarea.focus();
        $(selector).trigger('input');
    }
</script>

<script>
    $(document).ready(function() {
        // Ensure dropdown initialization
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        
        // Fix for dropdown click handling
        $('.dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const dropdown = bootstrap.Dropdown.getInstance(this);
            if (!dropdown) {
                new bootstrap.Dropdown(this).toggle();
            } else {
                dropdown.toggle();
            }
        });
        
        // Open SMS Modal functionality
        $('#openSmsModal').on('click', function(e) {
            e.preventDefault();
            // Scroll to SMS section or show modal if you have one
            const smsSection = document.querySelector('.card .card-header:contains("Quick Client Messaging")').closest('.card');
            if (smsSection) {
                smsSection.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Ensure dropdown initialization for Bootstrap 5
        const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
        if (dropdownElementList.length > 0) {
            const dropdownList = [...dropdownElementList].map(dropdownToggleEl => {
                return new bootstrap.Dropdown(dropdownToggleEl, {
                    // Optional: Close other dropdowns when one is shown
                    autoClose: true
                });
            });
        }
        
        // Hover enhancement for action buttons
        $('.quick-action-item').hover(
            function() {
                // Add pulsing effect to icon on hover
                const icon = $(this).find('.action-icon');
                icon.addClass('animate-pulse');
            },
            function() {
                // Remove effect when hover ends
                const icon = $(this).find('.action-icon');
                icon.removeClass('animate-pulse');
            }
        );
        
        // Add animation class for pulse effect
        if (!document.getElementById('animation-styles')) {
            const style = document.createElement('style');
            style.id = 'animation-styles';
            style.textContent = `
                @keyframes pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                    100% { transform: scale(1); }
                }
                
                .animate-pulse {
                    animation: pulse 1s infinite;
                }
            `;
            document.head.appendChild(style);
        }
        
        // Open SMS section when clicking the SMS menu item
        $('#openSmsModal').on('click', function(e) {
            e.preventDefault();
            
            // First close the dropdown
            const dropdown = bootstrap.Dropdown.getInstance(document.getElementById('quickActionsDropdown'));
            if (dropdown) {
                dropdown.hide();
            }
            
            // Then scroll to SMS section with highlight effect
            const $smsSection = $('h5:contains("Quick Client Messaging")').closest('.card');
            
            $('html, body').animate({
                scrollTop: $smsSection.offset().top - 70
            }, 800, function() {
                // Add highlight effect after scrolling
                $smsSection.addClass('highlight-pulse');
                
                // Remove highlight after a delay
                setTimeout(function() {
                    $smsSection.removeClass('highlight-pulse');
                }, 2000);
            });
        });
        
        // Add styles for section highlight effect
        if (!document.getElementById('highlight-styles')) {
            const highlightStyle = document.createElement('style');
            highlightStyle.id = 'highlight-styles';
            highlightStyle.textContent = `
                @keyframes highlightPulse {
                    0% { box-shadow: 0 0 0 0 rgba(1, 79, 91, 0.7); }
                    70% { box-shadow: 0 0 0 10px rgba(1, 79, 91, 0); }
                    100% { box-shadow: 0 0 0 0 rgba(1, 79, 91, 0); }
                }
                
                .highlight-pulse {
                    animation: highlightPulse 1s ease-out;
                }
            `;
            document.head.appendChild(highlightStyle);
        }
        
        // Add a cool rotation effect to the refresh button icon
        $('#refreshDashboard').on('click', function() {
            const icon = $(this).find('i');
            icon.addClass('rotate-animation');
            
            // Remove the class after animation completes
            setTimeout(function() {
                icon.removeClass('rotate-animation');
            }, 1000);
            
            // Actual reload takes place after animation
            setTimeout(function() {
                location.reload();
            }, 300);
        });
        
        // Add rotation animation style
        if (!document.getElementById('rotation-style')) {
            const rotateStyle = document.createElement('style');
            rotateStyle.id = 'rotation-style';
            rotateStyle.textContent = `
                @keyframes rotate {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                
                .rotate-animation {
                    animation: rotate 0.8s ease-out;
                }
            `;
            document.head.appendChild(rotateStyle);
        }
    });
</script>
@endpush

@push('css_or_js')
<style>
/* Improved Header Styling */
.avatar-lg {
    width: 72px;
    height: 72px;
    font-size: 28px;
    transition: transform 0.3s ease;
}

.avatar-lg:hover {
    transform: scale(1.05);
}

.bg-gradient-primary {
    background: linear-gradient(45deg, #014F5B 0%, #2980b9 100%) !important;
}

/* Badge styling */
.badge {
    font-weight: 500;
    letter-spacing: 0.3px;
}

.bg-opacity-25 {
    --bs-bg-opacity: 0.25;
}

/* Quick Action Dropdown Styling */
.header-action-dropdown .dropdown-menu {
    width: 280px;
    border-radius: 0.5rem;
    padding: 0.5rem;
    margin-top: 0.5rem;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.dropdown-header {
    color: #6c757d;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.5rem 1rem;
}

.quick-action-item {
    display: flex;
    align-items: flex-start;
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.quick-action-item:hover {
    background-color: rgba(1, 79, 91, 0.08);
    transform: translateX(2px);
}

.quick-action-item:active {
    background-color: rgba(1, 79, 91, 0.12);
}

.action-icon {
    font-size: 1.25rem;
    color: #014F5B;
    margin-right: 0.75rem;
    padding: 0.5rem;
    background-color: rgba(1, 79, 91, 0.1);
    border-radius: 0.375rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.quick-action-item:hover .action-icon {
    background-color: rgba(1, 79, 91, 0.2);
}

.action-title {
    display: block;
    font-weight: 500;
    font-size: 0.9rem;
    color: #333;
}

.action-desc {
    display: block;
    font-size: 0.75rem;
    color: #6c757d;
}

.dropdown-divider {
    margin: 0.25rem 0;
    opacity: 0.1;
}

/* Button Glow Effects */
.btn-action-glow {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-action-glow:hover {
    background-color: rgba(255, 255, 255, 0.2);
    transform: translateY(-2px);
}

.btn-action-glow:active {
    transform: translateY(0);
}

.btn-refresh-glow {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
}

.btn-refresh-glow:hover {
    box-shadow: 0 5px 15px rgba(1, 79, 91, 0.4);
    transform: translateY(-2px);
}

.btn-refresh-glow:active {
    box-shadow: 0 2px 5px rgba(1, 79, 91, 0.4);
    transform: translateY(0);
}
</style>
<style>
    /* Steve Jobs inspired minimalist styles */
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    }
    
    .fw-light {
        font-weight: 300 !important;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(45deg, #014F5B 0%, #2980b9 100%) !important;
    }
    
    .bg-gradient-success {
        background: linear-gradient(45deg, #27ae60 0%, #2ecc71 100%) !important;
    }
    
    .bg-gradient-warning {
        background: linear-gradient(45deg, #f39c12 0%, #f1c40f 100%) !important;
    }
    
    .bg-gradient-info {
        background: linear-gradient(45deg, #3498db 0%, #2980b9 100%) !important;
    }
    
    .icon-bg {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-sm {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 12px;
    }
    
    .bg-soft-primary {
        background-color: rgba(1, 79, 91, 0.15) !important;
    }
    
    .bg-soft-success {
        background-color: rgba(46, 204, 113, 0.15) !important;
    }
    
    .bg-soft-info {
        background-color: rgba(52, 152, 219, 0.15) !important;
    }
    
    .bg-soft-warning {
        background-color: rgba(243, 156, 18, 0.15) !important;
    }
    
    .bg-soft-danger {
        background-color: rgba(231, 76, 60, 0.15) !important;
    }
    
    .text-primary {
        color: #014F5B !important;
    }
    
    .text-success {
        color: #2ecc71 !important;
    }
    
    .text-info {
        color: #3498db !important;
    }
    
    .text-warning {
        color: #f39c12 !important;
    }
    
    .text-danger {
        color: #e74c3c !important;
    }
    
    .text-orange {
        color: #e67e22 !important;
    }
    
    .select2-container .select2-selection--multiple {
        min-height: 38px !important;
    }
    
    .select2-container--default .select2-selection--multiple {
        border-color: #ced4da !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush