@extends('layouts.admin.app')

@section('title', translate('Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2">
            <img width="24" src="{{asset('public/assets/admin/img/media/business-setup.png')}}" alt="{{ translate('business_setup') }}">
            <h2 class="page-header-title">{{translate('Business Setup')}}</h2>
        </div>

        <div class="inline-page-menu my-4">
            @include('admin-views.business-settings.partial._business-setup-tabs')
        </div>

        <div class="card">
            <div class="card-body">
                    <form action="{{route('admin.business-settings.system_feature_update')}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-sm-6 col-xl-4">
                                @php($addMoneyStatus=\App\CentralLogics\Helpers::get_business_settings('add_money_status'))
                                <div class="d-flex flex-wrap flex-grow-1 justify-content-between mb-4 mb-4">
                                    <span class="text-dark">
                                        {{translate('Add Money')}}
                                    </span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="hidden" name="add_money_status" value="0">
                                        <input type="checkbox" name="add_money_status" value="1" class="toggle-switch-input" {{ isset($addMoneyStatus) && $addMoneyStatus==1?'checked':''}}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($sendMoneyStatus=\App\CentralLogics\Helpers::get_business_settings('send_money_status'))
                                <div class="d-flex flex-wrap flex-grow-1 justify-content-between mb-4">
                                    <span class="text-dark">
                                        {{translate('Send Money')}}

                                    </span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="hidden" name="send_money_status" value="0">
                                        <input type="checkbox" name="send_money_status" value="1" class="toggle-switch-input" {{ isset($sendMoneyStatus) && $sendMoneyStatus==1?'checked':''}}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($cashOutStatus=\App\CentralLogics\Helpers::get_business_settings('cash_out_status'))
                                <div class="d-flex flex-wrap flex-grow-1 justify-content-between mb-4">
                                    <span class="text-dark">
                                        {{translate('Cash Out')}}

                                    </span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="hidden" name="cash_out_status" value="0">
                                        <input type="checkbox" name="cash_out_status" value="1" class="toggle-switch-input" {{ isset($cashOutStatus) && $cashOutStatus==1?'checked':''}}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($sendMoneyRequestStatus=\App\CentralLogics\Helpers::get_business_settings('send_money_request_status'))
                                <div class="d-flex flex-wrap flex-grow-1 justify-content-between mb-4">
                                    <span class="text-dark">
                                        {{translate('Request Money')}}

                                    </span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="hidden" name="send_money_request_status" value="0">
                                        <input type="checkbox" name="send_money_request_status" value="1" class="toggle-switch-input" {{ isset($sendMoneyRequestStatus) && $sendMoneyRequestStatus==1?'checked':''}}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($withdrawRequestStatus=\App\CentralLogics\Helpers::get_business_settings('withdraw_request_status'))
                                <div class="d-flex flex-wrap flex-grow-1 justify-content-between mb-4">
                                    <span class="text-dark">
                                        {{translate('Withdraw Request')}}

                                    </span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="hidden" name="withdraw_request_status" value="0">
                                        <input type="checkbox" name="withdraw_request_status" value="1" class="toggle-switch-input" {{ isset($withdrawRequestStatus) && $withdrawRequestStatus==1?'checked':''}}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xl-4">
                                @php($linkedWebsiteStatus=\App\CentralLogics\Helpers::get_business_settings('linked_website_status'))
                                <div class="d-flex flex-wrap flex-grow-1 justify-content-between mb-4">
                                    <span class="text-dark">
                                        {{translate('Linked Website')}}

                                    </span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="hidden" name="linked_website_status" value="0">
                                        <input type="checkbox" name="linked_website_status" value="1" class="toggle-switch-input" {{ isset($linkedWebsiteStatus) && $linkedWebsiteStatus==1?'checked':''}}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                @php($bannerStatus=\App\CentralLogics\Helpers::get_business_settings('banner_status'))
                                <div class="d-flex flex-wrap flex-grow-1 justify-content-between mb-4">
                                    <span class="text-dark">
                                        {{translate('Banner')}}

                                    </span>
                                    <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                        <input type="hidden" name="banner_status" value="0">
                                        <input type="checkbox" name="banner_status" value="1" class="toggle-switch-input" {{ isset($bannerStatus) && $bannerStatus==1?'checked':''}}>
                                        <span class="toggle-switch-label text">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary demo-form-submit">{{ translate('submit') }}</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>

@endsection
