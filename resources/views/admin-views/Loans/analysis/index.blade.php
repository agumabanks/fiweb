@extends('layouts.admin.app')

@section('title', 'Comprehensive Loan Analysis')

@push('css_or_js')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.css" />
<style>
    /* Minimal, modern styling */
    .card {
        border-radius: 8px;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    .card-header {
        background-color: #fff;
        font-weight: 600;
        font-size: 1.1rem;
        border-bottom: none;
    }
    .form-control {
        border-radius: 6px;
    }
    .spinner-border {
        width: 2rem;
        height: 2rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">

    <h1 class="mb-4 text-center">Comprehensive Loan Analysis</h1>

    <!-- Error Display Section -->
    <div id="loan-analysis-errors"></div>

    <!-- Filter Card -->
    <div class="card">
        <div class="card-header">
            Filters
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="status" class="fw-bold">Loan Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all">All</option>
                            <option value="pending">Pending</option>
                            <option value="running">Running</option>
                            <option value="paid">Paid</option>
                            <option value="overdue">Overdue</option>
                            <option value="defaulted">Defaulted</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="aging" class="fw-bold">Loan Aging</label>
                        <select name="aging" id="aging" class="form-control">
                            <option value="all">All</option>
                            <option value="30">0-30 Days</option>
                            <option value="60">31-60 Days</option>
                            <option value="90+">61+ Days</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="agent_id" class="fw-bold">Field Agent</label>
                        <select name="agent_id" id="agent_id" class="form-control">
                            <option value="all">All Agents</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">
                                    {{ $agent->f_name }} {{ $agent->l_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="partial_disbursed" class="fw-bold">Partial Disbursed</label>
                        <select name="partial_disbursed" id="partial_disbursed" class="form-control">
                            <option value="">All</option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="renewed" class="fw-bold">Renewed</label>
                        <select name="renewed" id="renewed" class="form-control">
                            <option value="">All</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label for="min_amount" class="fw-bold">Min Principal</label>
                        <input type="number" step="0.01" name="min_amount" id="min_amount" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="max_amount" class="fw-bold">Max Principal</label>
                        <input type="number" step="0.01" name="max_amount" id="max_amount" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="per_page" class="fw-bold">Per Page</label>
                        <input type="number" name="per_page" id="per_page" value="15" min="1" max="100" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <!-- Disbursement Date Range -->
                    <div class="col-md-3 mb-3">
                        <label for="from_disbursement" class="fw-bold">From Disbursed</label>
                        <input type="date" name="from_disbursement" id="from_disbursement" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="to_disbursement" class="fw-bold">To Disbursed</label>
                        <input type="date" name="to_disbursement" id="to_disbursement" class="form-control">
                    </div>

                    <!-- Due Date Range -->
                    <div class="col-md-3 mb-3">
                        <label for="from_due" class="fw-bold">From Due</label>
                        <input type="date" name="from_due" id="from_due" class="form-control">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="to_due" class="fw-bold">To Due</label>
                        <input type="date" name="to_due" id="to_due" class="form-control">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats Card -->
    <div class="card">
        <div class="card-header">
            Summary
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tbody id="summary-body">
                        <tr>
                            <th>Total Loans</th>
                            <td id="sum-total-loans">0</td>
                        </tr>
                        <tr>
                            <th>Total Principal (Money Given)</th>
                            <td id="sum-total-principal">0</td>
                        </tr>
                        <tr>
                            <th>Total Final Amount</th>
                            <td id="sum-total-final">0</td>
                        </tr>
                        <tr>
                            <th>Total Paid So Far</th>
                            <td id="sum-total-paid">0</td>
                        </tr>
                        <tr>
                            <th>Total Outstanding</th>
                            <td id="sum-total-outstanding">0</td>
                        </tr>
                        <tr>
                            <th>Average Repayment Interval (Days)</th>
                            <td id="sum-average-repayment">0</td>
                        </tr>
                        <tr>
                            <th>Default Rate (%)</th>
                            <td id="sum-default-rate">0%</td>
                        </tr>
                        <tr>
                            <th>Overdue Count</th>
                            <td id="sum-overdue-count">0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Loan Table -->
    <div class="card" id="loan-table-card">
        <div class="card-header">
            Detailed Loan Records
        </div>
        <div class="card-body" id="loan-table-container">
            <!-- Initially a loading spinner -->
            <div class="text-center p-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading Sanaa data...</span>
                </div>
                <p class="mt-2">Loading Sanaa ...</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
@endpush

@push('script_2')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let fetchTimer = null;

    const filterForm = document.getElementById('filter-form');

    // Debounce filter changes
    filterForm.addEventListener('change', function() {
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(() => {
            fetchData(1); // always reload from page 1 on filter change
        }, 400);
    });

    // Pagination links
    document.addEventListener('click', function(e) {
        const pageLink = e.target.closest('.pagination a');
        if(pageLink) {
            e.preventDefault();
            const url = pageLink.getAttribute('href');

            // Instead of split('page=')[1], we use a regex approach:
            const pageRegex = /[?&]page=(\d+)/;
            const match = url.match(pageRegex);
            let pageNum = 1;
            if(match && match[1]) {
                pageNum = match[1];
            }
            fetchData(pageNum);
        }
    });

    // Initial data fetch
    fetchData(1);

    function fetchData(page = 1) {
        // Gather filter data
        const formData = new FormData(filterForm);
        formData.append('page', page);

        let dataObj = {};
        formData.forEach((val, key) => { dataObj[key] = val; });

        $.ajax({
            url: "{{ route('admin.loan.analysis.fetch') }}",
            method: 'GET',
            data: dataObj,
            dataType: 'json',
            beforeSend: function() {
                $('#loan-analysis-errors').html('');
                $('#loan-table-container').html(`
                    <div class="text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading Sanaa data...</span>
                        </div>
                        <p class="mt-2">Loading Sanaa ...</p>
                    </div>
                `);
            },
            success: function(response) {
                if(response.errors) {
                    displayValidationErrors(response.errors);
                    return;
                }
                // Insert table
                $('#loan-table-container').html(response.html);

                // Update summary
                updateSummary(response.summary);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                if(xhr.status === 422) {
                    displayValidationErrors(xhr.responseJSON.errors);
                } else {
                    $('#loan-table-container').html(`
                        <div class="alert alert-danger">Failed to load data. Please try again.</div>
                    `);
                }
            }
        });
    }

    function displayValidationErrors(errors) {
        let html = '<div class="alert alert-danger"><ul>';
        for(const field in errors) {
            errors[field].forEach(msg => {
                html += `<li>${msg}</li>`;
            });
        }
        html += '</ul></div>';
        $('#loan-analysis-errors').html(html);
    }

    /* SUMMARY */
    function updateSummary(summary) {
        $('#sum-total-loans').text(summary.total_loans ?? 0);
        $('#sum-total-principal').text(numberFormat(summary.total_principal));
        $('#sum-total-final').text(numberFormat(summary.total_final));
        $('#sum-total-paid').text(numberFormat(summary.total_paid));
        $('#sum-total-outstanding').text(numberFormat(summary.total_outstanding));
        $('#sum-average-repayment').text(summary.average_repayment_time ?? 0);
        $('#sum-default-rate').text((summary.default_rate ?? 0) + '%');
        $('#sum-overdue-count').text(summary.overdue_count ?? 0);
    }

    function numberFormat(val) {
        if(!val) val=0;
        return parseFloat(val).toLocaleString();
    }
});
</script>
@endpush
