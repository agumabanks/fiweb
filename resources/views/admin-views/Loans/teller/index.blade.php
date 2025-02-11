@extends('layouts.admin.app')

@section('title','Teller Dashboard')

@push('css_or_js')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <!-- Custom styling for a minimal, elegant look -->
    <style>
        body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            background-color: #fafafa;
            color: #333;
        }
        h2 {
            font-weight: 300;
            margin-bottom: 30px;
        }
        label {
            font-weight: 300;
        }
        .form-control {
            border-radius: 4px;
            border: 1px solid #ccc;
            padding: 8px 12px;
            font-size: 14px;
        }
        .table {
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }
        .btn-success {
            background-color: #5cb85c;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            font-weight: 500;
        }
        .btn-success:hover {
            background-color: #4cae4c;
        }
        .modal-header, .modal-footer {
            border: none;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 30px 15px;">
    <h2 class="text-center text-dark">Teller Dashboard</h2>

    <div class="row mb-4">
        <!-- Agent Filter -->
        <div class="col-md-3 mb-2">
            <label for="agentFilter">Agent</label>
            <select id="agentFilter" class="form-control">
                <option value="all">All Agents</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">
                        {{ $agent->f_name }} {{ $agent->l_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <!-- From Date -->
        <div class="col-md-3 mb-2">
            <label for="fromDate">From Date</label>
            <input type="date" id="fromDate" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>
        <!-- To Date -->
        <div class="col-md-3 mb-2">
            <label for="toDate">To Date</label>
            <input type="date" id="toDate" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>
        <!-- Global Search -->
        <div class="col-md-3 mb-2">
            <label for="clientSearch">Search Client</label>
            <input type="text" id="clientSearch" class="form-control" placeholder="Name, phone or TRX">
        </div>
    </div>

    <table class="table table-bordered" id="tellerTable" style="width: 100%;">
        <thead>
            <tr>
                <th>Client</th>
                <th>Phone</th>
                <th>Loan TRX</th>
                <th>Installment Amt</th>
                <th>Installment Date</th>
                <th>Client Balance</th>
                <th style="min-width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>

    <!-- Payment Modal -->
    <div class="modal fade" id="payModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form id="payLoanForm">
          @csrf
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Process Payment</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="installment_id" id="installment_id">
                <input type="hidden" name="client_id" id="pay_client_id">
                <div class="form-group">
                    <label>Amount</label>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Note</label>
                    <input type="text" name="note" class="form-control" placeholder="Optional">
                </div>
                <p id="clientInfo" class="small text-muted"></p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-success">Submit Payment</button>
            </div>
          </div>
        </form>
      </div>
    </div>
</div>
@endsection

@push('script')
    <!-- jQuery and DataTables JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <!-- Bootstrap JS for modal (if not already loaded) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function(){

        // Initialize DataTable with serverSide processing
        let table = $('#tellerTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            ajax: {
                url: '{{ route('admin.teller.data') }}',
                type: 'POST',
                data: function(d){
                    d._token    = '{{ csrf_token() }}';
                    d.agent_id  = $('#agentFilter').val();
                    d.from_date = $('#fromDate').val();
                    d.to_date   = $('#toDate').val();
                }
            },
            columns: [
                { data: 'client_name', name: 'client_name' },
                { data: 'client_phone', name: 'client_phone' },
                { data: 'loan_trx', name: 'loan_trx' },
                { data: 'install_amount', name: 'install_amount' },
                { data: 'installment_date', name: 'installment_date' },
                { data: 'client_balance', name: 'client_balance' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data){
                        return `<button class="btn btn-sm btn-success payBtn"
                                    data-installment-id="${data.installment_id}"
                                    data-client-id="${data.client_id}"
                                    data-client-name="${data.client_name}"
                                    data-client-phone="${data.client_phone}"
                                    data-client-balance="${data.client_balance}">
                                    Pay
                                </button>`;
                    }
                }
            ],
            language: {
                processing: "<span style='font-size: 1rem;'>Processing...</span>",
                emptyTable: "No installments available for the selected filters."
            }
        });

        // Reload table on filter change (agent and date fields)
        $('#agentFilter, #fromDate, #toDate').on('change', function(){
            table.ajax.reload(null, false);
        });

        // Global search (tied to our search field)
        $('#clientSearch').on('keyup', function(){
            table.search(this.value).draw();
        });

        // Open modal when "Pay" button is clicked
        $(document).on('click', '.payBtn', function(){
            let installmentId = $(this).data('installment-id');
            let clientId      = $(this).data('client-id');
            let clientName    = $(this).data('client-name');
            let clientPhone   = $(this).data('client-phone');
            let balance       = $(this).data('client-balance');

            $('#installment_id').val(installmentId);
            $('#pay_client_id').val(clientId);
            $('#clientInfo').text(`${clientName} | ${clientPhone} | Balance: ${balance}`);
            $('#payModal').modal('show');
        });

        // Process payment form via AJAX
        $('#payLoanForm').on('submit', function(e){
            e.preventDefault();
            let formData = $(this).serialize();

            $.ajax({
                url: '{{ route('admin.teller.payLoan') }}',
                method: 'POST',
                data: formData,
                success: function(res){
                    alert(res.message || 'Payment processed successfully!');
                    $('#payModal').modal('hide');
                    table.ajax.reload(null, false);
                },
                error: function(err){
                    console.error(err);
                    alert(err.responseJSON?.error || 'Payment failed. Please try again.');
                }
            });
        });
    });
    </script>
@endpush
