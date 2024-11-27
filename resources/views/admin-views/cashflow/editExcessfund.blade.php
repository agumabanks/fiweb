@extends('layouts.admin.app')

@section('title', 'Edit Excess Fund')

@section('content')
<div class="container p-4">
    <h2>Edit Excess Fund</h2>

    <form action="{{ route('admin.excess-funds.update', $excessFund->id) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Client Selection -->
        <div class="mb-3">
            <label for="client_id" class="form-label">Client</label>
            <select name="client_id" id="client_id" class="form-control" required>
                <option value="">Select a client</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ $excessFund->client_id == $client->id ? 'selected' : '' }}>
                        {{ $client->name }}
                    </option>
                @endforeach
            </select>
            @error('client_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Amount -->
        <div class="mb-3">
            <label for="amount" class="form-label">Amount</label>
            <input type="number" name="amount" id="amount" class="form-control" value="{{ old('amount', $excessFund->amount) }}" step="0.01" min="0" required>
            @error('amount')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Date Added -->
        <div class="mb-3">
            <label for="date_added" class="form-label">Date Added</label>
            <input type="date" name="date_added" id="date_added" class="form-control" value="{{ old('date_added', $excessFund->date_added->format('Y-m-d')) }}" required>
            @error('date_added')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Status -->
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-control" required>
                <option value="unallocated" {{ $excessFund->status == 'unallocated' ? 'selected' : '' }}>Unallocated</option>
                <option value="allocated" {{ $excessFund->status == 'allocated' ? 'selected' : '' }}>Allocated</option>
            </select>
            @error('status')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <!-- Submit and Cancel Buttons -->
        <button type="submit" class="btn btn-success">Update Excess Fund</button>
        <a href="{{ route('admin.excess-funds.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
