@extends('layouts.admin.app')

@section('title', 'Cash Flow and Expense Management')

@section('content')
<div class="container p-4">
    <!-- Page Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <h2 class="text-muted mb-0">Cash Flow and Expenses</h2>
        <div class="d-flex gap-3">
            <a href="{{ route('admin.cashflow.create') }}" class="btn btn-primary">
                <i class="tio-add"></i> {{ translate('Add Cash Flow') }}
            </a>
            <a href="{{ route('admin.expense.create') }}" class="btn btn-primary">
                <i class="tio-add"></i> {{ translate('Add Expense') }}
            </a>
            <a href="{{ route('admin.excess-funds.create') }}" class="btn btn-primary">
                <i class="tio-add"></i> {{ translate('Add Excess Funds') }}
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#actualCashModal">
                <i class="tio-add"></i> {{ translate('Add Actual Safe Balance') }}
            </button>
        </div>
    </div>
    <!-- End Page Header -->

    <!-- Search Form -->
    <div class="mb-4">
        <form action="{{ url()->current() }}" method="GET" class="d-flex">
            <input type="search" name="search" class="form-control me-2" placeholder="{{ translate('Search by Description') }}" value="{{ request()->get('search') }}">
            <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
        </form>
    </div>
    <!-- End Search Form -->

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="cashflowTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="cashflows-tab" data-toggle="tab" href="#cashflows" role="tab" aria-controls="cashflows" aria-selected="true">{{ translate('Cash Flows') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="expenses-tab" data-toggle="tab" href="#expenses" role="tab" aria-controls="expenses" aria-selected="false">{{ translate('Expenses') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="excess-funds-tab" data-toggle="tab" href="#excess-funds" role="tab" aria-controls="excess-funds" aria-selected="false">{{ translate('Excess Funds') }}</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="actual-cash-tab" data-toggle="tab" href="#actual-cash" role="tab" aria-controls="actual-cash" aria-selected="false">{{ translate('Actual Cash Records') }}</a>
        </li>
    </ul>
    <!-- End Tabs Navigation -->

    <!-- Tabs Content -->
    <div class="tab-content" id="cashflowTabsContent">
        <!-- Cashflows Tab -->
        <div class="tab-pane fade show active" id="cashflows" role="tabpanel" aria-labelledby="cashflows-tab">
            <div class="card shadow-sm">
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ translate('Balance Before') }}</th>
                                <th>{{ translate('Capital Added') }}</th>
                                <th>{{ translate('Cash Banked') }}</th>
                                <th>{{ translate('Date') }}</th>
                                <th class="text-center">{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cashflows as $cashflow)
                            <tr>
                                <td>{{ number_format($cashflow->balance_bf, 2) }}</td>
                                <td>{{ number_format($cashflow->capital_added, 2) }}</td>
                                <td>{{ number_format($cashflow->cash_banked, 2) }}</td>
                                <td>{{ $cashflow->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.cashflow.destroy', $cashflow->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this cash flow?') }}');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="tio-delete"></i> {{ translate('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ translate('No cash flows found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Cashflows Tab -->

        <!-- Expenses Tab -->
        <div class="tab-pane fade" id="expenses" role="tabpanel" aria-labelledby="expenses-tab">
            <div class="card shadow-sm">
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ translate('Description') }}</th>
                                <th>{{ translate('Amount') }}</th>
                                <th>{{ translate('Agent') }}</th>
                                <th>{{ translate('Date') }}</th>
                                <th class="text-center">{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->description }}</td>
                                <td>{{ number_format($expense->amount, 2) }}</td>
                                <td>{{ $expense->agent->l_name ?? 'N/A' }}</td>
                                <td>{{ $expense->created_at->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <!-- Reverse Button -->
                                    <form action="{{ route('admin.expenses.reverse', $expense->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to reverse this expense?') }}');" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning btn-sm">
                                            <i class="tio-rotate"></i> {{ translate('Reverse') }}
                                        </button>
                                    </form>
                                    <!-- Delete Button -->
                                    <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this expense?') }}');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="tio-delete"></i> {{ translate('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ translate('No expenses found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Expenses Tab -->

        <!-- Excess Funds Tab -->
        <div class="tab-pane fade" id="excess-funds" role="tabpanel" aria-labelledby="excess-funds-tab">
            <div class="card shadow-sm">
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ translate('Client') }}</th>
                                <th>{{ translate('Amount') }}</th>
                                <th>{{ translate('Date Added') }}</th>
                                <th>{{ translate('Status') }}</th>
                                <th class="text-center">{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($excessFunds as $fund)
                            <tr>
                                <td>{{ $fund->client->name ?? 'N/A' }}</td>
                                <td>{{ number_format($fund->amount, 2) }}</td>
                                <td>{{ $fund->created_at->format('Y-m-d') }}</td>
                                <td>{{ ucfirst($fund->status) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.excess-funds.edit', $fund->id) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="tio-edit"></i> {{ translate('Edit') }}
                                    </a>
                                    <form action="{{ route('admin.excess-funds.destroy', $fund->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this excess fund?') }}');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="tio-delete"></i> {{ translate('Delete') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">{{ translate('No excess funds found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Excess Funds Tab -->

        <!-- Actual Cash Records Tab -->
        <div class="tab-pane fade" id="actual-cash" role="tabpanel" aria-labelledby="actual-cash-tab">
            <div class="card shadow-sm">
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ translate('Amount (UGX)') }}</th>
                                <th>{{ translate('Date Added') }}</th>
                                <th class="text-center">{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($actualCashRecords as $actualCash)
                            <tr>
                                <td>{{ number_format($actualCash->amount, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($actualCash->date_added)->format('Y-m-d') }}</td>
                                <td class="text-center">
                                    <!-- Edit Button -->
                                    <button type="button" class="btn btn-outline-warning btn-sm editActualCashBtn" data-toggle="modal" data-target="#editActualCashModal" data-id="{{ $actualCash->id }}" data-amount="{{ $actualCash->amount }}">
                                        <i class="tio-edit"></i> {{ translate('Edit') }}
                                    </button>
                                    <!-- Delete Button -->
                                    <button type="button" class="btn btn-outline-danger btn-sm deleteActualCashBtn" data-id="{{ $actualCash->id }}">
                                        <i class="tio-delete"></i> {{ translate('Delete') }}
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">{{ translate('No actual cash records found.') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!-- End Actual Cash Records Tab -->
    </div>
    <!-- End Tabs Content -->
</div>

<!-- Modals -->
<!-- Add Actual Cash Modal -->
<div class="modal fade" id="actualCashModal" tabindex="-1" role="dialog" aria-labelledby="actualCashModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('admin.actual-cash.store') }}" method="POST" id="addActualCashForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Add Actual Cash') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="add-amount">{{ translate('Amount (UGX)') }}</label>
                        <input type="number" name="amount" id="add-amount" class="form-control" required min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ translate('Save Actual Cash') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Add Actual Cash Modal -->

<!-- Edit Actual Cash Modal -->
<div class="modal fade" id="editActualCashModal" tabindex="-1" role="dialog" aria-labelledby="editActualCashModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" id="editActualCashForm">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Edit Actual Cash') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-amount">{{ translate('Amount (UGX)') }}</label>
                        <input type="number" name="amount" id="edit-amount" class="form-control" required min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">{{ translate('Update Actual Cash') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Edit Actual Cash Modal -->

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteActualCashModal" tabindex="-1" role="dialog" aria-labelledby="deleteActualCashModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" id="deleteActualCashForm">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ translate('Delete Actual Cash Record') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>{{ translate('Are you sure you want to delete this actual cash record?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">{{ translate('Delete') }}</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ translate('Cancel') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End Delete Confirmation Modal -->
<!-- End Modals -->
@endsection

@push('script_2')
<script>
    $(document).ready(function() {
        // Handle Edit Button Click
        $('.editActualCashBtn').on('click', function() {
            var actualCashId = $(this).data('id');
            var amount = $(this).data('amount');

            // Set the form action to the update route
            var formAction = '{{ route("admin.actual-cash.update", ":id") }}';
            formAction = formAction.replace(':id', actualCashId);
            $('#editActualCashForm').attr('action', formAction);

            // Set the amount input value
            $('#edit-amount').val(amount);
        });

        // Handle Delete Button Click
        $('.deleteActualCashBtn').on('click', function() {
            var actualCashId = $(this).data('id');

            // Set the form action to the delete route
            var formAction = '{{ route("admin.actual-cash.destroy", ":id") }}';
            formAction = formAction.replace(':id', actualCashId);
            $('#deleteActualCashForm').attr('action', formAction);

            // Show the delete confirmation modal
            $('#deleteActualCashModal').modal('show');
        });
    });
</script>
@endpush
