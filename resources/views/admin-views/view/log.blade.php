@extends('layouts.admin.app')

@section('title', translate('Log'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/agent.png')}}" alt="{{ translate('user') }}">
            <h2 class="page-header-title">{{translate('Details')}}</h2>
        </div>

        <div class="page-header mb-4">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                @include('admin-views.view.partails.navbar')
            </div>
        </div>

        <div class="card">
            <div class="card-header flex-between __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Logs')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $userLogs->total() }}</span>
                </div>
                <div>
                    <form action="{{url()->current()}}" method="GET">
                        <div class="input-group">
                            <input id="datatableSearch_" type="search" name="search"
                                    class="form-control mn-md-w380"
                                    placeholder="{{translate('Search by ip, deviceId, browser, os or device model')}}" aria-label="Search"
                                    value="{{$search}}" required autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th >{{translate('name')}}</th>
                            <th>{{translate('phone')}}</th>
                            <th>{{translate('ip_address')}}</th>
                            <th>{{translate('device_id')}}</th>
                            <th>{{translate('os')}}</th>
                            <th>{{translate('device_model')}}</th>
                            <th class="text-center">{{translate('login_time')}}</th>
                        </tr>
                    </thead>

                    <tbody id="set-rows">
                    @foreach($userLogs as $key=>$userLog)
                        @if($userLog->user)
                            <tr>
                                <td>
                                    <a class="d-block font-size-sm text-body"
                                        href="{{route('admin.customer.view',[$userLog->user['id']])}}">
                                        {{$userLog->user['f_name'].' '.$userLog->user['l_name']}}
                                    </a>
                                </td>
                                <td>
                                    {{$userLog->user['phone']}}
                                </td>
                                <td>{{ $userLog->ip_address }}</td>
                                <td>{{ $userLog->device_id }}</td>
                                <td>{{ $userLog->os }}</td>
                                <td>{{ $userLog->device_model }}</td>
                                <td class="text-center">{{ date('d-M-Y H:iA', strtotime($userLog->created_at)) }}</td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $userLogs->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
