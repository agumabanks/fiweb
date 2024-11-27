@extends('layouts.admin.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1>Branches</h1>
        <!-- Add Branch Button -->
        <a href="{{ route('admin.branches.create') }}" class="btn btn-primary">Add Branch</a>
    </div>
    
    <div class="card-header __wrap-gap-10">
        <div class="d-flex align-items-center gap-2">
            <h5 class="card-header-title">{{translate('Clients Table')}}</h5>
        </div>
        <div class="d-flex flex-wrap gap-3">
            <form action="{{ url()->current() }}" method="GET">
                <div class="input-group">
                    <input id="datatableSearch_" type="search" name="search"
                           class="form-control mn-md-w280"
                           placeholder="{{translate('Search by Name')}}" aria-label="Search"
                           value="{{ old('search') }}" required autocomplete="off">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                    </div>
                </div>
            </form>
            <a href="{{ route('admin.client.create') }}" class="btn btn-primary">
                <i class="tio-add"></i> {{translate('Add')}} {{translate('Client')}}
            </a>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Branch ID 2</th>
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
