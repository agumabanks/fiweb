@extends('layouts.admin.app')

@section('title', translate('OTP Setup'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2">
            <img width="24" src="{{asset('public/assets/admin/img/media/business-setup.png')}}" alt="{{ translate('business_setup') }}">
            <h2 class="page-header-title">{{translate('OTP Setup')}}</h2>
        </div>

        <div class="inline-page-menu my-4">
            @include('admin-views.business-settings.partial._business-setup-tabs')
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{route('admin.business-settings.otp_setup_update')}}" method="post">
                    @csrf
                    <div class="row">
                        <div class="col-sm-6 col-xl-4">
                            @php($maximumOtpHit=\App\CentralLogics\helpers::get_business_settings('maximum_otp_hit'))
                            <div class="form-group">
                                <label
                                    class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2"
                                    for="maximum_otp_hit">{{translate('maximum_OTP_submit_attempt')}}

                                    <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                                       title="{{ translate('The maximum OTP hit is a measure of how many times a specific one-time password has been generated and used within a time.') }}"></i>
                                </label>
                                <input type="number" name="maximum_otp_hit" class="form-control"
                                       id="maximum_otp_hit" value="{{$maximumOtpHit}}" min="1"
                                       step="1" required>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4">
                            @php($otpResendTime=\App\CentralLogics\helpers::get_business_settings('otp_resend_time'))
                            <div class="form-group">
                                <label class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2"
                                    for="otp_resend_time">{{translate('OTP_resend_time')}}
                                    <small class="text-danger">( {{translate('in_second')}} )</small>

                                    <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                                       title="{{ translate('If the user fails to get the OTP within a certain time, user can request a resend.') }}"></i>
                                </label>
                                <input type="number" name="otp_resend_time" class="form-control"
                                       id="otp_resend_time" value="{{$otpResendTime}}" min="1"
                                       step="1" required>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xl-4">
                            @php($temporaryBlockTime = \App\CentralLogics\helpers::get_business_settings('temporary_block_time'))
                            <div class="form-group">
                                <label
                                    class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2">{{translate('temporary_block_time')}}
                                    <small class="text-danger">( {{translate('in_second')}} )</small>

                                    <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                                       title="{{ translate('Temporary OTP block time refers to a security measure implemented by systems to restrict access to OTP service for a specified period of time for wrong OTP submission.') }}"></i>
                                </label>
                                <input type="number" name="temporary_block_time" class="form-control"
                                       value="{{$temporaryBlockTime}}" min="1" step="1" required>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-4">
                            @php($maximumLoginAttempt = \App\CentralLogics\helpers::get_business_settings('maximum_login_hit'))
                            <div class="form-group">
                                <label class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2">{{translate('maximum_login_attempt')}}

                                    <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                                       title="{{ translate('The maximum login hit is a measure of how many times a user can submit password within a time.') }}"></i>
                                </label>
                                <input type="number" name="maximum_login_hit" class="form-control"
                                       value="{{$maximumLoginAttempt}}" min="1" step="1" required>
                            </div>
                        </div>

                        <div class="col-sm-6 col-xl-4">
                            @php($temporaryLoginBlockTime=\App\CentralLogics\helpers::get_business_settings('temporary_login_block_time'))
                            <div class="form-group">
                                <label
                                    class="input-label text-capitalize d-flex flex-wrap align-items-center column-gap-2"
                                    for="temporary_login_block_time">{{translate('temporary_login_block_time')}}

                                    <i class="tio-info cursor-pointer" data-toggle="tooltip" data-placement="top"
                                       title="{{ translate('Temporary login block time refers to a security measure implemented by systems to restrict access for a specified period of time for wrong Password submission.') }}"></i>
                                </label>
                                <input type="number" name="temporary_login_block_time" class="form-control"
                                       id="temporary_login_block_time" value="{{$temporaryLoginBlockTime}}" min="1"
                                       step="1" required>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                                class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

