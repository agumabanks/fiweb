@extends('layouts.admin.app')

@section('title', $pageTitle)

@push('css_or_js')
    <!-- Additional CSS if needed -->
@endpush

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $pageTitle }}</h1>
        <p class="welcome-msg">Add funds to the savings account.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.savings.deposit', $savingsAccount->id) }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                    @error('amount')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">Deposit Funds</button>
                    <a href="{{ route('admin.savings.show', $savingsAccount->id) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush
