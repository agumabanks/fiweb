@extends('layouts.admin.app')

@section('title', $pageTitle)

@push('css_or_js')
    <!-- Additional CSS if needed -->
@endpush

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $pageTitle }}</h1>
        <p class="welcome-msg">Fill in the details to create a new savings account.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.savings.store') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                    <select class="form-control" id="client_id" name="client_id" required>
                        <option value="">Select Client</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }} ({{ $client->phone }})
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="agent_id" class="form-label">Agent</label>
                    <select class="form-control" id="agent_id" name="agent_id">
                        <option value="">Select Agent</option>
                        @foreach ($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->f_name }} {{ $agent->l_name }} ({{ $agent->phone }})
                            </option>
                        @endforeach
                    </select>
                    @error('agent_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="account_type_id" class="form-label">Account Type <span class="text-danger">*</span></label>
                    <select class="form-control" id="account_type_id" name="account_type_id" required>
                        <option value="">Select Account Type</option>
                        @foreach ($accountTypes as $type)
                            <option value="{{ $type->id }}" {{ old('account_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} ({{ number_format($type->interest_rate, 2) }}% {{ ucfirst($type->compounding_frequency) }})
                            </option>
                        @endforeach
                    </select>
                    @error('account_type_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="initial_deposit" class="form-label">Initial Deposit <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="initial_deposit" name="initial_deposit" value="{{ old('initial_deposit') }}" required>
                    @error('initial_deposit')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Add other necessary fields as per requirements -->

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    <a href="{{ route('admin.savings.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush

 