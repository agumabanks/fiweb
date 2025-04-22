<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Arrears Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <style>
        /* Dashboard Styles */
        :root {
            --primary-color: #4e73df;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --dark-color: #1a3a54;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }
        
        .dashboard-header {
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .metric-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            height: 100%;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            background-color: #fff;
            overflow: hidden;
        }
        
        .metric-card:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }
        
        .metric-card-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .metric-card-body {
            padding: 1.5rem;
        }
        
        .metric-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .metric-value {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.2;
            color: #212529;
        }
        
        .metric-trend {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        
        .trend-up { color: var(--success-color); }
        .trend-down { color: var(--danger-color); }
        
        .priority-badge {
            font-size: 0.75rem;
            padding: 0.4rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
        }
        
        .priority-high {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }
        
        .priority-medium {
            background-color: rgba(246, 194, 62, 0.1);
            color: var(--warning-color);
        }
        
        .priority-low {
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
        }
        
        .filter-section {
            background-color: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
            color: #212529;
            display: flex;
            align-items: center;
        }
        
        .filter-title i {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .filter-group {
            margin-bottom: 1.25rem;
        }
        
        .filter-label {
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #495057;
        }
        
        .btn-filter {
            font-size: 0.85rem;
            padding: 0.4rem 0.75rem;
            margin-right: 0.25rem;
            margin-bottom: 0.5rem;
            border-radius: 6px;
            font-weight: 500;
            border: 1px solid #ddd;
            background-color: #fff;
            color: #495057;
            transition: all 0.2s ease;
        }
        
        .btn-filter:hover {
            background-color: #f8f9fa;
        }
        
        .btn-filter.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .table-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fc;
        }
        
        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            color: #212529;
            display: flex;
            align-items: center;
        }
        
        .table-title i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .table-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-export, .btn-print {
            font-size: 0.875rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }
        
        .btn-export i, .btn-print i {
            font-size: 1rem;
        }
        
        .table-content {
            padding: 0;
            background-color: #fff;
        }
        
        .custom-table {
            margin-bottom: 0;
            width: 100%;
        }
        
        .custom-table th {
            font-weight: 600;
            color: #495057;
            border-top: none;
            padding: 1.25rem 1rem;
            background-color: #f8f9fc;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .custom-table td {
            vertical-align: middle;
            padding: 1.25rem 1rem;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        .client-name {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.25rem;
            font-size: 1rem;
        }
        
        .client-phone {
            color: #6c757d;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .loan-id {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 0.9rem;
        }
        
        .loan-dates {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .loan-dates div {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-bottom: 0.15rem;
        }
        
        .loan-dates i {
            font-size: 0.8rem;
            color: #adb5bd;
        }
        
        .progress {
            height: 8px;
            margin: 0.75rem 0 0.25rem;
            border-radius: 50px;
            background-color: rgba(0,0,0,0.05);
        }
        
        .progress-bar {
            border-radius: 50px;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
        }
        
        .progress-percentage {
            font-weight: 600;
        }
        
        .progress-values {
            color: #6c757d;
        }
        
        .overdue-amount {
            font-weight: 700;
            color: var(--danger-color);
            font-size: 1.1rem;
        }
        
        .overdue-details {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .overdue-details div {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-bottom: 0.15rem;
        }
        
        .overdue-details i {
            font-size: 0.8rem;
            color: #adb5bd;
        }
        
        .status-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            background-color: rgba(28, 200, 138, 0.1);
            color: var(--success-color);
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .status-badge i {
            font-size: 0.8rem;
        }
        
        .action-btn {
            padding: 0.475rem 0.85rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 6px;
            margin-right: 0.25rem;
            border: none;
            cursor: pointer;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-details {
            background-color: var(--dark-color);
            color: white;
        }
        
        .btn-profile {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-details:hover, .btn-profile:hover {
            opacity: 0.9;
            color: white;
        }
        
        /* Modal Styles */
        .modal-dialog {
            max-width: 800px;
        }
        
        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header-primary {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-title {
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .modal-title i {
            margin-right: 0.75rem;
        }
        
        .modal-close {
            color: white;
            opacity: 0.8;
        }
        
        .modal-close:hover {
            color: white;
            opacity: 1;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1.25rem 2rem;
            background-color: #f8f9fc;
            border-radius: 0 0 12px 12px;
        }
        
        .loan-detail-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
        }
        
        .client-detail {
            margin-left: 1rem;
            flex-grow: 1;
        }
        
        .client-avatar {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        
        .client-detail-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: #212529;
        }
        
        .client-detail-info {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .client-detail-info i {
            color: #adb5bd;
            font-size: 0.9rem;
        }
        
        .loan-stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        
        .loan-stat-card {
            background-color: #f8f9fc;
            padding: 1.25rem;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .loan-stat-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .loan-stat-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #212529;
        }
        
        .loan-stat-value.text-danger {
            color: var(--danger-color) !important;
        }
        
        .installment-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .installment-table th {
            font-size: 0.9rem;
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fc;
            padding: 0.85rem;
            text-align: left;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .installment-table td {
            font-size: 0.9rem;
            padding: 0.85rem;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .status-pill {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-pending {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }
        
        .status-partial {
            background-color: rgba(246, 194, 62, 0.1);
            color: var(--warning-color);
        }
        
        /* DataTables styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.35rem 0.75rem;
            margin-left: 0.25rem;
            border-radius: 6px;
            border: none !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            color: white !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #eaecf4 !important;
            color: var(--primary-color) !important;
        }
        
        /* Severity indicators */
        .severity-high {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .severity-medium {
            color: var(--warning-color);
            font-weight: 600;
        }
        
        .severity-low {
            color: var(--success-color);
            font-weight: 600;
        }
        
        /* Recent payments section */
        .recent-payments {
            margin-top: 2rem;
        }
        
        .recent-payment-item {
            display: flex;
            justify-content: space-between;
            padding: 0.85rem 0;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .payment-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .payment-amount {
            font-weight: 600;
            color: var(--success-color);
        }
        
        /* Dashboard summary cards */
        .metric-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .icon-primary {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
        }
        
        .icon-danger {
            background-color: rgba(231, 74, 59, 0.1);
            color: var(--danger-color);
        }
        
        .icon-warning {
            background-color: rgba(246, 194, 62, 0.1);
            color: var(--warning-color);
        }
        
        .icon-info {
            background-color: rgba(54, 185, 204, 0.1);
            color: var(--info-color);
        }
        
        .priority-distribution {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .priority-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .loan-stat-grid {
                grid-template-columns: 1fr;
            }
            
            .table-actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .modal-dialog {
                margin: 0.5rem;
            }
            
            .action-btn {
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="container-fluid p-4">
    <!-- Dashboard Header -->
    <div class="dashboard-header d-flex flex-wrap align-items-center justify-content-between">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Loan Arrears Management</h1>
            <p class="mb-0 text-muted">Monitor and manage overdue loan installments efficiently</p>
        </div>
        <div class="d-flex align-items-center">
            <div class="mr-3">
                <span class="text-muted small mr-2">Last updated:</span>
                <span class="badge badge-primary p-2">{{ now()->format('M d, Y - h:i A') }}</span>
            </div>
            <button id="refresh-data" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-sync-alt mr-1"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <!-- Clients in Arrears Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card h-100">
                <div class="p-3 d-flex align-items-center">
                    <div class="metric-icon icon-primary mr-3">
                        <i class="fas fa-users fa-lg"></i>
                    </div>
                    <div>
                        <div class="metric-label mb-1">CLIENTS IN ARREARS</div>
                        <div class="d-flex align-items-baseline">
                            <div class="text-primary metric-value" id="clients-count">0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Overdue Amount Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card h-100">
                <div class="p-3 d-flex align-items-center">
                    <div class="metric-icon icon-danger mr-3">
                        <i class="fas fa-money-bill-wave fa-lg"></i>
                    </div>
                    <div>
                        <div class="metric-label mb-1">TOTAL OVERDUE AMOUNT</div>
                        <div class="d-flex align-items-baseline">
                            <div class="text-danger metric-value" id="total-overdue">UGX 0</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Average Days Overdue Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card h-100">
                <div class="p-3 d-flex align-items-center">
                    <div class="metric-icon icon-warning mr-3">
                        <i class="fas fa-calendar-times fa-lg"></i>
                    </div>
                    <div>
                        <div class="metric-label mb-1">AVERAGE DAYS OVERDUE</div>
                        <div class="d-flex align-items-baseline">
                            <div class="text-warning metric-value" id="avg-days-overdue">0</div>
                            <div class="text-muted ml-2">days</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Priority Distribution Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="metric-card h-100">
                <div class="p-3">
                    <div class="metric-label mb-2">PRIORITY DISTRIBUTION</div>
                    <div class="priority-distribution">
                        <div class="priority-item">
                            <span class="priority-badge priority-high"><i class="fas fa-exclamation-circle mr-1"></i> High Priority</span>
                            <span id="high-priority-count" class="font-weight-bold">0</span>
                        </div>
                        <div class="priority-item">
                            <span class="priority-badge priority-medium"><i class="fas fa-exclamation-triangle mr-1"></i> Medium Priority</span>
                            <span id="medium-priority-count" class="font-weight-bold">0</span>
                        </div>
                        <div class="priority-item">
                            <span class="priority-badge priority-low"><i class="fas fa-info-circle mr-1"></i> Low Priority</span>
                            <span id="low-priority-count" class="font-weight-bold">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Filter Section -->
    <div class="filter-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="filter-title mb-0"><i class="fas fa-filter"></i> Filter & Sort Options</h6>
            <button id="reset-all" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-redo-alt mr-1"></i> Reset All
            </button>
        </div>
        
        <div class="row">
            <!-- Search -->
            <div class="col-md-4 col-lg-4 mb-3">
                <label for="search-input" class="filter-label">Search Client or Loan</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-transparent border-right-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" id="search-input" class="form-control border-left-0" placeholder="Name, phone, or loan ID">
                </div>
            </div>
            
            <!-- Agent Filter -->
            <div class="col-md-4 col-lg-4 mb-3">
                <label for="agent-select" class="filter-label">Agent</label>
                <select id="agent-select" class="form-control">
                    <option value="all">All Agents</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->f_name }} {{ $agent->l_name }}</option>
                    @endforeach
                </select>
            </div>
            
            <!-- Sort Order -->
            <div class="col-md-4 col-lg-4 mb-3">
                <label class="filter-label">Sort By</label>
                <div class="d-flex">
                    <select id="sort-field" class="form-control mr-2">
                        <option value="total_overdue_amount">Overdue Amount</option>
                        <option value="days_overdue">Days Overdue</option>
                        <option value="total_overdue_installments">Missed Installments</option>
                        <option value="progress">Loan Progress</option>
                    </select>
                    <select id="sort-direction" class="form-control" style="width: 120px;">
                        <option value="desc">Highest</option>
                        <option value="asc">Lowest</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Priority Filter -->
            <div class="col-md-6 mb-3">
                <label class="filter-label">Priority Level</label>
                <div>
                    <button type="button" class="btn btn-filter active" data-priority="all">All</button>
                    <button type="button" class="btn btn-filter" data-priority="high">
                        <i class="fas fa-exclamation-circle mr-1"></i> High
                    </button>
                    <button type="button" class="btn btn-filter" data-priority="medium">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Medium
                    </button>
                    <button type="button" class="btn btn-filter" data-priority="low">
                        <i class="fas fa-info-circle mr-1"></i> Low
                    </button>
                </div>
            </div>
            
            <!-- Overdue Period Filter -->
            <div class="col-md-6 mb-3">
                <label class="filter-label">Overdue Period</label>
                <div>
                    <button type="button" class="btn btn-filter" data-days="7">Last 7 Days</button>
                    <button type="button" class="btn btn-filter" data-days="14">Last 14 Days</button>
                    <button type="button" class="btn btn-filter" data-days="30">Last 30 Days</button>
                    <button type="button" class="btn btn-filter" data-days="60">Last 60 Days</button>
                    <button type="button" class="btn btn-filter" data-days="90">Last 90 Days</button>
                    <button type="button" class="btn btn-filter active" data-days="0">All Time</button>
                </div>
            </div>
        </div>
        
        <div class="row mt-2">
            <div class="col-12">
                <button id="apply-filters" class="btn btn-primary">
                    <i class="fas fa-search mr-1"></i> Apply Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Loan Arrears Table Card -->
    <div class="table-card">
        <div class="table-header">
            <h5 class="table-title">
                <i class="fas fa-list"></i> Loan Arrears Overview
            </h5>
            <div class="table-actions">
                <button class="btn btn-danger btn-export" id="export-pdf">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </button>
                <button class="btn btn-secondary btn-print" id="print-table">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
        <div class="table-content">
            <div class="table-responsive">
                <table id="arrears-table" class="table custom-table" width="100%">
                    <thead>
                        <tr>
                            <th>CLIENT</th>
                            <th>LOAN DETAILS</th>
                            <th>LOAN PROGRESS</th>
                            <th>OVERDUE</th>
                            <th>PRIORITY</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Loan Installment Details Modal -->
<div class="modal fade" id="loan-details-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <!-- Simplified Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    Loan Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <!-- Client Info Card - Fixed at top -->
            <div class="client-info-card">
                <div class="client-info-header">
                    <div class="client-avatar" id="modal-client-avatar">
                        <span id="modal-client-initials">CL</span>
                    </div>
                    <div class="client-info-meta">
                        <h5 id="modal-client-name">Client Name</h5>
                        <div class="client-info-details">
                            <span id="modal-client-phone"><i class="fas fa-phone-alt"></i> Phone Number</span>
                        </div>
                    </div>
                </div>
                <div class="client-highlights">
                    <div class="highlight-item">
                        <span class="highlight-label">Loan Amount</span>
                        <span id="modal-loan-amount" class="highlight-value">UGX 0</span>
                    </div>
                    <div class="highlight-item">
                        <span class="highlight-label">Client Balance</span>
                        <span id="modal-client-balance" class="highlight-value">UGX 0</span>
                    </div>
                </div>
            </div>
            
            <!-- Scrollable Content Area -->
            <div class="modal-body-scrollable">
                <!-- Loan Stats Section -->
                <div class="content-section">
                    <div class="loan-stat-grid">
                        <div class="loan-stat-card">
                            <div class="loan-stat-value" id="modal-loan-taken-date">--</div>
                            <div class="loan-stat-label">Loan Taken Date</div>
                        </div>
                        <div class="loan-stat-card">
                            <div class="loan-stat-value" id="modal-loan-due-date">--</div>
                            <div class="loan-stat-label">Loan Due Date</div>
                        </div>
                        <div class="loan-stat-card loan-stat-card-progress">
                            <div class="loan-progress-container">
                                <div class="loan-progress-header">
                                    <div class="loan-stat-value" id="modal-loan-progress">0%</div>
                                    <div class="loan-progress-expected" id="modal-expected-progress">Expected: 0%</div>
                                </div>
                                <div class="progress">
                                    <div id="modal-progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>
                            <div class="loan-stat-label">Loan Progress</div>
                        </div>
                        <div class="loan-stat-card">
                            <div class="loan-stat-value text-danger" id="modal-days-overdue">0</div>
                            <div class="loan-stat-label">Days Overdue</div>
                        </div>
                    </div>
                </div>
                
                <!-- Missed Installments Section -->
                <div class="content-section">
                    <h6 class="section-title">Missed Installments</h6>
                    <div class="table-container">
                        <table class="installment-table">
                            <thead>
                                <tr>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Balance</th>
                                    <th>Days Late</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="installments-table-body">
                                <tr>
                                    <td colspan="5" class="text-center py-3">
                                        <div class="spinner-border spinner-border-sm text-primary mr-2" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        Loading installment details...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Recent Payments Section -->
                <div class="content-section">
                    <h6 class="section-title">Recent Payments</h6>
                    <div id="recent-payments-container">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary mr-2" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            Loading recent payments...
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Footer -->
            <div class="modal-footer">
                <div class="modal-actions">
                    <a href="#" id="client-profile-link" class="btn btn-light">
                        View Profile
                    </a>
                    <a href="#" id="process-payment-link" class="btn btn-primary">
                        Process Payment
                    </a>
                </div>
                <button type="button" class="btn btn-link" data-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add this CSS to your style section -->
<style>
    /* ===== MODAL CORE STYLING ===== */
    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }
    
    .modal-content {
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        border-radius: 12px;
        border: none;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    /* ===== HEADER STYLING ===== */
    .modal-header {
        padding: 18px 24px;
        background-color: #fff;
        border-bottom: none;
    }
    
    .modal-title {
        font-size: 18px;
        font-weight: 500;
        color: #1a1a1a;
        letter-spacing: -0.2px;
    }
    
    .modal-header .close {
        padding: 18px;
        margin: -18px -18px -18px auto;
        opacity: 0.5;
        transition: opacity 0.2s ease;
    }
    
    .modal-header .close:hover {
        opacity: 0.8;
    }
    
    /* ===== CLIENT INFO CARD ===== */
    .client-info-card {
        padding: 0 24px 18px;
        background-color: #fff;
        position: relative;
    }
    
    .client-info-header {
        display: flex;
        align-items: center;
        margin-bottom: 16px;
    }
    
    .client-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: #f0f4f9;
        color: #4e73df;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 600;
        margin-right: 16px;
    }
    
    .client-info-meta h5 {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 4px;
        color: #1a1a1a;
    }
    
    .client-info-details {
        font-size: 14px;
        color: #6b7280;
    }
    
    .client-info-details i {
        font-size: 12px;
        margin-right: 4px;
    }
    
    .client-highlights {
        display: flex;
        margin: 0 -8px;
    }
    
    .highlight-item {
        flex: 1;
        margin: 0 8px;
        padding: 12px 16px;
        background-color: #f9fafb;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
    }
    
    .highlight-label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 4px;
    }
    
    .highlight-value {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
    }
    
    /* ===== SCROLLABLE CONTENT ===== */
    .modal-body-scrollable {
        overflow-y: auto;
        scrollbar-width: thin;
        max-height: 60vh;
        border-top: 1px solid #f3f4f6;
    }
    
    .content-section {
        padding: 18px 24px;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .content-section:last-child {
        border-bottom: none;
    }
    
    .section-title {
        font-size: 15px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 16px;
        letter-spacing: -0.1px;
    }
    
    /* ===== LOAN STATS CARDS ===== */
    .loan-stat-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    
    .loan-stat-card {
        background-color: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
    }
    
    .loan-stat-card-progress {
        grid-column: span 2;
    }
    
    .loan-stat-value {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    
    .loan-stat-value.text-danger {
        color: #dc2626 !important;
    }
    
    .loan-stat-label {
        font-size: 13px;
        color: #6b7280;
    }
    
    .loan-progress-container {
        margin-bottom: 4px;
    }
    
    .loan-progress-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .loan-progress-expected {
        font-size: 13px;
        color: #6b7280;
    }
    
    .progress {
        height: 6px;
        background-color: #e5e7eb;
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 3px;
    }
    
    /* ===== TABLE STYLING ===== */
    .table-container {
        margin: 0 -24px;
    }
    
    .installment-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .installment-table th {
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        padding: 12px 24px;
        text-align: left;
        border-bottom: 1px solid #f3f4f6;
        background-color: #f9fafb;
    }
    
    .installment-table td {
        font-size: 14px;
        padding: 12px 24px;
        border-bottom: 1px solid #f3f4f6;
        color: #1a1a1a;
    }
    
    .installment-table tr:last-child td {
        border-bottom: none;
    }
    
    .status-pill {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 12px;
        font-weight: 500;
    }
    
    /* ===== RECENT PAYMENTS ===== */
    .recent-payment-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .recent-payment-item:last-child {
        border-bottom: none;
    }
    
    .payment-date {
        font-size: 14px;
        color: #6b7280;
    }
    
    .payment-amount {
        font-size: 14px;
        font-weight: 600;
        color: #10b981;
    }
    
    /* ===== MODAL FOOTER ===== */
    .modal-footer {
        padding: 16px 24px;
        background-color: #f9fafb;
        border-top: 1px solid #f3f4f6;
        justify-content: space-between;
    }
    
    .modal-actions {
        display: flex;
        gap: 8px;
    }
    
    .btn-primary {
        background-color: #4e73df;
        border-color: #4e73df;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
    }
    
    .btn-light {
        background-color: #fff;
        border-color: #e5e7eb;
        color: #6b7280;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
    }
    
    .btn-link {
        color: #6b7280;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
    }
    
    .btn-primary:hover {
        background-color: #4262c5;
        border-color: #4262c5;
    }
    
    .btn-light:hover {
        background-color: #f9fafb;
        border-color: #d1d5db;
    }
    
    .btn-link:hover {
        text-decoration: none;
        color: #4b5563;
    }
    
    /* ===== SCROLLBAR STYLING ===== */
    .modal-body-scrollable::-webkit-scrollbar {
        width: 6px;
    }
    
    .modal-body-scrollable::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .modal-body-scrollable::-webkit-scrollbar-thumb {
        background-color: #d1d5db;
        border-radius: 3px;
    }
    
    .modal-body-scrollable::-webkit-scrollbar-thumb:hover {
        background-color: #9ca3af;
    }
    
    /* ===== ANIMATION ===== */
    .modal.fade .modal-dialog.modal-dialog-centered {
        transform: translate(0, -10px);
        transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    
    .modal.show .modal-dialog.modal-dialog-centered {
        transform: translate(0, 0);
    }
    
    /* ===== RESPONSIVE ADJUSTMENTS ===== */
    @media (max-width: 768px) {
        .loan-stat-grid {
            grid-template-columns: 1fr;
        }
        
        .loan-stat-card-progress {
            grid-column: span 1;
        }
        
        .client-highlights {
            flex-direction: column;
        }
        
        .highlight-item {
            margin-bottom: 8px;
        }
        
        .modal-footer {
            flex-direction: column;
            align-items: stretch;
        }
        
        .modal-actions {
            margin-bottom: 12px;
        }
        
        .btn {
            width: 100%;
            text-align: center;
            margin-bottom: 8px;
        }
    }
</style>

<!-- Required Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.70/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    // Variables for filters
    let priorityFilter = 'all';
    let overdueFilter = 0;
    let sortField = 'total_overdue_amount';
    let sortDirection = 'desc';
    
    // Utility functions
    function formatCurrency(amount) {
        return 'UGX ' + parseFloat(amount || 0).toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }
    
    function getInitials(name) {
        if (!name) return 'CL';
        return name.split(' ').map(part => part[0]).join('').toUpperCase();
    }
    
    function getSeverityClass(severity) {
        switch(severity) {
            case 'high': return 'severity-high';
            case 'medium': return 'severity-medium';
            case 'low': return 'severity-low';
            default: return '';
        }
    }
    
    // Initialize DataTable with proper error handling
    const table = $('#arrears-table').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"top"<"left"l><"right"f>>rt<"bottom"<"left"i><"right"p>>',
        language: {
            processing: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div><div class="mt-2">Loading data...</div></div>',
            emptyTable: 'No overdue loans found',
            zeroRecords: 'No matching records found',
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        ajax: {
            url: "{{ route('admin.loan-arrears.data') }}",
            type: 'GET',
            data: function(d) {
                d.search = { value: $('#search-input').val() };
                d.agent_id = $('#agent-select').val();
                d.overdue_days = overdueFilter;
                d.priority = priorityFilter !== 'all' ? priorityFilter : null;
                d.sort_field = sortField;
                d.sort_direction = sortDirection;
                // Add CSRF token
                d._token = $('meta[name="csrf-token"]').attr('content');
            },
            dataSrc: function(json) {
                // Update summary metrics
                updateDashboardMetrics(json.summary || {});
                return json.data || [];
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
                console.log('Response:', xhr.responseText);
                
                $('#arrears-table tbody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="alert alert-danger mb-0">
                                <h5 class="alert-heading">Failed to load data</h5>
                                <p>Error: ${thrown || 'Unknown error'}</p>
                                <hr>
                                <p class="mb-0">
                                    <button class="btn btn-sm btn-primary" onclick="location.reload()">
                                        <i class="fas fa-sync-alt mr-1"></i> Retry
                                    </button>
                                </p>
                            </div>
                        </td>
                    </tr>
                `);
                
                $('.dataTables_processing').hide();
            }
        },
        columns: [
            { 
                data: null,
                render: function(data) {
                    return `
                        <div>
                            <div class="client-name">${data.client_name}</div>
                            <div class="client-phone"><i class="fas fa-phone-alt"></i> ${data.client_phone}</div>
                        </div>
                    `;
                }
            },
            { 
                data: null,
                render: function(data) {
                    return `
                        <div>
                            <div class="loan-id"> ${formatCurrency(data.loan_final_amount)}</div>
                            <div class="loan-dates">
                                <div><i class="fas fa-calendar-plus"></i> Taken: ${moment(data.loan_taken_date).format('MMM DD, YYYY')}</div>
                                <div><i class="fas fa-calendar-times"></i> Due: ${moment(data.due_date).format('MMM DD, YYYY')}</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    const paidAmount = parseFloat(data.loan_paid_amount) || 0;
                    const totalAmount = parseFloat(data.loan_final_amount) || 1;
                    const progressPercent = Math.min(Math.round((paidAmount / totalAmount) * 100), 100);
                    
                    // Calculate expected progress based on time elapsed
                    const loanTakenDate = moment(data.loan_taken_date);
                    const dueDate = moment(data.due_date);
                    const today = moment();
                    const totalDays = dueDate.diff(loanTakenDate, 'days');
                    const elapsedDays = today.diff(loanTakenDate, 'days');
                    let expectedProgress = 0;
                    
                    if (totalDays > 0) {
                        expectedProgress = Math.min(100, Math.round((elapsedDays / totalDays) * 100));
                    }
                    
                    // Determine progress bar color based on deficit
                    let progressBarClass = 'bg-success';
                    if (progressPercent < expectedProgress - 20) {
                        progressBarClass = 'bg-danger';
                    } else if (progressPercent < expectedProgress - 10) {
                        progressBarClass = 'bg-warning';
                    }
                    
                    return `
                        <div>
                            <div class="progress-info">
                                <span class="progress-percentage">${progressPercent}% complete</span>
                                <span class="progress-values">${formatCurrency(paidAmount)} / ${formatCurrency(totalAmount)}</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar ${progressBarClass}" style="width: ${progressPercent}%"></div>
                            </div>
                            <div class="text-muted small mt-1">Expected: ${expectedProgress}% (${formatCurrency(totalAmount * expectedProgress / 100)})</div>
                        </div>
                    `;
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div>
                            <div class="overdue-amount">${formatCurrency(data.total_overdue_amount)}</div>
                            <div class="overdue-details">
                                <div><i class="fas fa-calendar-minus"></i> ${data.total_overdue_installments} installment(s)</div>
                                <div><i class="fas fa-clock"></i> ${data.days_overdue} days overdue</div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: 'priority',
                render: function(data) {
                    let priorityClass = 'priority-low';
                    let icon = 'info-circle';
                    
                    if (data === 'medium') {
                        priorityClass = 'priority-medium';
                        icon = 'exclamation-triangle';
                    } else if (data === 'high') {
                        priorityClass = 'priority-high';
                        icon = 'exclamation-circle';
                    }
                    
                    return `<span class="priority-badge ${priorityClass}"><i class="fas fa-${icon} mr-1"></i> ${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    const clientBalance = parseFloat(data.client_balance) || 0;
                    
                    let badge = '';
                    if (clientBalance <= 0) {
                        badge = '<div class="status-badge"><i class="fas fa-check-circle"></i> Has No Balance</div>';
                    } else {
                        badge = '<div class="status-badge" style="background-color: rgba(231, 74, 59, 0.1); color: #e74a3b;"><i class="fas fa-exclamation-circle"></i> Has Balance</div>';
                    }
                    
                    return `
                        <div>
                            <div class="font-weight-bold mb-1">${formatCurrency(clientBalance)}</div>
                            ${badge}
                        </div>
                    `;
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function(data) {
                    return `
                        <button class="action-btn btn-details view-details" data-loan-id="${data.loan_id}">
                            <i class="fas fa-eye"></i> Details
                        </button>
                        <a href="/admin/clients/${data.client_id}" class="action-btn btn-profile">
                            <i class="fas fa-user"></i> Profile
                        </a>
                    `;
                }
            }
        ],
        order: [[3, 'desc']], // Default sort by overdue amount
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 25,
        buttons: [
            {
                extend: 'pdfHtml5',
                text: 'Export PDF',
                className: 'btn btn-danger',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                orientation: 'landscape',
                pageSize: 'A4',
                title: 'Loan Arrears Report',
                customize: function(doc) {
                    doc.defaultStyle.fontSize = 10;
                    doc.styles.tableHeader.fontSize = 11;
                    doc.styles.tableHeader.alignment = 'left';
                    
                    // Title
                    doc.content.splice(0, 1, {
                        text: [
                            { text: 'Loan Arrears Report\n\n', style: { fontSize: 18, bold: true, alignment: 'center' } },
                            { text: `Generated on ${moment().format('MMMM D, YYYY [at] h:mm A')}\n\n`, style: { fontSize: 12, alignment: 'center' } }
                        ]
                    });
                    
                    // Summary section
                    doc.content.splice(1, 0, {
                        columns: [
                            {
                                width: '60%',
                                text: [
                                    { text: 'Summary:\n', style: { fontSize: 14, bold: true } },
                                    { text: `Clients in Arrears: ${$('#clients-count').text()}\n` },
                                    { text: `Total Overdue Amount: ${$('#total-overdue').text()}\n` },
                                    { text: `Average Days Overdue: ${$('#avg-days-overdue').text()}\n\n` }
                                ]
                            },
                            {
                                width: '40%',
                                text: [
                                    { text: 'Priority Distribution:\n', style: { fontSize: 14, bold: true } },
                                    { text: `High Priority: ${$('#high-priority-count').text()}\n` },
                                    { text: `Medium Priority: ${$('#medium-priority-count').text()}\n` },
                                    { text: `Low Priority: ${$('#low-priority-count').text()}\n` }
                                ]
                            }
                        ],
                        margin: [0, 0, 0, 20]
                    });
                }
            },
            {
                extend: 'print',
                text: 'Print',
                className: 'btn btn-secondary',
                exportOptions: { columns: [0, 1, 2, 3, 4, 5] },
                customize: function(win) {
                    $(win.document.body).css('font-size', '10pt');
                    $(win.document.body).find('table')
                        .addClass('compact')
                        .css('font-size', 'inherit');
                        
                    // Add title and summary
                    $(win.document.body).prepend(
                        `<div style="text-align: center; margin-bottom: 20px;">
                            <h1>Loan Arrears Report</h1>
                            <p>Generated on ${moment().format('MMMM D, YYYY [at] h:mm A')}</p>
                        </div>
                        <div style="display: flex; margin-bottom: 20px;">
                            <div style="flex: 60%;">
                                <h3>Summary</h3>
                                <p>Clients in Arrears: ${$('#clients-count').text()}<br>
                                Total Overdue Amount: ${$('#total-overdue').text()}<br>
                                Average Days Overdue: ${$('#avg-days-overdue').text()}</p>
                            </div>
                            <div style="flex: 40%;">
                                <h3>Priority Distribution</h3>
                                <p>High Priority: ${$('#high-priority-count').text()}<br>
                                Medium Priority: ${$('#medium-priority-count').text()}<br>
                                Low Priority: ${$('#low-priority-count').text()}</p>
                            </div>
                        </div>`
                    );
                }
            }
        ]
    });
    
    // Initialize export buttons
    const exportButtons = new $.fn.dataTable.Buttons(table, {
        buttons: ['pdfHtml5', 'print']
    });
    
    // Update dashboard metrics
    function updateDashboardMetrics(summary) {
        $('#clients-count').text(summary.client_count || 0);
        $('#total-overdue').text(formatCurrency(summary.total_overdue_amount || 0));
        $('#avg-days-overdue').text(summary.average_days_overdue || 0);
        $('#high-priority-count').text(summary.high_priority_count || 0);
        $('#medium-priority-count').text(summary.medium_priority_count || 0);
        $('#low-priority-count').text(summary.low_priority_count || 0);
    }
    
    // Get loan details for modal
    function getLoanDetails(loanId) {
        // Show loading state
        $('#installments-table-body').html(`
            <tr>
                <td colspan="5" class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary mr-2" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    Loading installment details...
                </td>
            </tr>
        `);
        
        // Show loading for recent payments
        $('#recent-payments-container').html(`
            <div class="text-center py-3">
                <div class="spinner-border spinner-border-sm text-primary mr-2" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                Loading recent payments...
            </div>
        `);
        
        // Fetch loan details
        $.ajax({
            url: `/admin/loan-arrears/missed-installments/${loanId}`,
            type: 'GET',
            success: function(response) {
                const loan = response.loan || {};
                const installments = response.installments || [];
                const recentPayments = response.recent_payments || [];
                const summary = response.summary || {};
                
                // Update client info
                $('#modal-client-name').text(loan.client_name || 'N/A');
                $('#modal-client-phone').text(loan.client_phone || 'N/A');
                $('#modal-loan-amount').text(formatCurrency(loan.amount || 0));
                $('#modal-client-balance').text(formatCurrency(loan.client_balance || 0));
                $('#modal-client-initials').text(getInitials(loan.client_name));
                
                // Update loan details
                $('#modal-loan-taken-date').text(moment(loan.loan_taken_date).format('MMM DD, YYYY'));
                $('#modal-loan-due-date').text(moment(loan.due_date).format('MMM DD, YYYY'));
                
                // Update progress
                const progress = summary.progress || 0;
                const expectedProgress = summary.expected_progress || 0;
                $('#modal-loan-progress').text(`${progress}%`);
                $('#modal-expected-progress').text(`(Expected: ${expectedProgress}%)`);
                
                // Determine progress bar color based on deficit
                let progressBarClass = 'bg-success';
                if (progress < expectedProgress - 20) {
                    progressBarClass = 'bg-danger';
                } else if (progress < expectedProgress - 10) {
                    progressBarClass = 'bg-warning';
                }
                
                $('#modal-progress-bar').removeClass('bg-success bg-warning bg-danger')
                    .addClass(progressBarClass)
                    .css('width', `${progress}%`);
                
                // Update overdue info
                $('#modal-days-overdue').text(summary.days_overdue || 0);
                
                // Update links
                $('#client-profile-link').attr('href', `/admin/clients/${loan.client_id}`);
                $('#process-payment-link').attr('href', `/admin/loans/pay-loan/${loan.id}`);
                
                // Update installments table
                if (installments.length > 0) {
                    let installmentsHtml = '';
                    
                    installments.forEach(function(item) {
                        let statusClass = 'status-pending';
                        let statusLabel = 'Pending';
                        
                        if (item.status === 'withbalance') {
                            statusClass = 'status-partial';
                            statusLabel = 'Partial';
                        }
                        
                        // Determine severity class
                        const severityClass = getSeverityClass(item.severity);
                        
                        installmentsHtml += `
                            <tr>
                                <td>${item.formatted_date}</td>
                                <td>${formatCurrency(item.install_amount || 0)}</td>
                                <td>${formatCurrency(item.installment_balance || 0)}</td>
                                <td class="${severityClass}">${item.days_late}</td>
                                <td><span class="status-pill ${statusClass}">${statusLabel}</span></td>
                            </tr>
                        `;
                    });
                    
                    $('#installments-table-body').html(installmentsHtml);
                } else {
                    $('#installments-table-body').html(`
                        <tr>
                            <td colspan="5" class="text-center">
                                <i class="fas fa-info-circle text-info mr-2"></i>
                                No missed installments found
                            </td>
                        </tr>
                    `);
                }
                
                // Update recent payments
                if (recentPayments.length > 0) {
                    let paymentsHtml = '';
                    
                    recentPayments.forEach(function(payment) {
                        paymentsHtml += `
                            <div class="recent-payment-item">
                                <div>
                                    <div class="payment-date">${moment(payment.payment_date).format('MMM DD, YYYY')}</div>
                                    <div class="text-muted small">${payment.note || 'No note'}</div>
                                </div>
                                <div class="payment-amount">${formatCurrency(payment.amount || 0)}</div>
                            </div>
                        `;
                    });
                    
                    $('#recent-payments-container').html(paymentsHtml);
                } else {
                    $('#recent-payments-container').html(`
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-info mr-2"></i>
                            No recent payments found
                        </div>
                    `);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching loan details:', error);
                
                $('#installments-table-body').html(`
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="alert alert-danger mb-0">
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                Failed to load installment details. Please try again.
                            </div>
                        </td>
                    </tr>
                `);
                
                $('#recent-payments-container').html(`
                    <div class="text-center py-3">
                        <div class="alert alert-danger mb-0">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            Failed to load recent payments. Please try again.
                        </div>
                    </div>
                `);
            }
        });
    }
    
    // View loan details
    $(document).on('click', '.view-details', function() {
        const loanId = $(this).data('loan-id');
        $('#loan-details-modal').modal('show');
        getLoanDetails(loanId);
    });
    
    // Priority filter buttons
    $('.btn-filter[data-priority]').on('click', function() {
        $('.btn-filter[data-priority]').removeClass('active');
        $(this).addClass('active');
        priorityFilter = $(this).data('priority');
    });
    
    // Overdue days filter buttons
    $('.btn-filter[data-days]').on('click', function() {
        $('.btn-filter[data-days]').removeClass('active');
        $(this).addClass('active');
        overdueFilter = $(this).data('days');
    });
    
    // Sort options
    $('#sort-field, #sort-direction').on('change', function() {
        sortField = $('#sort-field').val();
        sortDirection = $('#sort-direction').val();
    });
    
    // Apply filters
    $('#apply-filters').on('click', function() {
        table.ajax.reload();
    });
    
    // Refresh data
    $('#refresh-data').on('click', function() {
        table.ajax.reload();
    });
    
    // Reset all filters
    $('#reset-all').on('click', function() {
        $('#search-input').val('');
        $('#agent-select').val('all');
        $('#sort-field').val('total_overdue_amount');
        $('#sort-direction').val('desc');
        
        $('.btn-filter[data-priority]').removeClass('active');
        $('.btn-filter[data-priority="all"]').addClass('active');
        
        $('.btn-filter[data-days]').removeClass('active');
        $('.btn-filter[data-days="0"]').addClass('active');
        
        priorityFilter = 'all';
        overdueFilter = 0;
        sortField = 'total_overdue_amount';
        sortDirection = 'desc';
        
        table.ajax.reload();
    });
    
    // Connect export buttons to UI
    $('#export-pdf').on('click', function() {
        table.button(0).trigger();
    });
    
    $('#print-table').on('click', function() {
        table.button(1).trigger();
    });
    
    // Handle search on enter
    $('#search-input').on('keypress', function(e) {
        if (e.which === 13) {
            table.ajax.reload();
        }
    });
    
    // AJAX error handling
    $(document).ajaxError(function(event, jqXHR, settings, thrownError) {
        console.group('AJAX Error');
        console.log('Status:', jqXHR.status);
        console.log('Response:', jqXHR.responseText);
        console.log('Error:', thrownError);
        console.log('URL:', settings.url);
        console.groupEnd();
    });
});
</script>
</body>
</html>