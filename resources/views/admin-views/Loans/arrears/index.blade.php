@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <h1 class="mb-4">Loan Arrears Management</h1>

    <div class="alert alert-info">
        <strong>Note:</strong> Before 8 AM, all running loans are considered in arrears by default. After 8 AM, only those with overdue installments are shown.
    </div>

    <!-- SUMMARY ROW (Totals, # of Clients, etc.) -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Arrears Summary</h5>
        </div>
        <div class="card-body" id="arrears-summary">
            <!-- We'll populate these stats dynamically from AJAX response -->
            <ul class="list-unstyled mb-0">
                <li><strong>Number of Clients in Arrears:</strong> <span id="summary-client-count">0</span></li>
                <li><strong>Total Overdue Amount:</strong> UGX <span id="summary-total-overdue">0.00</span></li>
            </ul>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Filter Arrears</h5>
        </div>
        <div class="card-body">
            <form id="filter-form">
                <div class="row">
                    <!-- Search -->
                    <div class="col-md-4 mb-3">
                        <label for="search-client" class="form-label">Search</label>
                        <input 
                            type="text" 
                            id="search-client" 
                            class="form-control" 
                            placeholder="Client name/phone/loan txn"
                        />
                    </div>

                    <!-- Agent dropdown -->
                    <div class="col-md-4 mb-3">
                        <label for="agent-select" class="form-label">Agent</label>
                        <select id="agent-select" class="form-control">
                            <option value="all">All Agents</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}">
                                    {{ $agent->f_name }} {{ $agent->l_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Overdue months (buttons) -->
                    <div class="col-md-4 mb-3">
                        <label class="form-label d-block">Quick Filter (Overdue Months)</label>
                        <div class="btn-group" role="group">
                            <button 
                                type="button" 
                                class="btn btn-outline-secondary overdue-btn" 
                                data-months="1"
                            >
                                1 Month
                            </button>
                            <button 
                                type="button" 
                                class="btn btn-outline-secondary overdue-btn" 
                                data-months="2"
                            >
                                2 Months
                            </button>
                            <button 
                                type="button" 
                                class="btn btn-outline-secondary overdue-btn" 
                                data-months="3"
                            >
                                3 Months
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Additional Filters (optional) -->
                    <!-- e.g. Start/End date, Min Overdue, etc. -->
                    <div class="col-md-12 d-flex align-items-end justify-content-start">
                        <div>
                            <button 
                                type="button" 
                                id="filter-btn" 
                                class="btn btn-primary mr-2"
                            >
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <button 
                                type="button" 
                                id="reset-filter" 
                                class="btn btn-secondary"
                            >
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Arrears Table Card -->
    <div class="card shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0">Arrears Table</h5>
        </div>
        <div class="card-body">
            <table 
                id="arrears-table" 
                class="table table-bordered table-hover table-striped table-sm" 
                style="width:100%"
            >
                <thead class="thead-dark">
                    <tr>
                        <th>Client Name</th>
                        <th>Phone</th>
                        <th>Earliest Overdue Date</th>
                        <th>Missed Installments</th>
                        <th>Overdue Amount</th>
                        <th>Client Balance</th>
                        <th style="min-width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <!-- Optional: a <tfoot> if needed for a per-page sum. -->
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<!-- DataTables CSS & JS -->
<link 
    rel="stylesheet" 
    href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css"
/>
<link 
    rel="stylesheet" 
    href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css"
/>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script 
    src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js">
</script>
<script 
    src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.flash.min.js">
</script>

<!-- pdfmake and vfs_fonts for PDF export -->
<script 
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js">
</script>
<script 
    src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js">
</script>

<!-- Print and PDF buttons -->
<script 
    src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js">
</script>
<script 
    src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.pdfHtml5.min.js">
</script>

<!-- Icons (optional, for button icons) -->
<script 
    src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js">
</script>

<!-- Custom Initialization Script -->
<script>
$(document).ready(function() {
    var overdueMonths = null; // Will store 1, 2, or 3 if those buttons are clicked

    var table = $('#arrears-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20, // Show 20 per page
        ajax: {
            url: "{{ route('admin.loan-arrears.data') }}",
            data: function (d) {
                // Overwrite DataTables' default search with our custom input
                var searchValue = $('#search-client').val();
                if (searchValue) {
                    d.search = d.search || {};
                    d.search.value = searchValue;
                }

                // Agent filter
                var agentId = $('#agent-select').val();
                if (agentId) {
                    d.agent_id = agentId;
                }

                // Overdue months filter
                if (overdueMonths) {
                    d.overdue_months = overdueMonths;
                }
            }
        },
        columns: [
            { data: 'client_name', name: 'client_name' },
            { data: 'client_phone', name: 'client_phone' },
            {
                data: 'earliest_overdue_date',
                name: 'earliest_overdue_date',
                render: function(data) {
                    return data 
                        ? new Date(data).toLocaleDateString() 
                        : '';
                }
            },
            { 
                data: 'total_overdue_installments', 
                name: 'total_overdue_installments' 
            },
            {
                data: 'total_overdue_amount',
                name: 'total_overdue_amount',
                render: function(data) {
                    var val = parseFloat(data || 0).toFixed(2);
                    return 'UGX ' + val;
                }
            },
            {
                data: 'client_balance',
                name: 'client_balance',
                render: function(data) {
                    if (!data) data = 0;
                    var val = parseFloat(data).toFixed(2);
                    return 'UGX ' + val;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data) {
                    var clientId = data.client_id;
                    var loanId   = data.loan_id;
                    
                    // Adjust these URLs as per your routes
                    var viewProfileUrl  = "/admin/clients/" + clientId;
                    var payLoanUrl      = "/admin/loans/pay?client_id=" + clientId;
                    var sendReminderUrl = "/admin/loans/reminder?client_id=" + clientId;

                    return `
                        <div class="btn-group" role="group">
                            <a href="${viewProfileUrl}" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="${payLoanUrl}" class="btn btn-sm btn-success">
                                <i class="fas fa-money-bill-wave"></i> Pay
                            </a>
                            <a href="${sendReminderUrl}" class="btn btn-sm btn-warning">
                                <i class="fas fa-bell"></i> Remind
                            </a>
                        </div>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                titleAttr: 'Export to PDF',
                className: 'btn btn-danger btn-sm',
                exportOptions: {
                    // export ALL pages, not just the current page
                    modifier: {
                        page: 'all'
                    }
                }
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Print',
                titleAttr: 'Print Table',
                className: 'btn btn-secondary btn-sm',
                exportOptions: {
                    // export ALL pages, not just the current page
                    modifier: {
                        page: 'all'
                    }
                }
            }
        ],
        // Listen for the AJAX response so we can load summary data
        drawCallback: function(settings) {
            // 'settings.json' = the JSON response from the controller
            var response = settings.json;

            if (response && response.summary) {
                $('#summary-client-count').text(response.summary.client_count || 0);
                var totalOverdue = parseFloat(response.summary.total_overdue_amount || 0).toFixed(2);
                $('#summary-total-overdue').text(totalOverdue);
            } else {
                // default to 0 if not provided
                $('#summary-client-count').text(0);
                $('#summary-total-overdue').text('0.00');
            }
        }
    });

    // Quick filter (Overdue Months) buttons
    $('.overdue-btn').on('click', function() {
        overdueMonths = $(this).data('months');
        table.draw();
    });

    // Filter button
    $('#filter-btn').on('click', function() {
        overdueMonths = null; // reset if you want
        table.draw();
    });

    // Reset filter
    $('#reset-filter').on('click', function() {
        $('#search-client').val('');
        $('#agent-select').val('all');
        overdueMonths = null;
        table.draw();
    });
});
</script>
@endpush
