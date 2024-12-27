@extends('layouts.admin.app')

@section('title', 'Agent Daily Report')

@section('content')
<div class="container-fluid p-5">
    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.agent.trans') }}" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="date_range_start">Start Date</label>
                <input type="date" name="date_range[start]" class="form-control" value="{{ request('date_range.start') }}">
            </div>
            <div class="col-md-3">
                <label for="date_range_end">End Date</label>
                <input type="date" name="date_range[end]" class="form-control" value="{{ request('date_range.end') }}">
            </div>
            <div class="col-md-3">
                <label for="agent_id">Agent</label>
                <select name="agent_id" class="form-control">
                    <option value="">All Agents</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                            {{ $agent->l_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <label for="min_amount">Min Amount</label>
                <input type="number" name="min_amount" class="form-control" value="{{ request('min_amount') }}">
            </div>
            <div class="col-md-3">
                <label for="max_amount">Max Amount</label>
                <input type="number" name="max_amount" class="form-control" value="{{ request('max_amount') }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <!-- Recent Installment Payments -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-primary text-white text-center">
            <h4 class="mb-0 text-white">Recent Installment Payments</h4>
        </div>
        <div class="card-body p-4">
            <!-- Display Real-Time Installment Payments Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Agent Name</th>
                            <th>Client Name</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installmentPayments as $payment)
                        <tr class="{{ $payment->is_reversed ? 'text-muted' : '' }}">
                            <!-- Transaction ID -->
                            <td>
                                @if($payment->is_reversed)
                                    <del>{{ $payment->transaction_id ?? 'No Transaction ID' }}</del>
                                @else
                                    {{ $payment->transaction_id ?? 'No Transaction ID' }}
                                @endif
                            </td>

                            <!-- Agent Name -->
                            <td>
                                @if($payment->is_reversed)
                                    <del>{{ $payment->agent->l_name ?? 'Unknown Agent' }}</del>
                                @else
                                    {{ $payment->agent->l_name ?? 'Unknown Agent' }}
                                @endif
                            </td>

                            <!-- Client Name -->
                            <td>
                                @if($payment->is_reversed)
                                    <del>{{ $payment->client->name ?? 'Unknown Client' }}</del>
                                @else
                                    {{ $payment->client->name ?? 'Unknown Client' }}
                                @endif
                            </td>

                            <!-- Amount -->
                            <td>
                                @if($payment->is_reversed)
                                    <del>{{ number_format($payment->amount ?? 0, 0) }}</del>
                                @else
                                    {{ number_format($payment->amount ?? 0, 0) }}
                                @endif
                            </td>

                            <!-- Status -->
                            <td>
                                @if($payment->is_reversed)
                                    <del>
                                        <span class="badge bg-secondary">{{ ucfirst($payment->status ?? 'unknown') }}</span>
                                    </del>
                                @else
                                    @if($payment->status === 'paid')
                                        <span class="badge bg-success">{{ ucfirst($payment->status) }}</span>
                                    @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning">{{ ucfirst($payment->status) }}</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($payment->status ?? 'unknown') }}</span>
                                    @endif
                                @endif
                            </td>

                            <!-- Date -->
                            <td>
                                @if($payment->is_reversed)
                                    <del>{{ $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : 'No Date' }}</del>
                                @else
                                    {{ $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : 'No Date' }}
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No installment payments available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            <div class="d-flex justify-content-center mt-4">
                {{ $installmentPayments->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
