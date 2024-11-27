@extends('layouts.admin.app')

@section('title', translate('Merchant OTP'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 mb-3">
            <img width="24" src="{{asset('public/assets/admin/img/media/credit-card.png')}}" alt="{{ translate('image') }}">
            <h2 class="page-header-title">{{translate('Payment OTP Verification')}}</h2>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">{{translate('OTP')}}</h3>
                    </div>
                    <div class="card-body">

                        @php($otpStatus=\App\CentralLogics\Helpers::get_business_settings('payment_otp_verification'))
                        <form action="{{route('admin.merchant-config.merchant-payment-otp-verification-update')}}" method="post">
                            @csrf
                            <div class="mb-2 d-flex align-items-center gap-2">
                                <input type="radio" name="payment_otp_verification"
                                        value="1" {{isset($otpStatus) && $otpStatus==1?'checked':''}}>
                                <label class="mb-0">{{translate('active')}}</label>
                                <br>
                            </div>
                            <div class="form-group d-flex align-items-center gap-2">
                                <input type="radio" name="payment_otp_verification"
                                        value="0" {{isset($otpStatus) && $otpStatus==0?'checked':''}}>
                                <label class="mb-0">{{translate('inactive')}} </label>
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
@endsection
