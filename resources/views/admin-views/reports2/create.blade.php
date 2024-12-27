@extends('layouts.admin.app')

@section('content')
<div class="container ">
    <h1 class="mt-3 mb-2">Create Daily Report</h1>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    <form action="{{ route('admin.daily-reports.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="branch_name">Branch Name:</label>
            <input type="text" class="form-control" id="branch_name" name="branch_name" required>
        </div>

        <div class="form-group">
            <label for="report_date">Report Date:</label>
            <input type="date" class="form-control" id="report_date" name="report_date" required>
        </div>

        <div class="form-group">
            <label for="opening_balance">Opening Balance:</label>
            <input type="number" class="form-control" id="opening_balance" name="opening_balance" required>
        </div>

        <div class="form-group">
            <label for="capital">Capital:</label>
            <input type="number" class="form-control" id="capital" name="capital" required>
        </div>

        <div class="form-group">
            <label for="total_cash">Total Cash:</label>
            <input type="number" class="form-control" id="total_cash" name="total_cash" required>
        </div>

        <div class="form-group">
            <label for="total_cash_out">Total Cash Out:</label>
            <input type="number" class="form-control" id="total_cash_out" name="total_cash_out" required>
        </div>

        <div class="form-group">
            <label for="closing_balance">Closing Balance:</label>
            <input type="number" class="form-control" id="closing_balance" name="closing_balance" required>
        </div>

        <button type="submit" class="btn btn-primary">Create Report</button>
    </form>
</div>
@endsection
