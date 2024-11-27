@php
    // Define default values for membership and page title if not set
    $membership = $membership ?? new \App\Models\Membership();
    $pageTitle = $pageTitle ?? 'Create New Membership';
@endphp

@extends('layouts.admin.app')

@section('title', $pageTitle)

@push('css_or_js')
    <style>
        /* Additional styling if needed */
        .form-group label {
            font-weight: bold;
        }
        .form-control {
            height: 45px;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid content">
    <!-- Page Header -->
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $pageTitle }}</h1>
        <p class="welcome-msg">Fill out the form below to create a new membership.</p>
    </div>

    <!-- Membership Creation Form Card -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.memberships.store') }}" method="POST">
                @csrf
                
                <!-- Client Selection -->
                <div class="form-group mb-3">
                    <label for="client_id" class="form-label">Client</label>
                    <select name="client_id" id="client_id" class="form-control" required>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Agent Selection -->
                <div class="form-group mb-3">
                    <label for="user_id" class="form-label">Agent</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="" disabled selected>Select an agent</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('user_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->f_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Membership Type -->
                <div class="form-group mb-3">
                    <label for="membership_type" class="form-label">Membership Type</label>
                    <select name="membership_type" id="membership_type" class="form-control" required>
                        <option value="Standard" {{ old('membership_type') == 'Standard' ? 'selected' : '' }}>Standard</option>
                        <option value="Premium" {{ old('membership_type') == 'Premium' ? 'selected' : '' }}>Premium</option>
                        <option value="Metal" {{ old('membership_type') == 'Metal' ? 'selected' : '' }}>Metal</option>
                    </select>
                    @error('membership_type')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Is Paid -->
                <div class="form-group mb-3">
                    <label for="is_paid" class="form-label">Is Paid</label>
                    <select name="is_paid" id="is_paid" class="form-control" required>
                        <option value="1" {{ old('is_paid') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('is_paid') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('is_paid')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Shares -->
                <div class="form-group mb-3">
                    <label for="shares" class="form-label">Shares</label>
                    <input type="number" name="shares" id="shares" class="form-control" value="{{ old('shares', 0) }}" min="0" required>
                    @error('shares')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Share Value -->
                <div class="form-group mb-3">
                    <label for="share_value" class="form-label">Share Value</label>
                    <input type="number" name="share_value" id="share_value" class="form-control" step="0.01" value="{{ old('share_value', 0.00) }}" required>
                    @error('share_value')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Membership Fees -->
                <div class="form-group mb-3">
                    <label for="membership_fees" class="form-label">Membership Fees</label>
                    <input type="number" name="membership_fees" id="membership_fees" class="form-control" step="0.01" value="{{ old('membership_fees', 0.00) }}" required>
                    @error('membership_fees')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Is Shares Paid -->
                <div class="form-group mb-3">
                    <label for="is_shares_paid" class="form-label">Is Shares Paid</label>
                    <select name="is_shares_paid" id="is_shares_paid" class="form-control" required>
                        <option value="1" {{ old('is_shares_paid') == '1' ? 'selected' : '' }}>Yes</option>
                        <option value="0" {{ old('is_shares_paid') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                    @error('is_shares_paid')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Create Membership</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
    <script>
        // Add any custom JavaScript here if necessary
    </script>
@endpush
