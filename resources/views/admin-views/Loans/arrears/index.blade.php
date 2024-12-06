@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <h1>Loan Arrears Management</h1>
    <div class="alert alert-info">
        <strong>Note:</strong> Before 8 AM, all running loans are considered in arrears by default. After 8 AM, only those with overdue installments are shown.
    </div>

    <!-- Optional: Add filters like date range or agent filter -->
    <div class="card mb-4">
        <div class="card-body">
            <!-- Example filter form -->
            <form id="filter-form" class="form-inline">
                <div class="form-group mr-2">
                    <label for="search-client" class="mr-2">Search:</label>
                    <input type="text" id="search-client" class="form-control" placeholder="Client name/phone/loan txn">
                </div>
                <button type="button" id="filter-btn" class="btn btn-primary">Filter</button>
                <button type="button" id="reset-filter" class="btn btn-secondary ml-2">Reset</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="arrears-table" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Phone</th>
                        <th>Loan TXN</th>
                        <th>Earliest Overdue Date</th>
                        <th>Missed Installments</th>
                        <th>Overdue Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <!-- Optional: Add a footer to sum total overdue amounts if desired -->
            </table>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.dataTables.min.css"/>
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.flash.min.js"></script>
<!-- pdfmake and vfs_fonts needed for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<!-- print and pdfHtml5 buttons -->
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.pdfHtml5.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#arrears-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20, // Show 20 per page
        ajax: {
            url: "{{ route('admin.loan-arrears.data') }}",
            data: function (d) {
                d.search = d.search || {};
                // If you want to pass custom search input
                var searchValue = $('#search-client').val();
                if(searchValue) {
                    d.search.value = searchValue;
                }
            }
        },
        columns: [
            { data: 'client_name', name: 'client_name' },
            { data: 'client_phone', name: 'client_phone' },
            { data: 'loan_transaction_id', name: 'loan_transaction_id' },
            {
                data: 'earliest_overdue_date', 
                name: 'earliest_overdue_date',
                render: function(data, type, row) {
                    // Format date if needed
                    return data ? new Date(data).toLocaleDateString() : '';
                }
            },
            { data: 'total_overdue_installments', name: 'total_overdue_installments' },
            {
                data: 'total_overdue_amount',
                name: 'total_overdue_amount',
                render: function(data) {
                    return 'UGX ' + parseFloat(data).toFixed(2); // Format currency
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(data) {
                    var clientId = data.client_id;
                    var loanId = data.loan_id;
                    // Action buttons:
                    // View Profile: adjust route as per your application
                    var viewProfileUrl = "/admin/clients/" + clientId;
                    // Pay Loan: adjust route as per your application
                    var payLoanUrl = "/admin/loans/pay?client_id=" + clientId;
                    // Send Reminder (optional): adjust route
                    var sendReminderUrl = "/admin/loans/reminder?client_id=" + clientId;

                    return `
                        <a href="${viewProfileUrl}" class="btn btn-sm btn-info">View Profile</a>
                        <a href="${payLoanUrl}" class="btn btn-sm btn-success ml-2">Pay Loan</a>
                        <a href="${sendReminderUrl}" class="btn btn-sm btn-warning ml-2">Send Reminder</a>
                    `;
                }
            }
        ],
        dom: 'Bfrtip',
        buttons: [
            'pdfHtml5',
            'print'
        ]
    });

    // Filter button
    $('#filter-btn').on('click', function() {
        table.draw();
    });

    // Reset filter
    $('#reset-filter').on('click', function() {
        $('#search-client').val('');
        table.draw();
    });
});
</script>
@endpush
