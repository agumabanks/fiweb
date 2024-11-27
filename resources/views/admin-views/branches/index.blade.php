@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-2">
        <h1>Branches</h1>
        <!-- Add Branch Button -->
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">Add Branch</a>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Branch ID</th>
                <th>Branch Name</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach($branches as $branch)
                <tr>
                    <td>{{ $branch->branch_id }}</td>
                    <td>{{ $branch->branch_name }}</td>
                    <td>{{ $branch->location }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
