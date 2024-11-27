

@push('script')
    <!-- Include any additional scripts here -->
    
    @extends('layouts.admin.app')

@section('title', $pageTitle)

@push('css_or_js')
    <!-- Additional CSS if needed -->
    <style>
        .savings-table th, .savings-table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $pageTitle }}</h1>
        <p class="welcome-msg">Manage all savings accounts.</p>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.savings.create') }}" class="btn btn-primary">
            <i class="tio-add-circle"></i> Create New Savings Account
        </a>
        <!--<a href="{{ route('admin.savings.create') }}" class="btn btn-primary">-->
        <!--    <i class="tio-add-circle"></i> New Shares-->
        <!--</a>-->
        
        <a href="{{ route('admin.memberships.index') }}" class="btn btn-primary">
            <i class="tio-add-circle"></i> Memberships
        </a>
        
        
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($savingsAccounts->count())
                <div class="table-responsive">
                    <table class="table table-hover savings-table">
                        <thead class="table-light">
                            <tr>
                                <th>Account Number</th>
                                <th>Client Name</th>
                                <th>Agent</th>
                                <th>Account Type</th>
                                <th>Balance</th>
                                <th>Interest Rate (%)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($savingsAccounts as $account)
                                <tr>
                                    <td>{{ $account->account_number }}</td>
                                    <td>{{ $account->client->name }}</td>
                                    <td>{{ $account->agent ? $account->agent->f_name . ' ' . $account->agent->l_name : 'N/A' }}</td>
                                    <td>{{ $account->accountType->name }}</td>
                                    <td>{{ number_format($account->balance, 0) }} /=</td>
                                    <td>{{ number_format($account->accountType->interest_rate, 2) }}</td>
                                     <td>
                                        <div class="d-flex justify-content-start gap-2">
                                             <a href="{{ route('admin.savings.show', $account->id) }}" class="action-btn btn btn-outline-primary" title="{{ translate('View') }}">
                                                <i class="tio-visible"></i>
                                            </a>
        
                                             <a href="{{ route('admin.savings.edit', $account->id) }}" class="action-btn btn btn-outline-info" title="{{ translate('Edit') }}">
                                                <i class="tio-edit"></i>
                                            </a>
        
                                             <form action="{{ route('admin.savings.destroy', $account->id) }}" method="POST" onsubmit="return confirm('{{ translate('Are you sure you want to delete this savings account?') }}');" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn btn btn-outline-danger" title="{{ translate('Delete') }}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $savingsAccounts->links() }}
                </div>
            @else
                <div class="alert alert-info">No savings accounts found.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush

<!--@endpush-->
