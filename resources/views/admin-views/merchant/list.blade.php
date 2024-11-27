@extends('layouts.admin.app')

@section('title', translate('Merchant List'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/store.png')}}" alt="{{ translate('merchant') }}">
            <h2 class="page-header-title">{{translate('Merchant List')}}</h2>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('merchant Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $merchants->total() }}</span>
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

                    <a href="{{route('admin.merchant.add')}}" class="btn btn-primary">
                        <i class="tio-add"></i> {{translate('Add')}} {{translate('merchant')}}
                    </a>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('name')}}</th>
                        <th>{{translate('Contact')}}</th>
                        <th>{{ translate('callback') }}</th>
                        <th>{{translate('status')}}</th>
                        <th class="text-center">{{translate('action')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($merchants as $key=>$merchant)
                        <tr>
                            <td>{{$merchants->firstitem()+$key}}</td>
                            <td>
                                <a href="{{route('admin.merchant.view',[$merchant['id']])}}" class="media gap-3 align-items-center text-dark">
                                    <div class="avatar avatar-lg border rounded-circle">
                                        <img class="rounded-circle img-fit"
                                            src="{{$merchant['image_fullpath']}}"
                                             alt="{{ translate('merchant') }}">
                                    </div>
                                    <div class="media-body">
                                        {{$merchant['f_name'].' '.$merchant['l_name']}}
                                    </div>
                                </a>
                            </td>
                            <td>
                                <div>
                                    <a class="text-dark" href="tel:{{$merchant['phone']}}">{{$merchant['phone']}}</a>
                                </div>
                                <div>
                                    @if(isset($merchant['email']))
                                        <a class="text-dark" href="mailto:{{ $merchant['email'] }}" class="text-primary">{{ $merchant['email'] }}</a>
                                    @else
                                        <span class="badge-pill badge-soft-dark text-muted">{{ translate('Email unavailable') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{$merchant->merchant['callback']}}
                            </td>
                            <td>
                                <label class="switcher" for="welcome_status_{{$merchant['id']}}">
                                    <input type="checkbox" name="welcome_status" class="switcher_input change-status"
                                           data-route="{{route('admin.merchant.status',[$merchant['id']])}}"
                                            id="welcome_status_{{$merchant['id']}}" {{$merchant?($merchant['is_active']==1?'checked':''):''}}>
                                    <span class="switcher_control"></span>
                                </label>
                            </td>
                            <td>
                                <div class="d-flex justify-content-center gap-2">
                                    <a class="action-btn btn btn-outline-primary"
                                        href="{{route('admin.merchant.view',[$merchant['id']])}}">
                                        <i class="fa fa-eye" aria-hidden="true"></i>
                                    </a>
                                    <a class="action-btn btn btn-outline-info"
                                        href="{{route('admin.merchant.edit',[$merchant['id']])}}">
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
                    {!! $merchants->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
