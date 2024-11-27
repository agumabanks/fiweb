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
                <ul class="list-unstyled">
                    <li class="{{Request::is('admin/business-settings/mail-config')?'active':''}}"><a href="{{route('admin.business-settings.mail_config')}}">{{translate('Mail Config')}}</a></li>
                    <li class="{{Request::is('admin/business-settings/send-mail-index')?'active':''}}"><a href="{{route('admin.business-settings.send_mail_index')}}">{{translate('Send Test Mail')}}</a></li>
                </ul>
            </ul>
        </div>

        <div class="mt-3">
            @php($data = \App\CentralLogics\helpers::get_business_settings('mail_config'))
            <form action="javascript:" method="post">
                @csrf
                <input type="hidden" name="status" value="{{(isset($data)&& isset($data['status'])) ? $data['status']:0 }}">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="test-mail">
                                <div class="row">
                                    <div class="col-lg-8">
                                        <form action="javascript:">
                                            <label class="form-label">{{translate('Email')}}</label>
                                            <div class="row gx-3 gy-1">
                                                <div class="col-md-8 col-sm-7">
                                                    <div>
                                                        <label for="inputPassword2" class="sr-only">
                                                            {{ translate('mail') }}</label>
                                                        <input type="email" id="test-email" class="form-control"
                                                            placeholder="Ex: jhon@email.com">
                                                    </div>
                                                </div>
                                                <div class="col-md-4 col-sm-5">
                                                    <button type="button" id="send-mail" class="btn btn-primary h--45px btn-block">
                                                        <i class="tio-telegram"></i>
                                                        {{ translate('send_mail') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

        <div class="modal fade" id="sent-mail-modal">
            <div class="modal-dialog status-warning-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">
                            <span aria-hidden="true" class="tio-clear"></span>
                        </button>
                    </div>
                    <div class="modal-body pt-0">
                        <div class="text-center mb-20">
                            <img src="{{asset('/public/assets/admin/img/sent-mail-box.png')}}" alt="{{ translate('mail-box') }}" class="mb-20">
                            <h5 class="modal-title">{{translate('Congratulations! Your SMTP mail has been setup successfully!')}}</h5>
                            <p class="txt">
                                {{translate("Go to test mail to check that its work perfectly or not!")}}
                            </p>
                        </div>
                        <div class="d-flex justify-content-center">
                            <a href="{{route('admin.business-settings.send_mail_index')}}" class="btn btn--primary min-w-120">
                                <img src="{{asset('/public/assets/admin/img/paper-plane.png')}}" alt=""> {{translate('Send Test Mail')}}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('script_2')
<script>
    "use strict";

    function ValidateEmail(inputText) {
        let mailformat = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
        return !!inputText.match(mailformat);
    }

    $('#send-mail').on('click', function (){
        if (ValidateEmail($('#test-email').val())) {
            Swal.fire({
                title: '{{ translate('Are you sure?') }}?',
                text: "{{ translate('a_test_mail_will_be_sent_to_your_email') }}!",
                showCancelButton: true,
                confirmButtonColor: '#00868F',
                cancelButtonColor: 'secondary',
                confirmButtonText: '{{ translate('Yes') }}!'
            }).then((result) => {
                if (result.value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    });
                    $.ajax({
                        url: "{{ route('admin.business-settings.send_mail') }}",
                        method: 'GET',
                        data: {
                            "email": $('#test-email').val()
                        },
                        beforeSend: function() {
                            $('#loading').show();
                        },
                        success: function(data) {
                            if (data.success === 2) {
                                toastr.error(
                                    '{{ translate('email_configuration_error') }} !!'
                                );
                            } else if (data.success === 1) {
                                toastr.success(
                                    '{{ translate('email_configured_perfectly!') }}!'
                                );
                            } else {
                                toastr.info(
                                    '{{ translate('email_status_is_not_active') }}!'
                                );
                            }
                        },
                        complete: function() {
                            $('#loading').hide();

                        }
                    });
                }
            })
        } else {
            toastr.error('{{ translate('invalid_email_address') }} !!');
        }
    });
</script>
@endpush
