@extends('layouts.admin.app')

@section('title', translate('FCM Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex align-items-center gap-3 pb-2">
            <img width="24" src="{{asset('public/assets/admin/img/media/business-setup.png')}}" alt="{{ translate('business_setup') }}">
            <h2 class="page-header-title">{{translate('Business Setup')}}</h2>
        </div>

        <div class="inline-page-menu my-4">
            @include('admin-views.business-settings.partial._business-setup-tabs')
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.update-fcm'):'javascript:'}}" method="post" enctype="multipart/form-data">
                    @csrf
                    @php($key=\App\Models\BusinessSetting::where('key','push_notification_key')->first())

                    <div class="form-group">
                        <label class="input-label">{{translate('server')}} {{translate('key')}}</label>
                        <textarea name="push_notification_key" class="form-control" required>{{env('APP_MODE')!='demo'?$key->value??'':''}}</textarea>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                            class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{translate('push')}} {{translate('messages')}}</h5>
            </div>
            <div class="card-body">
                <form action="{{route('admin.business-settings.update-fcm-messages')}}" method="post"
                        enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        @php($data = \App\CentralLogics\Helpers::get_business_settings('money_transfer_message'))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="money_transfer_status">
                                        <input type="checkbox" name="money_transfer_status" class="switcher_input" value="1" id="money_transfer_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('EMoney Transfer Message')}}</span>
                                </div>

                                <textarea name="money_transfer_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings(CASH_IN))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="cash_in_status">
                                        <input type="checkbox" name="cash_in_status"
                                                class="switcher_input"
                                                value="1"
                                                id="cash_in_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Cash In Message')}}</span>
                                </div>

                                <textarea name="cash_in_message"
                                            class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings(CASH_OUT))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher"
                                            for="cash_out_status">
                                        <input type="checkbox" name="cash_out_status"
                                                class="switcher_input"
                                                value="1"
                                                id="cash_out_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Cash Out Message')}}</span>
                                </div>

                                <textarea name="cash_out_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings(SEND_MONEY))
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher"
                                            for="send_money_status">
                                        <input type="checkbox" name="send_money_status"
                                                class="switcher_input"
                                                value="1"
                                                id="send_money_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Send Money Message')}}</span>
                                </div>

                                <textarea name="send_money_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings('request_money'))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="request_money_status">
                                        <input type="checkbox" name="request_money_status" class="switcher_input"
                                                value="1" id="request_money_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Request Money Message')}}</span>
                                </div>

                                <textarea name="request_money_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings('approved_money'))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="approved_money_status">
                                        <input type="checkbox" name="approved_money_status" class="switcher_input"
                                                value="1" id="approved_money_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Approved Money Message')}}</span>
                                </div>

                                <textarea name="approved_money_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings('denied_money'))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="denied_money_status">
                                        <input type="checkbox" name="denied_money_status" class="switcher_input"
                                                value="1" id="denied_money_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Denied Money Message')}}</span>
                                </div>

                                <textarea name="denied_money_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings(ADD_MONEY))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="add_money_status">
                                        <input type="checkbox" name="add_money_status"
                                                class="switcher_input"
                                                value="1"
                                                id="add_money_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Add Money Message')}}</span>
                                </div>

                                <textarea name="add_money_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings(ADD_MONEY_BONUS))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="add_money_bonus_status">
                                        <input type="checkbox" name="add_money_bonus_status"
                                                class="switcher_input"
                                                value="1"
                                                id="add_money_bonus_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Add Money Bonus Message')}}</span>
                                </div>

                                <textarea name="add_money_bonus_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings(RECEIVED_MONEY))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher"
                                            for="received_money_status">
                                        <input type="checkbox" name="received_money_status"
                                                class="switcher_input"
                                                value="1"
                                                id="received_money_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Received Money Message')}}</span>
                                </div>

                                <textarea name="received_money_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                        @php($data = \App\CentralLogics\Helpers::get_business_settings(PAYMENT))
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <label class="switcher" for="payment_money_status">
                                        <input type="checkbox" name="payment_money_status"
                                                class="switcher_input"
                                                value="1"
                                                id="payment_money_status" {{$data?($data['status']==1?'checked':''):''}}>
                                        <span class="switcher_control"></span>
                                    </label>
                                    <span>{{translate('Add Payment Message')}}</span>
                                </div>

                                <textarea name="payment_money_message" class="form-control">{{$data['message']??''}}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
