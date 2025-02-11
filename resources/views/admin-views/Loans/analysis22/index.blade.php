@extends('layouts.admin.app')

@section('title', 'Loan Analysis Dashboard')

@push('css_or_js')
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom Styles for Enhanced Aesthetics */
        .card-header {
            background-color: #f8f9fa;
            border-bottom: none;
        }
        .alert-info, .alert-warning, .alert-success, .alert-danger, .alert-secondary {
            border-radius: 10px;
            padding: 20px;
        }
        .list-group-item {
            border: none;
            padding: 10px 20px;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="mt-4 mb-4 text-center">Loan Analysis Dashboard</h1>

    <!-- Error Display Section -->
    <div id="loan-analysis-errors"></div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <strong>Filters</strong>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <!-- Status Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="status">Loan Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="running">Running</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="defaulted">Defaulted</option>
                        </select>
                    </div>

                    <!-- Aging Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="aging">Loan Aging</label>
                        <select name="aging" id="aging" class="form-control">
                            <option value="all">All</option>
                            <option value="30">0-30 Days</option>
                            <option value="60">31-60 Days</option>
                            <option value="90+">61-90+ Days</option>
                        </select>
                    </div>

                    <!-- Partial Disbursed Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="partial_disbursed">Partial Disbursed</label>
                        <select name="partial_disbursed" id="partial_disbursed" class="form-control">
                            <option value="">All</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- Renewed Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="renewed">Renewed</label>
                        <select name="renewed" id="renewed" class="form-control">
                            <option value="">All</option>
                            <option value="1">Only Renewed</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <!-- Agent Filter -->
                    <div class="col-md-3 mb-3">
                        <label for="agent_id">Agent</label>
                        <select name="agent_id" id="agent_id" class="form-control">
                            <option value="all">All Agents</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->f_name }} {{ $agent->l_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Amount Range -->
                    <div class="col-md-3 mb-3">
                        <label for="min_amount">Min Amount</label>
                        <input type="number" name="min_amount" id="min_amount" class="form-control" placeholder="e.g., 1000">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="max_amount">Max Amount</label>
                        <input type="number" name="max_amount" id="max_amount" class="form-control" placeholder="e.g., 5000">
                    </div>

                    <!-- Outstanding Range -->
                    <div class="col-md-3 mb-3">
                        <label for="min_outstanding">Min Outstanding</label>
                        <input type="number" name="min_outstanding" id="min_outstanding" class="form-control" placeholder="e.g., 500">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="max_outstanding">Max Outstanding</label>
                        <input type="number" name="max_outstanding" id="max_outstanding" class="form-control" placeholder="e.g., 2000">
                    </div>
                </div>

                <div class="row">
                    <!-- Disbursement Date Range -->
                    <div class="col-md-3 mb-3">
                        <label for="from_disbursement">From Disbursement</label>
                        <input type="date" name="from_disbursement" id="from_disbursement" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="to_disbursement">To Disbursement</label>
                        <input type="date" name="to_disbursement" id="to_disbursement" class="form-control">
                    </div>

                    <!-- Due Date Range -->
                    <div class="col-md-3 mb-3">
                        <label for="from_due">From Due Date</label>
                        <input type="date" name="from_due" id="from_due" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="to_due">To Due Date</label>
                        <input type="date" name="to_due" id="to_due" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <!-- Per Page Limit -->
                    <div class="col-md-2 mb-3">
                        <label for="per_page">Per Page</label>
                        <input type="number" name="per_page" id="per_page" class="form-control" value="15" min="1" max="100">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <strong>Summary Statistics</strong>
        </div>
        <div class="card-body">
            <div class="row" id="summary-section">
                <div class="col-md-2 mb-3">
                    <div class="alert alert-info text-center">
                        <h5>Total Loans</h5>
                        <p id="total-loans">0</p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="alert alert-warning text-center">
                        <h5>Total Outstanding</h5>
                        <p id="total-outstanding">0 /=</p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="alert alert-success text-center">
                        <h5>Avg. Repayment</h5>
                        <p id="average-repayment-time">0 days</p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="alert alert-danger text-center">
                        <h5>Default Rate</h5>
                        <p id="default-rate">0%</p>
                    </div>
                </div>
                <div class="col-md-2 mb-3">
                    <div class="alert alert-secondary text-center">
                        <h5>Overdue Count</h5>
                        <p id="overdue-count">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insights -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <strong>Actionable Insights</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- High Risk Loans -->
                <div class="col-md-6 mb-3">
                    <h5>High Risk Loans</h5>
                    <ul id="high-risk-loans" class="list-group">
                        <li class="list-group-item">No high-risk loans found.</li>
                    </ul>
                </div>
                <!-- Top Agents -->
                <div class="col-md-6 mb-3">
                    <h5>Top Agents</h5>
                    <ul id="top-agents" class="list-group">
                        <li class="list-group-item">No top agents found.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <strong>Loan Trends</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Loan Status Distribution Chart -->
                <div class="col-md-6 mb-3">
                    <canvas id="loanStatusChart"></canvas>
                </div>
                <!-- Loan Aging Buckets Chart -->
                <div class="col-md-6 mb-3">
                    <canvas id="loanAgingChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Loan Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <strong>Loan Details</strong>
        </div>
        <div class="card-body" id="loan-table">
            {{-- Initial Placeholder --}}
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading loans...</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let loanStatusChart, loanAgingChart;
        let fetchTimer = null;

        // Initialize Charts on Page Load
        initializeCharts();

        // Listen for filter changes with debounce
        const filterForm = document.getElementById('filter-form');
        filterForm.addEventListener('change', function() {
            clearTimeout(fetchTimer);
            fetchTimer = setTimeout(() => {
                fetchData();
            }, 400);
        });

        // Handle Pagination Clicks
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination a')) {
                e.preventDefault();
                const url = e.target.closest('.pagination a').getAttribute('href');
                if (url) {
                    const page = url.split('page=')[1];
                    fetchData(page);
                }
            }
        });

        // Initial Data Fetch
        fetchData();

        /**
         * Fetch loan analysis data via AJAX.
         * @param {number} page - The page number for pagination.
         */
        function fetchData(page = 1) {
            const formData = new FormData(filterForm);
            formData.append('page', page);

            let dataObj = {};
            formData.forEach((value, key) => {
                dataObj[key] = value;
            });

            $.ajax({
                url: "{{ route('admin.loan.analysis.data') }}",
                method: 'GET',
                data: dataObj,
                dataType: 'json',
                beforeSend: function() {
                    // Show loading spinner in loan table
                    $('#loan-table').html(`
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading loans...</span>
                            </div>
                        </div>
                    `);
                    // Clear previous errors
                    $('#loan-analysis-errors').html('');
                },
                success: function(response) {
                    // Update Loan Table
                    $('#loan-table').html(response.html);

                    // Update Summary Statistics
                    updateSummary(response.summary);

                    // Update Insights
                    updateInsights(response.insights);

                    // Update Charts
                    updateCharts(response.charts);
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    if (xhr.status === 422) {
                        displayValidationErrors(xhr.responseJSON.errors);
                    } else {
                        $('#loan-table').html('<div class="alert alert-danger">Failed to load data. Please try again later.</div>');
                    }
                }
            });
        }

        /**
         * Display validation errors to the user.
         * @param {object} errors - The validation errors returned from the server.
         */
        function displayValidationErrors(errors) {
            let errorHtml = '<div class="alert alert-danger"><ul>';
            $.each(errors, function(key, messages) {
                $.each(messages, function(index, message) {
                    errorHtml += `<li>${message}</li>`;
                });
            });
            errorHtml += '</ul></div>';
            $('#loan-analysis-errors').html(errorHtml);
        }

        /**
         * Update the summary statistics section.
         * @param {object} summary - The summary data.
         */
        function updateSummary(summary) {
            $('#total-loans').text(summary.total_loans);
            $('#total-outstanding').text(`${Number(summary.total_outstanding).toLocaleString()} /=`);
            $('#average-repayment-time').text(`${Number(summary.average_repayment_time).toFixed(2)} days`);
            $('#default-rate').text(`${Number(summary.default_rate).toFixed(2)}%`);
            $('#overdue-count').text(summary.overdue_count);
        }

        /**
         * Update the insights section.
         * @param {object} insights - The insights data.
         */
        function updateInsights(insights) {
            // High-Risk Loans 
            let hrHtml = '';
            if (insights.high_risk_loans.length > 0) {
                insights.high_risk_loans.forEach(loan => {
                    const clientName = loan.client ? loan.client.name
                      : 'N/A';
                    hrHtml += `
                        <li class="list-group-item">
                            <strong>ID:</strong> ${loan.id} | 
                            <strong>TRX:</strong> ${loan.trx} | 
                            <strong>Client:</strong> ${clientName} | 
                            <strong>Amount:</strong> ${Number(loan.amount).toLocaleString()} /= 
                        </li>
                    `;
                });
            } else {
                hrHtml = '<li class="list-group-item">No high-risk loans found.</li>';
            }
            $('#high-risk-loans').html(hrHtml);

            // Top Agents
            let taHtml = '';
            if (insights.top_agents.length > 0) {
                insights.top_agents.forEach(agentRec => {
                    const agentName = agentRec.agent ? `${agentRec.agent.f_name} ${agentRec.agent.l_name}` : 'N/A';
                    taHtml += `
                        <li class="list-group-item">
                            <strong>Agent:</strong> ${agentName} | 
                            <strong>Running Loans:</strong> ${agentRec.total}
                        </li>
                    `;
                });
            } else {
                taHtml = '<li class="list-group-item">No top agents found.</li>';
            }
            $('#top-agents').html(taHtml);
        }

        /**
         * Initialize Chart.js charts.
         */
        function initializeCharts() {
            // Loan Status Distribution Chart
            const ctxStatus = document.getElementById('loanStatusChart').getContext('2d');
            loanStatusChart = new Chart(ctxStatus, {
                type: 'pie',
                data: {
                    labels: [], // To be updated via AJAX
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            '#007bff',
                            '#28a745',
                            '#ffc107',
                            '#dc3545',
                            '#6f42c1'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Loan Status Distribution'
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });

            // Loan Aging Buckets Chart
            const ctxAging = document.getElementById('loanAgingChart').getContext('2d');
            loanAgingChart = new Chart(ctxAging, {
                type: 'bar',
                data: {
                    labels: [], // To be updated via AJAX
                    datasets: [{
                        label: 'Number of Loans',
                        data: [],
                        backgroundColor: '#17a2b8',
                        borderColor: '#138496',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Loan Aging Buckets'
                        },
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision:0
                            },
                            title: {
                                display: true,
                                text: 'Number of Loans'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Aging Buckets'
                            }
                        }
                    }
                }
            });
        }

        /**
         * Update Chart.js charts with new data.
         * @param {object} charts - The chart data.
         */
        function updateCharts(charts) {
            // Update Loan Status Chart
            loanStatusChart.data.labels = Object.keys(charts.statusData);
            loanStatusChart.data.datasets[0].data = Object.values(charts.statusData);
            loanStatusChart.update();

            // Update Loan Aging Buckets Chart
            loanAgingChart.data.labels = Object.keys(charts.agingBuckets);
            loanAgingChart.data.datasets[0].data = Object.values(charts.agingBuckets);
            loanAgingChart.update();
        }
    });
</script>
@endpush
