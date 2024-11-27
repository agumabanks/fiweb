@extends('layouts.admin.app')

@section('title', translate('reCaptcha Setup'))

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
            <div class="card-header d-flex flex-wrap gap-2 justify-content-between">
                <h5 class="mb-0">{{translate('reCaptcha')}}</h5>
                <a href="https://www.google.com/recaptcha/admin/create" target="_blank" class="btn btn-primary">
                    <i class="tio-info-outined"></i> {{translate('Credentials SetUp')}}
                </a>
            </div>
            <div class="card-body mt-3">
                @php($config=\App\CentralLogics\Helpers::get_business_settings('recaptcha'))
                <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.recaptcha_update',['recaptcha']):'javascript:'}}" method="post">
                    @csrf
                    <h6 class="mb-3">{{translate('Status')}}</h6>
                    <div class="d-flex flex-wrap gap-4 align-items-center mb-4">
                        <div class="d-flex align-items-center gap-2">
                            <input type="radio" name="status" id="recaptcha_active"
                                value="1" {{isset($config) && $config['status']==1?'checked':''}}>
                            <label class="mb-0 cursor-pointer" for="recaptcha_active">{{translate('active')}}</label>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <input type="radio" name="status" value="0" id="recaptcha_inactive" {{isset($config) && $config['status']==0?'checked':''}}>
                            <label class="mb-0 cursor-pointer" for="recaptcha_inactive">{{translate('inactive')}} </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-capitalize">{{translate('Site Key')}}</label><br>
                                <input type="text" class="form-control" name="site_key"
                                        value="{{env('APP_MODE')!='demo'?$config['site_key']??"":''}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="text-capitalize">{{translate('Secret Key')}}</label><br>
                                <input type="text" class="form-control" name="secret_key"
                                        value="{{env('APP_MODE')!='demo'?$config['secret_key']??"":''}}">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h5 class="mb-3">{{translate('Instructions')}}</h5>
                        <ol class="pl-3 d-flex flex-column gap-2">
                            <li>{{translate('Go to the Credentials page')}}
                                ({{translate('Click')}} <a
                                    href="https://www.google.com/recaptcha/admin/create"
                                    target="_blank">{{translate('here')}}</a>)
                            </li>
                            <li>{{translate('Add a ')}}
                                <b>{{translate('label')}}</b> {{translate('(Ex: Test Label)')}}
                            </li>
                            <li>
                                {{translate('Select reCAPTCHA v2 as ')}}
                                <b>{{translate('reCAPTCHA Type')}}</b>
                                ({{translate("Sub type: I'm not a robot Checkbox")}}
                                )
                            </li>
                            <li>
                                {{translate('Add')}}
                                <b>{{translate('domain')}}</b>
                                {{translate('(For ex: demo.6amtech.com)')}}
                            </li>
                            <li>
                                {{translate('Check in ')}}
                                <b>{{translate('Accept the reCAPTCHA Terms of Service')}}</b>
                            </li>
                            <li>
                                {{translate('Press')}}
                                <b>{{translate('Submit')}}</b>
                            </li>
                            <li>{{translate('Copy')}} <b>Site
                                    Key</b> {{translate('and')}} <b>Secret
                                    Key</b>, {{translate('paste in the input filed below and')}}
                                <b>Save</b>.
                            </li>
                        </ol>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"
                            class="btn btn-primary demo-form-submit">{{translate('save')}}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

