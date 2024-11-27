@extends('layouts.merchant.app')

@section('title', translate('Withdraw Request List'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/cash-withdrawal.png')}}" alt="{{translate('image')}}">
            <h1 class="page-header-title">{{translate('Withdraw_Requests')}}</h1>
        </div>

        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom gap-3 mb-3">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {{$requestStatus=='all'?'active':''}}"
                       href="{{url()->current()}}?request_status=all">
                        {{translate('all')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$requestStatus=='pending'?'active':''}}"
                       href="{{url()->current()}}?request_status=pending">
                        {{translate('pending')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$requestStatus=='approved'?'active':''}}"
                       href="{{url()->current()}}?request_status=approved">
                        {{translate('approved')}}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{$requestStatus=='denied'?'active':''}}"
                       href="{{url()->current()}}?request_status=denied">
                        {{translate('denied')}}
                    </a>
                </li>
            </ul>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10 flex-between">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Withdraw Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $withdrawRequests->total() }}</span>
                </div>

                <div class="flex-between __wrap-gap-10 align-items-center">
                    <div class="">
                        <button type="button" class="btn btn-outline-primary" data-toggle="dropdown"
                                aria-expanded="true">
                            <i class="tio-download-to"></i>
                            {{translate('Export')}}
                            <i class="tio-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2"
                                   href="{{route('merchant.withdraw.download', ['withdrawal_method'=>$method,'search'=>$search, 'request_status'=>$requestStatus])}}">
                                    <img width="20" src="{{asset('public/assets/admin/img/media/excel.png')}}" alt="{{translate('image')}}">
                                    <span>{{translate('Excel')}}</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="">
                        <select name="withdrawal_method" class="form-control js-select2-custom" id="withdrawal_method"
                                required>
                            <option value="all" selected>{{translate('Filter by method')}}</option>
                            @foreach($withdrawalMethods as $withdrawalMethod)
                                <option
                                    value="{{$withdrawalMethod->id}}" {{ $method == $withdrawalMethod->id ? 'selected' : '' }}>{{translate($withdrawalMethod->method_name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <form action="{{url()->current()}}" method="GET">
                            <div class="input-group">
                                <input id="datatableSearch_" type="search" name="search"
                                       class="form-control mn-md-w280"
                                       placeholder="{{translate('Search by Name')}}" aria-label="Search"
                                       value="{{$search??''}}" required autocomplete="off">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary">{{translate('Search')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="table-responsive datatable-custom">
                <table
                    class="table table-borderless table-nowrap table-align-middle card-table table-striped">
                    <thead class="thead-light">
                    <tr>
                        <th>{{translate('SL')}}</th>
                        <th>{{translate('Sender')}}</th>
                        <th>{{translate('Sender Type')}}</th>
                        <th>{{translate('Requested Amount')}}</th>
                        <th>{{translate('Withdrawal Method')}}</th>
                        <th>{{translate('Withdrawal Method Fields')}}</th>
                        <th>{{translate('Sender_Note')}}</th>
                        <th>{{translate('Request_Status')}}</th>
                        <th>{{translate('Payment_Status')}}</th>
                        <th>{{translate('Requested time')}}</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($withdrawRequests as $key=>$withdrawRequest)
                        <tr>
                            <td>{{$withdrawRequests->firstitem()+$key}}</td>
                            <td>
                                @if($withdrawRequest->user)
                                    <span class="d-block font-size-sm text-body">
                                        {{ $withdrawRequest->user->f_name . ' ' . $withdrawRequest->user->l_name }}
                                    </span>
                                @else
                                    <span class="badge badge-pill">{{translate('User_not_available')}}</span>
                                @endif
                            </td>
                            <td>
                                @if($withdrawRequest->user)
                                    <small class="badge badge-pill">
                                        {{ $withdrawRequest->user->type == 1 ? translate('Agent') : ($withdrawRequest->user->type == 3 ? translate('Merchant') : translate('Customer')) }}
                                    </small>
                                @else
                                    <span class="badge badge-pill">{{translate('Not_available')}}</span>
                                @endif
                            </td>
                            <td>{{ Helpers::set_symbol($withdrawRequest->amount) }}</td>
                            <td>
                                @if($withdrawRequest->withdrawal_method)
                                    <span class="badge badge-pill">{{ translate($withdrawRequest->withdrawal_method->method_name) }}</span>
                                @else
                                    <span class="badge badge-pill badge-danger">{{translate('not_available')}}</span>
                                @endif
                            </td>
                            <td>
                                @foreach($withdrawRequest->withdrawal_method_fields as $key=>$item)
                                    {{translate($key) . ': ' . $item}} <br/>
                            @endforeach
                            <td>
                                <div class="mx-w300 mn-w160 text-wrap">
                                    {{ $withdrawRequest->sender_note }}
                                </div>
                            </td>
                            <td>
                                @if( $withdrawRequest->request_status == 'pending' )
                                    <span class="badge badge-pill badge-soft-primary"> {{translate('Pending')}}</span>
                                @elseif( $withdrawRequest->request_status == 'approved' )
                                    <span class="badge badge-pill badge-soft-success"> {{translate('Approved')}}</span>
                                @elseif( $withdrawRequest->request_status == 'denied' )
                                    <span class="badge badge-pill badge-soft-danger"> {{translate('Denied')}}</span>
                                @endif
                            </td>
                            <td>
                                @if($withdrawRequest->is_paid )
                                    <span class="badge badge-pill badge-soft-success">{{translate('Paid')}}</span>
                                @else
                                    <span class="badge badge-pill badge-soft-danger">{{translate('Not_Paid')}}</span>
                                @endif
                            </td>
                            <td>{{ date_time_formatter($withdrawRequest->created_at) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">{{translate('No_data_available')}}</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $withdrawRequests->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";
        $("#withdrawal_method").on('change', function (event) {
            location.href = "{{route('merchant.withdraw.list')}}" + '?request_status=all' + '&withdrawal_method=' + $(this).val();
        })
    </script>
@endpush
