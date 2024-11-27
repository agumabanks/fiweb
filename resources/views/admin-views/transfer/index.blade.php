@extends('layouts.admin.app')

@section('title', translate('Transfer List'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/dollar-2.png')}}" alt="{{ translate('transfer') }}">
            <h1 class="page-header-title">{{translate('Transfer')}}</h1>
        </div>

        <div class="card card-body mb-3">
            <form action="{{route('admin.transfer.store')}}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Receiver Type')}}</label>
                            <select name="receiver_type" class="form-control js-select2-custom" id="receiver_type" required>
                                <option value="" selected disabled>{{translate('Select Type')}}</option>
                                <option value="1">{{translate('Agent')}}</option>
                                <option value="2">{{translate('Customer')}}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Receiver')}}</label>
                            <select name="to_user_id" class="form-control js-data-example-ajax" id="receiver"
                                    data-placeholder="{{translate('Choose')}}" required>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label" for="exampleFormControlInput1">{{translate('Amount')}}</label>
                            <input type="number" name="amount" step="any" class="form-control" min="1" max="{{$unusedBalance}}"
                                   {{$unusedBalance <= 0 ? 'disabled' : ''}}
                                   placeholder="{{translate('Ex : 9999')}}" required>
                            @if($unusedBalance > 0)
                                <small class="w-100">{{ translate('The amount must be less than or equal to ') . $unusedBalance}}</small>
                            @else
                                <small class="w-100">{{ translate('The amount is too low to transfer') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">{{translate('Transfer')}}</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header __wrap-gap-10">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-header-title">{{translate('Transfer Table')}}</h5>
                    <span class="badge badge-soft-secondary text-dark">{{ $transfers->total() }}</span>
                </div>
                <div>
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
                </div>
            </div>
            <div class="table-responsive datatable-custom">
                <table
                    class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                        <tr>
                            <th class="border-0">{{translate('SL')}}</th>
                            <th class="border-0">{{translate('Receiver')}}</th>
                            <th class="border-0">{{translate('Receiver Type')}}</th>
                            <th class="border-0">{{translate('amount')}}</th>
                            <th class="border-0 text-center">{{translate('Time')}}</th>
                        </tr>
                    </thead>

                    <tbody>
                    @foreach($transfers as $key=>$transfer)
                        <tr>
                            <td>
                                {{$transfers->firstitem()+$key}}
                            </td>
                            <td>
                                @php($userInfo = \App\CentralLogics\Helpers::get_user_info($transfer->receiver))
                                @if(isset($userInfo))
                                    <a href="{{route('admin.customer.view',[$userInfo['id']])}}">{{ $userInfo->f_name . ' ' . $userInfo->l_name }}</a>
                                @else
                                    <span class="text-muted badge badge-danger text-dark">{{ translate('User unavailable') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transfer->receiver_type == 1)
                                    <span class="text-uppercase badge badge-light text-muted">{{translate('Agent')}}</span>
                                @elseif($transfer->receiver_type == 2)
                                    <span class="text-uppercase badge badge-light text-muted">{{translate('Customer')}}</span>
                                @endif
                            </td>
                            <td class="amount-column">
                                <span class="">
                                    {{ Helpers::set_symbol($transfer['amount']) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted badge badge-light">{{ $transfer->created_at->diffForHumans() }}</span>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-responsive mt-4 px-3">
                <div class="d-flex justify-content-end">
                    {!! $transfers->links() !!}
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>
        "use strict";

        $("#receiver").select2({
            ajax: {
                url: '{{route('admin.transfer.get_user')}}',
                type: "get",
                data: function (params) {
                    let receiver_type = $('#receiver_type').val();
                    if (receiver_type == null) {
                        swal('{{translate('Select_valid_receiver_type_first')}}');
                    }
                    return {
                        q: params.term,
                        page: params.page,
                        receiver_type: receiver_type
                    };

                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
                __port: function (params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });

        $('#receiver_type').on('change', function() {
            $('#receiver').empty();
        });
    </script>
@endpush
