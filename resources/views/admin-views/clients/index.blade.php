@extends('layouts.admin.app')

@section('title', translate('dashboard'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<div class="card">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <h2 class="my-4"> {{$pageTitle}}</h2>
        <!-- <a href="{{ route('admin.client.create') }}" class="btn btn-primary mb-3">-->
        <!-- Add Client-->
        <!--</a>-->
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

    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
        <thead class="thead-light">
            <tr>
                <th>Client</th>
                <th>Phone</th>
                <th>NIN</th>
                <th>Business</th>
                <th>Added By</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody id="set-rows">
            @foreach ($clientsData as $data)
                <tr>
                    <td>{{ $data['client']->name }}</td>
                    <td>{{ $data['client']->phone }}</td>
                    <td>{{ $data['client']->nin }}</td>
                    <td>{{ $data['client']->business }}</td>
                    <td>{{ $data['added_by_name'] }}</td>
                    <td>
                        <div class="d-flex justify-content-center gap-2">
                            <a class="action-btn btn btn-outline-primary"
                               href="{{ route('admin.clients.profile', $data['client']->id) }}">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </a>
                            <a class="action-btn btn btn-outline-info"
                               href="{{ route('admin.clients.edit', $data['client']->id) }}">
                                <i class="fa fa-pencil" aria-hidden="true"></i>
                            </a>
                            
                            <!--<form action="{{ route('admin.clients.delete', $data['client']->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?');">-->
                            <!--    @csrf-->
                            <!--    <button type="submit" class="action-btn btn btn-outline-danger">-->
                            <!--        <i class="fa fa-trash"></i>-->
                            <!--    </button>-->
                            <!--</form>-->
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="table-responsive mt-4 px-3">
        <div class="d-flex justify-content-end">
            {!! $clients->links() !!}
            <nav id="datatablePagination" aria-label="Activity pagination"></nav>
        </div>
    </div>
</div>
@endsection

@push('script')
@endpush

@push('script_2')
@endpush
