@extends('layouts.admin.app')

@section('title', translate('User Logs'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/man.png')}}" alt="">
            <h2 class="page-header-title">{{translate('User Logs')}}</h2>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('User Logs')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $userLogs->total() }}</span>
                </div>
                <div>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                   class="form-control mn-md-w380 height-44px"
                                   placeholder="Search by ip, deviceId, browser, os or device model" aria-label="Search"
                                   value="{{$search??''}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                    <thead class="thead-light">
                    <tr>
                        <th >{{translate('SL')}}</th>
                        <th >{{translate('name')}}</th>
                        <th>{{translate('phone')}}</th>
                        <th>{{translate('ip_address')}}</th>
                        <th>{{translate('device_id')}}</th>
                        <th>{{translate('os')}}</th>
                        <th>{{translate('device_model')}}</th>
                        <th>{{translate('login_time')}}</th>
                    </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($userLogs as $key=>$user_log)
                        @if($user_log->user)
                            <tr>
                                <td>{{$userLogs->firstitem()+$key}}</td>
                                <td>
                                    <a class="d-block font-size-sm text-dark"
                                       @if($user_log->user->type == 1)
                                       href="{{route('admin.agent.log',[$user_log->user['id']])}}"
                                    @elseif($user_log->user->type == 2)
                                       href="{{route('admin.customer.log',[$user_log->user['id']])}}"
                                    @endif
                                    >
                                        {{$user_log->user['f_name'].' '.$user_log->user['l_name']}}
                                    </a>
                                </td>
                                <td>
                                    <a href="tel:{{$user_log->user['phone']}}" class="text-dark">{{$user_log->user['phone']}}</a>
                                </td>
                                <td>{{ $user_log->ip_address }}</td>
                                <td>{{ $user_log->device_id }}</td>
                                <td>{{ $user_log->os }}</td>
                                <td>{{ $user_log->device_model }}</td>
                                <td>{{ date_time_formatter($user_log->created_at) }}</td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $userLogs->links() !!}
                    <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                </div>
            </div>
        </div>
    </div>
@endsection
