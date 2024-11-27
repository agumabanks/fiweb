@extends('layouts.admin.app')

@section('title', $pageTitle)

@push('css_or_js')
    <!-- Additional CSS if needed -->
    <style>
        .memberships-table th, .memberships-table td {
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $pageTitle }}</h1>
        <p class="welcome-msg">Manage all memberships.</p>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.memberships.create') }}" class="btn btn-primary">
            <i class="tio-add-circle"></i> Create New Membership
        </a>
        
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if($memberships->count())
                <div class="table-responsive">
                    <table class="table table-hover memberships-table">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Membership Type</th>
                                <th>Is Paid</th>
                                <th>Shares</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($memberships as $membership)
                                <tr>
                                    <td>{{ $membership->id }}</td>
                                    <td>{{ $membership->client->name }}</td>
                                    <td>{{ $membership->membership_type }}</td>
                                    <td>{{ $membership->is_paid ? 'Yes' : 'No' }}</td>
                                    <td>{{ number_format($membership->shares, 2) }}</td>
                                    <td>
                                        <div class="d-flex justify-content-start gap-2">
                                            <a href="{{ route('admin.memberships.show', $membership->id) }}" class="action-btn btn btn-outline-primary" title="View">
                                                <i class="tio-visible"></i>
                                            </a>
                                            <a href="{{ route('admin.memberships.edit', $membership->id) }}" class="action-btn btn btn-outline-info" title="Edit">
                                                <i class="tio-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.memberships.destroy', $membership->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this membership?');" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn btn btn-outline-danger" title="Delete">
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
                    {{ $memberships->links() }}
                </div>
            @else
                <div class="alert alert-info">No memberships found.</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush
