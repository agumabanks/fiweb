@extends('layouts.admin.app')

@section('title', 'Add Excess Fund')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-4">
                    <h2 class="mb-0 text-center">Add Excess Fund</h2>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.excess-funds.store') }}" method="POST" class="mt-4">
                        @csrf
                        
                        <!-- Client Selection -->
                        <div class="mb-4">
                            <label for="client_id" class="form-label fw-semibold">Client</label>
                            <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Select a client</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('client_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Amount Input -->
                        <div class="mb-4">
                            <label for="amount" class="form-label fw-semibold">Amount</label>
                            <input type="number" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="Enter amount" value="{{ old('amount') }}" required>
                            @error('amount')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Date Added -->
                        <div class="mb-4">
                            <label for="date_added" class="form-label fw-semibold">Date Added</label>
                            <input type="date" name="date_added" id="date_added" class="form-control @error('date_added') is-invalid @enderror" value="{{ old('date_added') }}" required>
                            @error('date_added')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Status Selection -->
                        <div class="mb-4">
                            <label for="status" class="form-label fw-semibold">Status</label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="unallocated" {{ old('status') == 'unallocated' ? 'selected' : '' }}>Unallocated</option>
                                <option value="allocated" {{ old('status') == 'allocated' ? 'selected' : '' }}>Allocated</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                                Submit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
