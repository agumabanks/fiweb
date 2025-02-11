@extends('layouts.admin.app')

@section('title', translate('All Clients'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        /* Sticky header styling */
        .sticky-header {
            /* position: sticky; */
            top: 0;
            /* z-index: 100; */
            background-color: #fff;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        /* Card and overall layout */
        .card {
            border: none;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        /* Refined form controls */
        .form-group {
            margin-bottom: 1rem;
        }
        .form-control {
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 0.5rem 0.75rem;
            font-size: 14px;
        }
        /* Pagination: style only once if rendered in the partial view */
        .pagination {
            margin-top: 1rem;
        }
    </style>
@endpush

@section('content')
<div class="card m-4">
    <!-- Sticky Header: Title + Filter Form -->
    <div class="sticky-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h2 class="mb-3">{{ $pageTitle }}</h2>
            <form id="client-filter-form" class="d-flex flex-wrap align-items-end gap-2">
                <!-- Search Input -->
                <div class="form-group">
                    <input id="search-input" type="search" name="search" class="form-control"
                           placeholder="{{ translate('Search by Name') }}"
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
                        <option value="all">{{ translate('All Loans Pays') }}</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>
                            {{ translate('Paid') }}
                        </option>
                        <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>
                            {{ translate('Unpaid') }}
                        </option>
                    </select>
                </div>
                <!-- Client Status Filter -->
                <div class="form-group">
                    <select id="client-status-select" name="status" class="form-control">
                        <option value="all">{{ translate('All Client Statuses') }}</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>
                            {{ translate('Active') }}
                        </option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>
                            {{ translate('Inactive') }}
                        </option>
                    </select>
                </div>
                <!-- Paid Today Filter -->
                <div class="form-group">
                    <select id="paid-today-select" name="paid_today" class="form-control">
                        <option value="all" {{ request('paid_today') == 'all' ? 'selected' : '' }}>
                            {{ translate('All Payment Today Statuses') }}
                        </option>
                        <option value="paid" {{ request('paid_today') == 'paid' ? 'selected' : '' }}>
                            {{ translate('Paid Today') }}
                        </option>
                        <option value="not_paid" {{ request('paid_today') == 'not_paid' ? 'selected' : '' }}>
                            {{ translate('Not Paid Today') }}
                        </option>
                    </select>
                </div>
                <!-- Date Range Filters -->
                <div class="form-group">
                    <input type="date" id="from-date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="form-group">
                    <input type="date" id="to-date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <!-- Records Per Page -->
                <div class="form-group">
                    <select id="per-page-select" name="per_page" class="form-control">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <!-- Export & Add Buttons -->
                <div class="form-group">
                    <button type="button" id="export-button" class="btn btn-success">
                        <i class="fas fa-file-excel"></i> {{ translate('Export to Excel') }}
                    </button>
                </div>
                <div class="form-group">
                    <button type="button" id="download-pdf-button" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> {{ translate('Download PDF') }}
                    </button>
                </div>
                <div class="form-group">
                    <a href="{{ route('admin.client.create') }}" class="btn btn-primary">
                        <i class="tio-add"></i> {{ translate('Add') }} {{ translate('Client') }}
                    </a>
                </div>
            </form>
        </div>
    <!-- End Sticky Header -->

    <!-- Clients Table Container -->
    <div id="clients-table-container" class="m-3">
        @include('admin-views.clients.partials.clients-table', [
            'clientsData' => $clientsData,
            'emptyMessage' => $emptyMessage,
            'clients' => $clients
        ])
    </div>
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
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Function to fetch and update the clients table
    function fetchClients(page = 1) {
        var data = {
            search: $('#search-input').val(),
            agent_id: $('#agent-select').val(),
            payment_status: $('#payment-status-select').val(),
            status: $('#client-status-select').val(),
            paid_today: $('#paid-today-select').val(),
            from_date: $('#from-date').val(),
            to_date: $('#to-date').val(),
            per_page: $('#per-page-select').val(),
            page: page,
        };

        $.ajax({
            url: '{{ route('admin.allclients') }}',
            type: 'GET',
            data: data,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            beforeSend: function() {
                // Optionally, show a loading indicator here
            },
            success: function(response) {
                // Replace the content of the table container
                $('#clients-table-container').html(response.html);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                console.error('Response:', xhr.responseText);
            },
            complete: function() {
                // Optionally, hide the loading indicator here
            }
        });
    }

    // Debounce the search input
    $('#search-input').on('keyup', function() {
        clearTimeout($.data(this, 'timer'));
        var wait = setTimeout(fetchClients, 500);
        $(this).data('timer', wait);
    });

    // Trigger fetch on any filter change (including per page)
    $('#agent-select, #payment-status-select, #client-status-select, #from-date, #to-date, #paid-today-select, #per-page-select').on('change', function() {
        fetchClients();
    });

    // Handle pagination link clicks (ensuring only one instance shows)
    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        if (page !== undefined) {
            fetchClients(page);
        }
    });

    // Handle PDF download
    $('#download-pdf-button').on('click', function() {
        var query = $.param({
            search: $('#search-input').val(),
            agent_id: $('#agent-select').val(),
            payment_status: $('#payment-status-select').val(),
            status: $('#client-status-select').val(),
            paid_today: $('#paid-today-select').val(),
            from_date: $('#from-date').val(),
            to_date: $('#to-date').val(),
            download: 'pdf'
        });
        window.location.href = '{{ route('admin.allclients') }}?' + query;
    });

    // Handle Excel export
    $('#export-button').on('click', function() {
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

    // Prevent form submission and fetch via AJAX instead
    $('#client-filter-form').on('submit', function(e) {
        e.preventDefault();
        fetchClients();
    });
});
</script>
@endpush
