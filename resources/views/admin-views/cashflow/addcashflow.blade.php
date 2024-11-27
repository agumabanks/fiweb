@extends('layouts.admin.app')

@section('title', 'Create Cash Flow')

@section('content')
<div class="container p-4">
    <h1>Create New Cash Flow</h1>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.cashflow.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="balance_bf">Opening Balance</label>
            <input type="number" step="0.01" class="form-control" id="balance_bf" name="balance_bf" value="{{ old('balance_bf') }}" required>
            @error('balance_bf') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="capital_added">Capital Added</label>
            <input type="number" step="0.01" class="form-control" id="capital_added" name="capital_added" value="{{ old('capital_added') }}" required>
            @error('capital_added') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        
         <div class="form-group">
            <label for="unknown_funds">Unknown Funds</label>
            <input type="number" step="0.01" class="form-control" id="unknown_funds" name="unknown_funds" value="{{ old('unknown_funds') }}" required>
            @error('unknown_funds') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-group">
            <label for="cash_banked">Cash Banked</label>
            <input type="number" step="0.01" class="form-control" id="cash_banked" name="cash_banked" value="{{ old('cash_banked') }}" required>
            @error('cash_banked') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="btn btn-primary">Create Cash Flow</button>
    </form>
</div>
@endsection
