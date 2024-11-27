@extends('layouts.admin.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid content">
    <div class="page-header pb-2">
        <h1 class="page-header-title text-primary mb-1">{{ $pageTitle }}</h1>
        <p class="welcome-msg">Transfer shares from Membership #{{ $membership->id }} to another membership.</p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('shares.transfer.post', $membership->id) }}" method="POST">
                @csrf
                <div class="form-group mb-3">
                    <label for="to_membership_id" class="form-label">To Membership</label>
                    <select name="to_membership_id" id="to_membership_id" class="form-control" required>
                        @foreach($memberships as $otherMembership)
                            <option value="{{ $otherMembership->id }}">Membership #{{ $otherMembership->id }} - {{ $otherMembership->user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label for="amount" class="form-label">Amount of Shares</label>
                    <input type="number" name="amount" id="amount" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="form-group mb-3">
                    <label for="description" class="form-label">Description (optional)</label>
                    <textarea name="description" id="description" class="form-control"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Transfer Shares</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
    <!-- Additional Scripts if needed -->
@endpush
