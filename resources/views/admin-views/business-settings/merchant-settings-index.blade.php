@extends('layouts.admin.app')

@section('title', translate('Merchant Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/settings.png')}}" alt="{{ translate('settings') }}">
            <h2 class="page-header-title">{{translate('Settings')}}</h2>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="">
                            <form action="{{route('admin.merchant-config.settings-update')}}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12 col-12">
                                        @php($merchantCommissionPercent=\App\CentralLogics\helpers::get_business_settings('merchant_commission_percent'))
                                        <div class="form-group">
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <label class="text-dark mb-0">{{translate('Transaction Commission')}}</label>
                                                <small class="text-danger"> *( {{translate('percent (%)')}} )</small>
                                            </div>

                                            <input type="number" name="merchant_commission_percent" class="form-control" id="merchant_commission_percent"
                                                   value="{{$merchantCommissionPercent??''}}" min="0" step=".02" max="100" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">{{translate('save')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
