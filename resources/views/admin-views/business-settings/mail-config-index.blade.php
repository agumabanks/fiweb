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
                <li class="{{Request::is('admin/business-settings/mail-config')?'active':''}}"><a href="{{route('admin.business-settings.mail_config')}}">{{translate('Mail Config')}}</a></li>
                <li class="{{Request::is('admin/business-settings/send-mail-index')?'active':''}}"><a href="{{route('admin.business-settings.send_mail_index')}}">{{translate('Send Test Mail')}}</a></li>
            </ul>
        </div>

        <div class="mt-3">
            @php($data = \App\CentralLogics\Helpers::get_business_settings('mail_config'))
            <form action="{{route('admin.business-settings.mail_config_status')}}" method="post" id="mail-config-disable_form">
            @csrf
                <div class="form-group text-center d-flex flex-wrap align-items-center">
                    <label class="toggle-switch h--45px toggle-switch-sm d-flex justify-content-between border rounded px-3 py-0 form-control mb-2">
                        <span class="pr-1 d-flex align-items-center switch--label text--primary">
                            <span class="text-primary">
                                {{isset($data) && isset($data['status'])&&$data['status']==1?translate('Turn OFF'):translate('Turn ON')}}
                            </span>
                        </span>
                        <input class="toggle-switch-input" id="mail-config-disable" type="checkbox"
                               onclick="toogleStatusModal(event,'mail-config-disable','mail-success.png','mail-warning.png','{{translate('Important!')}}','{{translate('Warning!')}}',`<p>{{translate('Enabling mail configuration services will allow the system to send emails. Please ensure that you have correctly configured the SMTP settings to avoid potential issues with email delivery.')}}</p>`,`<p>{{translate('Disabling mail configuration services will prevent the system from sending emails. Please only turn off this service if you intend to temporarily suspend email sending. Note that this may affect system functionality that relies on email communication.')}}</p>`)" name="status" value="1" {{isset($data) && isset($data['status'])&&$data['status']==1?'checked':''}}>
                        <span class="toggle-switch-label text p-0">
                            <span class="toggle-switch-indicator"></span>
                        </span>
                    </label>
                    <small>{{translate('*By Turning OFF mail configuration, all your mailing services will be off.')}}</small>
                </div>
            </form>

            <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.mail_config_update',['mail_config']):'javascript:'}}" method="post">
                @csrf
                <input type="hidden" name="status" value="{{(isset($data)&& isset($data['status'])) ? $data['status']:0 }}">
                <div class="disable-on-turn-of {{isset($data) && isset($data['status'])&&$data['status']==1?'':'inactive'}}">
                    <div class="row g-3">
                    <div class="col-sm-12">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('mailer_name') }}</label><br>
                            <input type="text" placeholder="{{ translate('Ex:') }} Alex" class="form-control" name="name"
                                value="{{ env('APP_MODE') != 'demo' ? $data['name'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('host') }}</label><br>
                            <input type="text" class="form-control" name="host" placeholder="{{translate('Ex_:_mail.6am.one')}}"
                                value="{{ env('APP_MODE') != 'demo' ? $data['host'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('driver') }}</label><br>
                            <input type="text" class="form-control" name="driver" placeholder="{{translate('Ex : smtp')}}"
                                value="{{ env('APP_MODE') != 'demo' ? $data['driver'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('port') }}</label><br>
                            <input type="text" class="form-control" name="port" placeholder="{{translate('Ex : 587')}}"
                                value="{{ env('APP_MODE') != 'demo' ? $data['port'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('username') }}</label><br>
                            <input type="text" placeholder="{{ translate('Ex:') }} ex@yahoo.com" class="form-control" name="username"
                                value="{{ env('APP_MODE') != 'demo' ? $data['username'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('email_id') }}</label><br>
                            <input type="text" placeholder="{{ translate('Ex:') }} ex@yahoo.com" class="form-control" name="email"
                                value="{{ env('APP_MODE') != 'demo' ? $data['email_id'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('encryption') }}</label><br>
                            <input type="text" placeholder="{{ translate('Ex:') }} tls" class="form-control" name="encryption"
                                value="{{ env('APP_MODE') != 'demo' ? $data['encryption'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label class="form-label">{{ translate('password') }}</label><br>
                            <input type="text" class="form-control" name="password" placeholder="{{translate('Ex : 5+ Characters')}}"
                                value="{{ env('APP_MODE') != 'demo' ? $data['password'] ?? '' : '' }}" required>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="d-flex justify-content-end">
                            <button type="reset" class="btn btn-reset">{{translate('reset')}}</button>
                            <button type="{{ env('APP_MODE') != 'demo' ? 'submit' : 'button' }}"
                                    class="btn btn-primary demo-form-submit">{{ translate('save') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="toggle-status-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-5">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="{{ translate('image') }}" class="mb-5">
                                <h5 class="modal-title" id="toggle-status-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-status-message">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn--primary min-w-120 mr-3" data-dismiss="modal">
                                {{translate('Ok')}}
                            </button>
                            <button id="reset_btn" type="reset" class="btn btn-warning min-w-120" data-dismiss="modal">
                                {{translate("Cancel")}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script_2')
    <script>

    function toogleStatusModal(e, toggle_id, on_image, off_image, on_title, off_title, on_message, off_message) {
        e.preventDefault();
        if ($('#'+toggle_id).is(':checked')) {
            $('#toggle-status-title').empty().append(on_title);
            $('#toggle-status-message').empty().append(on_message);
            $('#toggle-status-image').attr('src', "{{asset('/public/assets/admin/img/modal')}}/"+on_image);
            $('#toggle-status-ok-button').attr('toggle-ok-button', toggle_id);
        } else {
            $('#toggle-status-title').empty().append(off_title);
            $('#toggle-status-message').empty().append(off_message);
            $('#toggle-status-image').attr('src', "{{asset('/public/assets/admin/img/modal')}}/"+off_image);
            $('#toggle-status-ok-button').attr('toggle-ok-button', toggle_id);
        }
        $('#toggle-status-modal').modal('show');
    }
    $('#toggle-status-ok-button').on('click', function (){
        var toggle_id = $('#toggle-status-ok-button').attr('toggle-ok-button');
        if ($('#'+toggle_id).is(':checked')) {
            $('#'+toggle_id).prop('checked', false);
            $('#'+toggle_id).val(0);
        } else {
            $('#'+toggle_id).prop('checked', true);
            $('#'+toggle_id).val(1);
        }
        $('#'+toggle_id+'_form').submit();
    });
        const disableMailConf = () => {
            if($('#mail-config-disable').is(':checked')) {
                $('.disable-on-turn-of').removeClass('inactive')
            }else {
                $('.disable-on-turn-of').addClass('inactive')
            }
        }
        $('#mail-config-disable').on('change', function(){
            disableMailConf()
        })
    </script>
@endpush
