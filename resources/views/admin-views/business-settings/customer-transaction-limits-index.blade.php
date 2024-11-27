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

        <div class="inline-page-menu my-4">
            <ul class="list-unstyled">
                <li class="{{Request::is('admin/business-settings/customer-transaction-limits')?'active':''}}"><a href="{{route('admin.business-settings.customer_transaction_limits')}}">{{translate('Customer')}}</a></li>
                <li class="{{Request::is('admin/business-settings/agent-transaction-limits')?'active':''}}"><a href="{{route('admin.business-settings.agent_transaction_limits')}}">{{translate('Agent')}}</a></li>
            </ul>
        </div>

        <div class="mt-3">
            @php($config=\App\CentralLogics\Helpers::get_business_settings('customer_add_money_limit'))
            <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.transaction_limits_update',['customer_add_money_limit']):'javascript:'}}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap flex-grow-1 justify-content-between">
                        <span class="text-dark"><i class="tio-money"></i>
                            {{translate('Add Money Limit')}}
                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                               title="{{ translate('When this feature is enabled, transaction limits will be applied on a daily and monthly basis.') }}">
                            </i>
                        </span>
                            <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="toggle-switch-input" {{ isset($config) && $config['status']==1?'checked':''}}>
                                <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Daily Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a day is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_day" value="{{$config['transaction_limit_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 5')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Max Amount per Transaction')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum amount of money that can be used in a single transaction is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="max_amount_per_transaction" value="{{$config['max_amount_per_transaction']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 100')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction Amount Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a day.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_day" value="{{$config['total_transaction_amount_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Monthly Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a month is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_month" value="{{$config['transaction_limit_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 10')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction_amount_per_month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a month.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_month" value="{{$config['total_transaction_amount_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-3">
            @php($config=\App\CentralLogics\Helpers::get_business_settings('customer_send_money_limit'))
            <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.transaction_limits_update',['customer_send_money_limit']):'javascript:'}}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap flex-grow-1 justify-content-between">
                        <span class="text-dark"><i class="tio-money"></i>
                            {{translate('Send Money Limit')}}
                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                               title="{{ translate('When this feature is enabled, transaction limits will be applied on a daily and monthly basis.') }}">
                            </i>
                        </span>
                            <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="toggle-switch-input" {{ isset($config) && $config['status']==1?'checked':''}}>
                                <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Daily Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a day is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_day" value="{{$config['transaction_limit_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 5')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Max Amount per Transaction')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum amount of money that can be used in a single transaction is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="max_amount_per_transaction" value="{{$config['max_amount_per_transaction']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 100')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction Amount Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a day.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_day" value="{{$config['total_transaction_amount_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Monthly Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a month is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_month" value="{{$config['transaction_limit_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 10')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction_amount_per_month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a month.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_month" value="{{$config['total_transaction_amount_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-3">
            @php($config=\App\CentralLogics\Helpers::get_business_settings('customer_send_money_request_limit'))
            <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.transaction_limits_update',['customer_send_money_request_limit']):'javascript:'}}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap flex-grow-1 justify-content-between">
                        <span class="text-dark"><i class="tio-money"></i>
                            {{translate('Money Request Limit')}}
                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                               title="{{ translate('When this feature is enabled, transaction limits will be applied on a daily and monthly basis.') }}">
                            </i>
                        </span>
                            <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="toggle-switch-input" {{ isset($config) && $config['status']==1?'checked':''}}>
                                <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Daily Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a day is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_day" value="{{$config['transaction_limit_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 5')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Max Amount per Transaction')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum amount of money that can be used in a single transaction is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="max_amount_per_transaction" value="{{$config['max_amount_per_transaction']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 100')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction Amount Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a day.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_day" value="{{$config['total_transaction_amount_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Monthly Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a month is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_month" value="{{$config['transaction_limit_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 10')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction_amount_per_month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a month.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_month" value="{{$config['total_transaction_amount_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-3">
            @php($config=\App\CentralLogics\Helpers::get_business_settings('customer_cash_out_limit'))
            <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.transaction_limits_update',['customer_cash_out_limit']):'javascript:'}}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap flex-grow-1 justify-content-between">
                        <span class="text-dark"><i class="tio-money"></i>
                            {{translate('Cash Out Limit')}}
                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                               title="{{ translate('When this feature is enabled, transaction limits will be applied on a daily and monthly basis.') }}">
                            </i>
                        </span>
                            <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="toggle-switch-input" {{ isset($config) && $config['status']==1?'checked':''}}>
                                <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Daily Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a day is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_day" value="{{$config['transaction_limit_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 5')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Max Amount per Transaction')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum amount of money that can be used in a single transaction is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="max_amount_per_transaction" value="{{$config['max_amount_per_transaction']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 100')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction Amount Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a day.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_day" value="{{$config['total_transaction_amount_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Monthly Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a month is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_month" value="{{$config['transaction_limit_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 10')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction_amount_per_month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a month.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_month" value="{{$config['total_transaction_amount_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="mt-3">
            @php($config=\App\CentralLogics\Helpers::get_business_settings('customer_withdraw_request_limit'))
            <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.transaction_limits_update',['customer_withdraw_request_limit']):'javascript:'}}" method="post">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex flex-wrap flex-grow-1 justify-content-between">
                        <span class="text-dark"><i class="tio-money"></i>
                            {{translate('Withdraw Request Limit')}}
                            <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                               title="{{ translate('When this feature is enabled, transaction limits will be applied on a daily and monthly basis.') }}">
                            </i>
                        </span>
                            <label class="switch--custom-label toggle-switch toggle-switch-sm d-inline-flex">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" name="status" value="1" class="toggle-switch-input" {{ isset($config) && $config['status']==1?'checked':''}}>
                                <span class="toggle-switch-label text">
                                <span class="toggle-switch-indicator"></span>
                            </span>
                            </label>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Daily Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a day is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_day" value="{{$config['transaction_limit_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 5')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Max Amount per Transaction')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum amount of money that can be used in a single transaction is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="max_amount_per_transaction" value="{{$config['max_amount_per_transaction']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 100')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-6">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction Amount Per Day')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a day.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_day" value="{{$config['total_transaction_amount_per_day']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <h6 class="mb-0 text-capitalize d-flex align-items-center gap-1">
                                        {{translate('Monthly Transaction')}}
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Transaction Limit Per Month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('The maximum number of transactions allowed in a month is set by this setting.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="transaction_limit_per_month" value="{{$config['transaction_limit_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 10')}}" step="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-xl-12">
                                        <div class="form-group">
                                            <label class="input-label" for="exampleFormControlInput1">{{translate('Total Transaction_amount_per_month')}}
                                                <i class="tio-info cursor-pointer text-primary" data-toggle="tooltip" data-placement="top"
                                                   title="{{ translate('This field refers to the maximum amount of money for transactions in a month.') }}">
                                                </i>
                                            </label>
                                            <input type="number" name="total_transaction_amount_per_month" value="{{$config['total_transaction_amount_per_month']??''}}" class="form-control"
                                                   placeholder="{{translate('Ex: 500')}}" step="any" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <button type="reset" class="btn btn-secondary">{{translate('reset')}}</button>
                            <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary demo-form-submit">{{translate('submit')}}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
