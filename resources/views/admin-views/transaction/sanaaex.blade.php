@extends('layouts.admin.app')

@section('title', 'Cash Flow and Expense Management')

@section('content')
<div class="container p-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h2 class="my-4"> {{$pageTitle}}</h2>
        
    </div>

    <div class="card-header __wrap-gap-10">
        <div class="d-flex align-items-center gap-2">
            <h5 class="card-header-title">{{translate('Clients Table')}}</h5>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group">
                    <input id="datatableSearch_" type="search" name="search"
                           class="form-control mn-md-w280"
                           placeholder="{{translate('Search by Name')}}" aria-label="Search"
                           value="{{ old('search') }}" required autocomplete="off">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                    </div>
                </div>
            </form>
            <a href="{{ route('admin.client.create') }}" class="btn btn-primary">
                <i class="tio-add"></i> {{translate('Add')}} {{translate('Client')}}
            </a>
        </div>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>@lang('ID')</th>
                <th>@lang('Balance Before')</th>
                <th>@lang('Capital Added')</th>
                <th>@lang('Cash Banked')</th>
                <th>@lang('Date')</th>
            </tr>
        </thead>
        <tbody id="cashflowTableBody">
            @foreach($cashflows as $cashflow)
                <tr>
                    <td>{{ $cashflow->id }}</td>
                    <td>{{ number_format($cashflow->balance_bf, 2) }}</td>
                    <td>{{ number_format($cashflow->capital_added, 2) }}</td>
                    <td>{{ number_format($cashflow->cash_banked, 2) }}</td>
                    <td>{{ $cashflow->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Expenses Table -->
    <h2>@lang('Expenses')</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>@lang('ID')</th>
                <th>@lang('Description')</th>
                <th>@lang('Amount')</th>
                <th>@lang('Date')</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $expense)
                <tr>
                    <td>{{ $expense->id }}</td>
                    <td>{{ $expense->description }}</td>
                    <td>{{ number_format($expense->amount, 2) }}</td>
                    <td>{{ $expense->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Modal for Add Cash Flow -->
    <div class="modal fade" id="cashflowModal" tabindex="-1" role="dialog" aria-labelledby="cashflowModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashflowModalLabel">@lang('Add Cash Flow')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="cashflowForm" method="POST" action="{{ route('cashflow.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="balance_bf">@lang('Balance Before')</label>
                            <input type="number" class="form-control" id="balance_bf" name="balance_bf" required>
                        </div>
                        <div class="form-group">
                            <label for="capital_added">@lang('Capital Added')</label>
                            <input type="number" class="form-control" id="capital_added" name="capital_added" required>
                        </div>
                        <div class="form-group">
                            <label for="cash_banked">@lang('Cash Banked')</label>
                            <input type="number" class="form-control" id="cash_banked" name="cash_banked" required>
                        </div>
                        <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Add Expense -->
    <div class="modal fade" id="expenseModal" tabindex="-1" role="dialog" aria-labelledby="expenseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="expenseModalLabel">@lang('Add Expense')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="expenseForm" method="POST" action="{{ route('expense.store') }}">
                        @csrf
                        <div class="form-group">
                            <label for="description">@lang('Description')</label>
                            <input type="text" class="form-control" id="description" name="description" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">@lang('Amount')</label>
                            <input type="number" class="form-control" id="amount" name="amount" required>
                        </div>
                        <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script_2')
<script>
    // Show modals on button clicks
    $('#addCashflowButton').on('click', function () {
        $('#cashflowModal').modal('show');
    });
    $('#addExpenseButton').on('click', function () {
        $('#expenseModal').modal('show');
    });

    // Handle cashflow form submission
    $('#cashflowForm').on('submit', function (e) {
        e.preventDefault();
        // Add your AJAX request or form submission logic here
    });

    // Handle expense form submission
    $('#expenseForm').on('submit', function (e) {
        e.preventDefault();
        // Add your AJAX request or form submission logic here
    });
</script>
@endpush
