@extends('layouts.admin.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4 text-muted">Add New Expense</h2>

    <!-- Error Handling -->
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Add Expense Form -->
    <form action="{{ route('admin.expense.store') }}" method="POST">
        @csrf

        <!-- Description Field -->
        <div class="mb-3">
            <label for="description" class="form-label">{{ translate('Name:') }}</label>
            <input type="text" class="form-control" id="description" name="description" placeholder="Enter expense name or description" required>
        </div>

        <!-- Amount Field -->
        <div class="mb-3">
            <label for="amount" class="form-label">{{ translate('Amount:') }}</label>
            <input type="number" class="form-control" id="amount" name="amount" step="0.01" placeholder="Enter expense amount" required>
        </div>

        <!-- Time Field -->
        <div class="mb-3">
            <label for="time" class="form-label">{{ translate('Time:') }}</label>
            <input type="datetime-local" class="form-control" id="time" name="time" required>
        </div>

        <!-- Category Field -->
        <div class="mb-3">
            <label for="category" class="form-label">{{ translate('Category/ type:') }}</label>
            <input type="text" class="form-control" id="category" name="category" placeholder="Enter category">
        </div>

        <!-- Payment Method Field -->
        <div class="mb-3">
            <label for="payment_method" class="form-label">{{ translate('Payment Method:') }}</label>
            <input type="text" class="form-control" id="payment_method" name="payment_method" placeholder="Enter payment method">
        </div>

        <!-- Agent Selection Field -->
        <div class="mb-3">
            <label for="agent" class="form-label">{{ translate('Select Agent:') }}</label>
            <select name="agent_id" id="agent" class="form-control" required>
                <option value="">{{ translate('-- Select Agent --') }}</option>
                @foreach ($agents as $agent)
                    <option value="{{ $agent->id }}" {{ old('agent') == $agent->id ? 'selected' : '' }}>{{ $agent->f_name }}</option>
                @endforeach
            </select>
        </div>
        
        
        

        <!-- Notes Field -->
        <div class="mb-3">
            <label for="notes" class="form-label">{{ translate('Notes:') }}</label>
            <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add any notes"></textarea>
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary">{{ translate('Save Expense') }}</button>
        </div>
    </form>
</div>
@endsection
