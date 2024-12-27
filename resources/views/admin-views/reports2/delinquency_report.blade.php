@extends('layouts.admin.app')

@section('title', translate('Delinquency Metrics and Aging Report'))

@section('content')
<div class="container-fluid py-4" style="background: linear-gradient(135deg, #f9fafb 0%, #ffffff 100%); min-height: 100vh;">
    <!-- Header with Export Buttons -->
    <div class="row mb-4">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <h2 class="mb-3 mb-md-0 fw-semibold">{{ translate('Delinquency Metrics and Aging Report') }}</h2>
            <div class="d-flex flex-wrap gap-2">
                <button id="exportPDF" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> {{ translate('Export PDF') }}
                </button>
                <button id="exportExcel" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> {{ translate('Export Excel') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0 fw-semibold">{{ translate('Filter Report') }}</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label fw-semibold">{{ translate('Start Date') }}</label>
                    <input type="date" class="form-control shadow-none" id="start_date" name="start_date" required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label fw-semibold">{{ translate('End Date') }}</label>
                    <input type="date" class="form-control shadow-none" id="end_date" name="end_date" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">{{ translate('Generate Report') }}</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row mb-4" id="summaryMetrics" style="display: none;">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card text-white bg-primary h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title fw-semibold text-white">{{ translate('Total Loans') }}</h5>
                    <h2 id="totalLoans" class="fw-bold text-white">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-white bg-danger h-100 border-0 shadow-sm">
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <h5 class="card-title fw-semibold text-white">{{ translate('Delinquent Loans') }}</h5>
                    <h2 id="totalDelinquent" class="fw-bold text-white">0</h2>
                </div>
            </div>
        </div>
    </div>

   

    <!-- Detailed Loans Table -->
    <div class="card mb-4 border-0 shadow-sm" id="detailedTables" style="display: none;">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0 fw-semibold">{{ translate('Delinquent Loans Details') }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="fw-semibold">{{ translate('Client Name') }}</th>
                            <th class="fw-semibold">{{ translate('Client Phone') }}</th>
                            <th class="fw-semibold">{{ translate('Loan Amount') }}</th>
                            <th class="fw-semibold">{{ translate('Due Date') }}</th>
                            <th class="fw-semibold">{{ translate('Days Overdue') }}</th>
                            <th class="fw-semibold">{{ translate('Agent') }}</th>
                            <th class="fw-semibold">{{ translate('Follow-Up Status') }}</th>
                        </tr>
                    </thead>
                    <tbody id="delinquentLoansTableBody">
                        <tr id="noDataRow" style="display: none;">
                            <td colspan="7" class="text-center text-muted">{{ translate('No delinquent loans found for the selected date range.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

     <!-- Charts Section -->
     <div class="row mb-4" id="chartsSection" style="display: none;">
        <!-- Aging Distribution Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column" style="min-height: 300px;">
                    <h5 class="card-title mb-4 fw-semibold">{{ translate('Aging Distribution') }}</h5>
                    <canvas id="agingChart" class="flex-grow-1"></canvas>
                </div>
            </div>
        </div>
        <!-- Delinquency Trends Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column" style="min-height: 300px;">
                    <h5 class="card-title mb-4 fw-semibold">{{ translate('Delinquency Trends') }}</h5>
                    <canvas id="trendChart" class="flex-grow-1"></canvas>
                </div>
            </div>
        </div>
        <!-- Agent Performance Chart -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex flex-column" style="min-height: 300px;">
                    <h5 class="card-title mb-4 fw-semibold">{{ translate('Agent Performance') }}</h5>
                    <canvas id="agentChart" class="flex-grow-1"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script_2')
<!-- Include Chart.js, Bootstrap Icons, SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    /* Custom spinner overlay */
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: none; /* Hidden by default */
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    #loadingOverlay .spinner-border {
        width: 4rem;
        height: 4rem;
    }
    #loadingOverlay .brand-text {
        margin-top: 1rem;
        font-size: 1.25rem;
        font-weight: 600;
        color: #0d6efd;
        font-family: 'Inter', sans-serif;
    }
</style>

<div id="loadingOverlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Sanaa ...</span>
    </div>
    <div class="brand-text">Sanaa ...</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const summaryMetrics = document.getElementById('summaryMetrics');
    const chartsSection = document.getElementById('chartsSection');
    const detailedTables = document.getElementById('detailedTables');
    const noDataRow = document.getElementById('noDataRow');
    const loadingOverlay = document.getElementById('loadingOverlay');

    let agingChart, trendChart, agentChart;

    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        loadingOverlay.style.display = 'flex';

        const formData = new FormData(filterForm);
        const startDate = formData.get('start_date');
        const endDate = formData.get('end_date');

        fetch('{{ route("admin.reports.delinquency.fetch") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            loadingOverlay.style.display = 'none';
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.json();
        })
        .then(data => {
            if (data.errors) {
                let errorMessages = '';
                for (let key in data.errors) {
                    errorMessages += `<p>${data.errors[key][0]}</p>`;
                }
                Swal.fire({
                    icon: 'error',
                    title: '{{ translate("Validation Error") }}',
                    html: errorMessages,
                });
                return;
            }

            if (data.loans.length === 0) {
                summaryMetrics.style.display = 'none';
                chartsSection.style.display = 'none';
                detailedTables.style.display = 'block';
                noDataRow.style.display = 'table-row';
                document.getElementById('delinquentLoansTableBody').innerHTML = '';
                return;
            } else {
                noDataRow.style.display = 'none';
            }

            summaryMetrics.style.display = 'flex';
            chartsSection.style.display = 'block';
            detailedTables.style.display = 'block';

            document.getElementById('totalLoans').innerText = data.summary.totalLoans;
            document.getElementById('totalDelinquent').innerText = data.summary.totalDelinquent;

            renderAgingChart(data.agingData);
            renderTrendChart(data.trends);
            renderAgentChart(data.agentPerformance);
            populateDelinquentLoansTable(data.loans);
        })
        .catch(error => {
            loadingOverlay.style.display = 'none';
            console.error('Error:', error);
            let errorMessage = '{{ translate("An unexpected error occurred while generating the report.") }}';
            if (error.message) {
                errorMessage = error.message;
            }
            Swal.fire({
                icon: 'error',
                title: '{{ translate("Error") }}',
                text: errorMessage,
            });
        });
    });

    document.getElementById('exportPDF').addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        window.open(`{{ route('admin.reports.delinquency.export.pdf') }}?start_date=${startDate}&end_date=${endDate}`, '_blank');
    });

    document.getElementById('exportExcel').addEventListener('click', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        window.open(`{{ route('admin.reports.delinquency.export.excel') }}?start_date=${startDate}&end_date=${endDate}`, '_blank');
    });

    function renderAgingChart(data) {
        const ctx = document.getElementById('agingChart').getContext('2d');
        if (agingChart) agingChart.destroy();
        agingChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56'],
                    hoverBackgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 20, padding: 15 }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                if (context.parsed !== null) label += context.parsed;
                                return label;
                            }
                        }
                    }
                }
            },
        });
    }

    function renderTrendChart(data) {
        const ctx = document.getElementById('trendChart').getContext('2d');
        if (trendChart) trendChart.destroy();
        trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => item.month),
                datasets: [{
                    label: '{{ translate("Delinquent Loans") }}',
                    data: data.map(item => item.count),
                    fill: true,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: '#36A2EB',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true, position: 'top', labels: { boxWidth: 20, padding: 15 } },
                    tooltip: { mode: 'index', intersect: false },
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: '{{ translate("Number of Loans") }}' },
                        ticks: { precision:0 }
                    },
                    x: {
                        title: { display: true, text: '{{ translate("Month") }}' }
                    }
                }
            },
        });
    }

    function renderAgentChart(data) {
        const ctx = document.getElementById('agentChart').getContext('2d');
        if (agentChart) agentChart.destroy();
        agentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.agent),
                datasets: [
                    {
                        label: '{{ translate("Delinquent Loans") }}',
                        data: data.map(item => item.delinquent),
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: '#36A2EB',
                        borderWidth: 1
                    },
                    {
                        label: '{{ translate("Total Loans") }}',
                        data: data.map(item => item.total),
                        backgroundColor: 'rgba(255, 206, 86, 0.7)',
                        borderColor: '#FFCE56',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 20, padding: 15 } },
                    tooltip: { mode: 'index', intersect: false },
                },
                interaction: { mode: 'nearest', axis: 'x', intersect: false },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: '{{ translate("Number of Loans") }}' },
                        ticks: { precision:0 }
                    },
                    x: {
                        title: { display: true, text: '{{ translate("Agent") }}' }
                    }
                }
            },
        });
    }

    function populateDelinquentLoansTable(loans) {
        const tableBody = document.getElementById('delinquentLoansTableBody');
        tableBody.innerHTML = '';

        loans.forEach(loan => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${loan.client_name}</td>
                <td><i class="bi bi-telephone-fill"></i> ${loan.client_phone}</td>
                <td>UGX ${new Intl.NumberFormat().format(loan.loan_amount)}</td>
                <td>${loan.due_date}</td>
                <td>${loan.days_overdue}</td>
                <td>${loan.agent_name}</td>
                <td>${loan.follow_up_status}</td>
            `;
            tableBody.appendChild(row);
        });

        if (loans.length === 0) {
            noDataRow.style.display = 'table-row';
        } else {
            noDataRow.style.display = 'none';
        }
    }

    // Set default date range (last 30 days)
    const today = new Date().toISOString().split('T')[0];
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
    document.getElementById('start_date').value = thirtyDaysAgo.toISOString().split('T')[0];
    document.getElementById('end_date').value = today;

    // Auto-submit filter form on page load
    filterForm.dispatchEvent(new Event('submit'));
});
</script>
@endpush
