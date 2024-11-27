@extends('layouts.admin.app')

@section('title', 'Savings Account Details')

@push('css_or_js')
    <!-- Additional CSS if needed -->
    <style>
        .transaction-table th, .transaction-table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">Savings Account Details for {{ $savingsAccount->client->name }}</h1>
        <p class="welcome-msg">View details and transactions of the savings account.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <!-- Account Details -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5>Account Information</h5>
                    <table class="table table-borderless">
                        <tr>
                            <th>Account Number:</th>
                            <td>{{ $savingsAccount->account_number }}</td>
                        </tr>
                        <tr>
                            <th>Client Name:</th>
                            <td>{{ $savingsAccount->client->name }}</td>
                        </tr>
                        <tr>
                            <th>Agent:</th>
                            <td>{{ $savingsAccount->agent ? $savingsAccount->agent->f_name . ' ' . $savingsAccount->agent->l_name : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Account Type:</th>
                            <td>{{ $savingsAccount->accountType->name }}</td>
                        </tr>
                        <tr>
                            <th>Interest Rate (%):</th>
                            <td>{{ number_format($savingsAccount->accountType->interest_rate, 0) }}</td>
                        </tr>
                        <tr>
                            <th>Compounding Frequency:</th>
                            <td>{{ ucfirst($savingsAccount->accountType->compounding_frequency) }}</td>
                        </tr>
                        <tr>
                            <th>Balance:</th>
                            <td>{{ number_format($savingsAccount->balance, 0) }} /=</td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $savingsAccount->created_at->format('Y-m-d') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $savingsAccount->updated_at->format('Y-m-d') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <h5>Actions</h5>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('admin.savings.depositForm', $savingsAccount->id) }}" class="btn btn-success">
                            <i class="tio-plus"></i> Deposit Funds
                        </a>
                        <a href="{{ route('admin.savings.withdrawForm', $savingsAccount->id) }}" class="btn btn-warning">
                            <i class="tio-minus"></i> Withdraw Funds
                        </a>
                        <a href="{{ route('admin.savings.edit', $savingsAccount->id) }}" class="btn btn-primary">
                            <i class="tio-edit"></i> Edit Account
                        </a>
                        <form action="{{ route('admin.savings.destroy', $savingsAccount->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this savings account?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="tio-delete"></i> Delete Account
                            </button>
                        </form>
                        <!--<a href="" class="btn btn-secondary" disabled>-->
                        <!--    <i class="tio-print"></i> Print Receipt-->
                        <!--</a>-->
                        <!-- Note: Replace 'transaction' => '' with actual transaction ID in the transaction rows -->
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <h5 class="mb-3">Transaction History</h5>
            <div class="table-responsive">
                <table class="table table-hover transaction-table">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($savingsAccount->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('Y-m-d') }}</td>
                                <td>
                                    @if ($transaction->type === 'deposit')
                                        <span class="badge bg-success">Deposit</span>
                                    @elseif ($transaction->type === 'withdrawal')
                                        <span class="badge bg-warning">Withdrawal</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->type === 'deposit')
                                        <span class="text-success">+ {{ number_format($transaction->amount, 0) }} /=</span>
                                    @elseif ($transaction->type === 'withdrawal')
                                        <span class="text-danger">- {{ number_format($transaction->amount, 0) }} /=</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->description }}</td>
                                <td>
                                    <a href="{{ route('admin.savings.transaction.receipt', ['savings' => $savingsAccount->id, 'transaction' => $transaction->id]) }}" class="btn btn-sm btn-secondary" target="_blank">
                                        <i class="tio-print"></i> {{ translate('Print Receipt') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination (if necessary) -->
            @if ($savingsAccount->transactions->count() > 10)
                <div class="mt-3">
                    {{ $savingsAccount->transactions()->paginate(10)->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush

