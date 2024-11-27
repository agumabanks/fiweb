@extends('layouts.admin.app')

@section('title', 'Membership Details')

@push('css_or_js')
    <!-- Additional CSS if needed -->
    <style>
        .transactions-table th, .transactions-table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">Membership Details #{{ $membership->id }}</h1>
        <p class="welcome-msg">Details for membership ID #{{ $membership->client->name }}.</p>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4>Membership Information</h4>
            <table class="table">
                <tr>
                    <th>User</th>
                    <td>{{ $membership->client->name }}</td>
                </tr>
                <tr>
                    <th>Membership Type</th>
                    <td>{{ $membership->membership_type }}</td>
                </tr>
                <tr>
                    <th>Is Paid</th>
                    <td>{{ $membership->is_paid ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Total Shares</th>
                    <td>{{ number_format($membership->shares, 2) }}</td>
                </tr>
                <!-- Add other membership details if needed -->
            </table>
            <div class="mb-3">
                <a href="{{ route('admin.shares.create', $membership->id) }}" class="btn btn-primary">
                    <i class="tio-add-circle"></i> Create Share Transaction
                </a>
                <a href="{{ route('admin.shares.transfer.form', $membership->id) }}" class="btn btn-secondary">
                    <i class="tio-arrow-right"></i> Transfer Shares
                </a>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4>Share Transactions</h4>
            
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush
