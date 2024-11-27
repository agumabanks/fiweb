@extends('layouts.admin.app')

@section('content')
<div class="container">
    <h1>Create New Branch</h1>

    <form action="{{ route('admin.branches.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="branch_name">Branch Name</label>
            <input type="text" name="branch_name" id="branch_name" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" name="location" id="location" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Create Branch</button>
    </form>
</div>
@endsection
