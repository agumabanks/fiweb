@extends('layouts.admin.app')

@section('title', translate('Details'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/agent.png')}}" alt="{{ translate('user') }}">
            <h2 class="page-header-title">{{translate('Details')}}</h2>
        </div>

        <div class="page-header">
            <div class="js-nav-scroller hs-nav-scroller-horizontal">
                @include('admin-views.view.partails.navbar')
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header text-capitalize">
                        <h5 class="mb-0">{{translate('wallet')}}</h5>
                    </div>
                    <div class="card-body">
                        <div>
                            <div class="dashboard--card h-100">
                                <h6 class="subtitle">{{translate('balance')}}</h6>
                                <h2 class="title">{{ Helpers::set_symbol($user->emoney['current_balance']??0) }}</h2>
                                <img src="{{ asset('public/assets/admin/img/media/dollar-1.png') }}" class="dashboard-icon" alt="{{ translate('dollar') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card word-break">
                    <div class="card-header text-capitalize">
                        <h5 class="mb-0">{{translate('Personal Info')}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0 word-nobreak">{{translate('name')}} : </h5>
                                <div class="text-dark">{{$user['f_name']??''}} {{$user['l_name']??''}}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0 word-nobreak">{{translate('Phone')}} : </h5>
                                <div class="text-dark">{{$user['phone']??''}}</div>
                            </div>
                            @if(isset($user['email']))
                                <div class="d-flex align-items-center gap-2">
                                   <h5 class="mb-0 word-nobreak">{{translate('Email')}} : </h5>
                                    <div class="text-dark">{{$user['email']}}</div>
                                </div>
                            @endif
                            @if(isset($user['identification_type']))
                                <div class="d-flex align-items-center gap-2">
                                   <h5 class="mb-0 word-nobreak">{{translate('identification_type')}} : </h5>
                                    <div class="text-dark">{{translate($user['identification_type'])}}</div>
                                </div>
                            @endif
                            @if(isset($user['identification_number']))
                                <div class="d-flex align-items-center gap-2">
                                   <h5 class="mb-0 word-nobreak">{{translate('identification_number')}} : </h5>
                                    <div class="text-dark">{{$user['identification_number']}}</div>
                                </div>
                            @endif
                            @if($user['type'] == 3)
                                @if(isset($user->merchant))
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 word-nobreak">{{translate('store_name')}} : </h5>
                                        <div class="text-dark">{{$user->merchant['store_name']}}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 word-nobreak">{{translate('store_callback')}} : </h5>
                                        <div class="text-dark">{{$user->merchant['callback']}}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 word-nobreak">{{translate('address')}} : </h5>
                                        <div class="text-dark">{{$user->merchant['address']}}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 word-nobreak">{{translate('BIN')}} : </h5>
                                        <div class="text-dark">{{$user->merchant['bin']}}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 word-nobreak">{{translate('public_key')}} : </h5>
                                        <div class="text-dark">{{$user->merchant['public_key']}}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 word-nobreak">{{translate('secret_key')}} : </h5>
                                        <div class="text-dark">{{$user->merchant['secret_key']}}</div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <h5 class="mb-0 word-nobreak">{{translate('merchant_number')}} : </h5>
                                        <div class="text-dark">{{$user->merchant['merchant_number']}}</div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
