@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
    <h1>Loan Advances</h1>
    <div class="alert alert-info">
        <strong>Note:</strong> Below are clients who have paid more than the due amount and now have advance installments credited.
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form id="advance-filter-form" class="form-inline">
                <div class="form-group mr-2">
                    <label class="mr-2">Search:</label>
                    <input type="text" id="advance-search-input" class="form-control" placeholder="Client/Phone/Loan TXN">
                </div>
                <button type="button" id="advance-filter-btn" class="btn btn-primary">Filter</button>
                <button type="button" id="advance-reset-filter" class="btn btn-secondary ml-2">Reset</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="loan-advances-table" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Phone</th>
                        <th>Loan TXN</th>
                        <th>Total Advance Amount</th>
                        <th>Remaining Advance Amount</th>
                        <th>Total Installments</th>
                        <th>Remaining Installments</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
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

<script>
$(document).ready(function(){
    var table = $('#loan-advances-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 20,
        ajax: {
            url: "{{ route('admin.loan-advances.data') }}",
            data: function(d) {
                var searchValue = $('#advance-search-input').val();
                if(searchValue) {
                    d.search = d.search || {};
                    d.search.value = searchValue;
                }
            }
        },
        columns: [
            { data: 'client_name', name: 'client_name' },
            { data: 'client_phone', name: 'client_phone' },
            { data: 'loan_transaction_id', name: 'loan_transaction_id' },
            {
                data: 'total_advance_amount',
                render: function(data){ return 'UGX '+parseFloat(data).toFixed(2); }
            },
            {
                data: 'remaining_advance_amount',
                render: function(data){ return 'UGX '+parseFloat(data).toFixed(2); }
            },
            { data: 'total_installments', name: 'total_installments' },
            { data: 'remaining_installments', name: 'remaining_installments' },
            {
                data: null,
                orderable: false,
                searchable: false,
                render: function(row){
                    var clientId = row.client_id;
                    var loanId = row.loan_id;
                    var viewProfileUrl = "/admin/clients/" + clientId;
                    var payLoanUrl = "/admin/loans/pay?client_id=" + clientId;
                    return `
                        <a href="${viewProfileUrl}" class="btn btn-sm btn-info">View Profile</a>
                        <a href="${payLoanUrl}" class="btn btn-sm btn-success ml-2">Manage Loan</a>
                    `;
                }
            }
        ]
    });

    $('#advance-filter-btn').on('click', function(){
        table.draw();
    });

    $('#advance-reset-filter').on('click', function(){
        $('#advance-search-input').val('');
        table.draw();
    });
});
</script>
@endpush
