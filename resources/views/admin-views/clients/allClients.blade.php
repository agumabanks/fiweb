@extends('layouts.admin.app')

@section('title', translate('All Clients'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="card m-4">
    <!-- Page Title -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 m-3">
        <h2 class="my-4">{{ $pageTitle }}</h2>
    </div>

    <!-- Filter Form -->
    <div class="card-header __wrap-gap-10 m-2">
        <div class="d-flex align-items-center gap-2">
            <h5 class="card-header-title">{{ translate('Clients Table') }}</h5>
        </div>
        <!-- Start of Filter Form -->
        <form id="client-filter-form" class="d-flex flex-wrap align-items-end gap-2">
            <!-- Search Input -->
            <div class="form-group">
                <input id="search-input" type="search" name="search"
                       class="form-control"
                       placeholder="{{ translate('Search by Name') }}" aria-label="Search"
                       value="{{ request('search') }}" autocomplete="off">
            </div>

            <!-- Agent Filter -->
            <div class="form-group">
                <select id="agent-select" name="agent_id" class="form-control">
                    <option value="all">{{ translate('All Agents') }}</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                            {{ $agent->f_name . ' ' . $agent->l_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div class="form-group">
                <select id="payment-status-select" name="payment_status" class="form-control">
                    <option value="all">{{ translate('All Payment Statuses') }}</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>{{ translate('Paid') }}</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>{{ translate('Unpaid') }}</option>
                </select>
            </div>

            <!-- Client Status Filter -->
            <div class="form-group">
                <select id="client-status-select" name="status" class="form-control">
                    <option value="all">{{ translate('All Client Statuses') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ translate('Active') }}</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>{{ translate('Inactive') }}</option>
                    <!-- Add more statuses if needed -->
                </select>
            </div>

            <!-- Paid Today Filter -->
            <div class="form-group">
                <select id="paid-today-select" name="paid_today" class="form-control">
                    <option value="all" {{ request('paid_today') == 'all' ? 'selected' : '' }}>{{ translate('All Payment Today Statuses') }}</option>
                    <option value="paid" {{ request('paid_today') == 'paid' ? 'selected' : '' }}>{{ translate('Paid Today') }}</option>
                    <option value="not_paid" {{ request('paid_today') == 'not_paid' ? 'selected' : '' }}>{{ translate('Not Paid Today') }}</option>
                </select>
            </div>

            <!-- Date Range Filter -->
            <div class="form-group">
                <input type="date" id="from-date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="form-group">
                <input type="date" id="to-date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <!-- Export Button -->
            <div class="form-group">
                <button type="button" id="export-button" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> {{ translate('Export to Excel') }}
                </button>
            </div>

            <!-- Export to PDF Button -->
            <div class="form-group">
                <button type="button" id="download-pdf-button" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> {{ translate('Download PDF') }}
                </button>
            </div>

            <!-- Add Client Button -->
            <div class="form-group">
                <a href="{{ route('admin.client.create') }}" class="btn btn-primary">
                    <i class="tio-add"></i> {{ translate('Add') }} {{ translate('Client') }}
                </a>
            </div>
        </form>
        <!-- End of Filter Form -->
    </div>

    <!-- Clients Table Container -->
    <div id="clients-table-container">
        @include('admin-views.clients.partials.clients-table', [
            'clientsData' => $clientsData,
            'emptyMessage' => $emptyMessage,
            'clients' => $clients
        ])
    </div>
</div>
@endsection

@push('script')
<!-- Include jQuery if not already included -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endpush

@push('script_2')
<script>
$(document).ready(function() {
    // Get CSRF token from meta tag
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Function to fetch and update clients table
    function fetchClients(page = 1) {
        // Get filter values
        var search = $('#search-input').val();
        var agentId = $('#agent-select').val();
        var paymentStatus = $('#payment-status-select').val();
        var status = $('#client-status-select').val();
        var paidToday = $('#paid-today-select').val();
        var fromDate = $('#from-date').val();
        var toDate = $('#to-date').val();

        // Prepare data
        var data = {
            search: search,
            agent_id: agentId,
            payment_status: paymentStatus,
            status: status,
            paid_today: paidToday,
            from_date: fromDate,
            to_date: toDate,
            page: page,
        };

        // AJAX request
        $.ajax({
            url: '{{ route('admin.allclients') }}',
            type: 'GET',
            data: data,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                // You can show a loading spinner here
            },
            success: function(response) {
                // Update the table container with new data
                $('#clients-table-container').html(response.html);
            },
            error: function(xhr, status, error) {
                // Handle errors
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            },
            complete: function() {
                // Hide loading spinner if you showed one
            }
        });
    }

    // Event listeners
    $('#search-input').on('keyup', function() {
        // Implement debouncing
        clearTimeout($.data(this, 'timer'));
        var wait = setTimeout(function() {
            fetchClients();
        }, 500); // Wait 500ms after the last keystroke
        $(this).data('timer', wait);
    });

    $('#agent-select, #payment-status-select, #client-status-select, #from-date, #to-date, #paid-today-select').on('change', function() {
        fetchClients();
    });

    // Handle pagination click
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page !== undefined) {
            fetchClients(page);
        }
    });

    // Handle download PDF button click
    $('#download-pdf-button').on('click', function() {
        // Build the query parameters with current filters
        var query = $.param({
            search: $('#search-input').val(),
            agent_id: $('#agent-select').val(),
            payment_status: $('#payment-status-select').val(),
            status: $('#client-status-select').val(),
            paid_today: $('#paid-today-select').val(),
            from_date: $('#from-date').val(),
            to_date: $('#to-date').val(),
            download: 'pdf' // Indicate that we want to download a PDF
        });
        // Redirect to the download route with query parameters
        window.location.href = '{{ route('admin.allclients') }}?' + query;
    });

    // Handle export button click
    $('#export-button').on('click', function() {
        // Redirect to export route with current filters
        var query = $.param({
            search: $('#search-input').val(),
            agent_id: $('#agent-select').val(),
            payment_status: $('#payment-status-select').val(),
            status: $('#client-status-select').val(),
            paid_today: $('#paid-today-select').val(),
            from_date: $('#from-date').val(),
            to_date: $('#to-date').val(),
            export: 'excel'
        });
        window.location.href = '{{ route('admin.allclients') }}?' + query;
    });

    // Prevent form submission
    $('#client-filter-form').on('submit', function(e) {
        e.preventDefault();
        fetchClients();
    });
});
</script>
@endpush
