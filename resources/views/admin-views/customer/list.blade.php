@extends('layouts.admin.app')

@section('title', translate('Agent List'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/rating.png')}}" alt="{{ translate('Agent') }}">
            <h2 class="page-header-title">{{translate('Agent List')}}</h2>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Agent Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $customers->total() }}</span>
                </div>
                <div class="d-flex flex-wrap gap-3">
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                   class="form-control mn-md-w280"
                                   placeholder="{{translate('Search by Name')}}" aria-label="Search"
                                   value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                            </div>
                        </div>
                    </form>
                    <a href="{{route('admin.customer.add')}}" class="btn btn-primary">
                        <i class="tio-add"></i> {{translate('Add')}} {{translate('Agent')}}
                    </a>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th>{{translate('SL')}}</th>
                            <th>{{translate('name')}}</th>
                            <th>{{translate('Contacts')}}</th>
                            <th>{{translate('status')}}</th>
                            <th class="text-center">{{translate('action')}}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($customers as $key=>$customer)
                        <tr>
                            <td>{{$customers->firstitem()+$key}}</td>
                            <td>
                                <a class="media gap-3 align-items-center text-dark" href="{{route('admin.customer.view',[$customer['id']])}}">
                                    <div class="avatar avatar-lg border rounded-circle">
                                        <img class="rounded-circle img-fit"
                                        src="{{$customer['image_fullpath']}}"
                                        alt="{{ translate('image') }}">
                                    </div>
                                    <div class="card-body">
                                        {{$customer['f_name'].' '.$customer['l_name']}}
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <a class="text-dark" href="tel:{{$customer['phone']}}">{{$customer['phone']}}</a>
                                    @if(isset($customer['email']))
                                        <a class="text-dark" href="mailto:{{ $customer['email'] }}" class="text-primary">{{ $customer['email'] }}</a>
                                    @else
                                        <span class="text-muted text-left">{{ translate('Email Unavailable') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <label class="switcher" for="welcome_status_{{$customer['id']}}">
                                    <input type="checkbox" name="welcome_status"
                                           class="switcher_input change-status"
                                           id="welcome_status_{{$customer['id']}}" {{$customer?($customer['is_active']==1?'checked':''):''}}
                                            data-route="{{route('admin.customer.status',[$customer['id']])}}">
                                    <span class="switcher_control"></span>
                                </label>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                    href="{{route('admin.customer.view',[$customer['id']])}}">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <a class="action-btn btn btn-outline-info"
                                    href="{{route('admin.customer.edit',[$customer['id']])}}">
                                        <i class="fa fa-pencil" aria-hidden="true"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $customers->links() !!}
                    <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>
    </div>
@endsection

