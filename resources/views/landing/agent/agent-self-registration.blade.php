@extends('layouts.landing.app')

@section('title', translate('Agent Self Registration'))

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
@endpush

@section('content')
    <div class="overflow-hidden" data-bg-img="{{asset('public/assets/landing/img/media/page-header-bg.png')}}">
        <div class="container">
            <div class="page-header text-center">
                <h2 class="text-white mb-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300"><span
                        class="bg-primary text-white">{{translate('Agent')}}</span> {{translate('Registration')}}</h2>
                <p class="mx-w-480 mx-auto text-white fs-18" data-aos="fade-up" data-aos-duration="1000"
                   data-aos-delay="500">{{translate('Opening a new agent account is simple ! Join as agent in')}} {{\App\Models\BusinessSetting::where('key','business_name')->value('value') ?? '6cash'}}</p>
            </div>
        </div>
    </div>

    <section class="my-5">
        <div class="container">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-lg-5">
                    <form id="agent-form-store">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label for="f_name">{{translate('First Name')}}*</label>
                                    <input type="text" name="f_name" id="f_name" placeholder="Ex: John"
                                           class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label for="l_name">{{translate('Last Name')}}*</label>
                                    <input type="text" name="l_name" id="l_name" placeholder="Ex: Doe"
                                           class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label class="input-label d-block">{{translate('phone')}} *</label>
                                    <div class="input-group __input-grp">
                                        <select id="country_code" name="country_code" class="__input-grp-select"
                                                required>
                                            <option value="" disabled selected>{{ translate('select') }}</option>
                                            @foreach(PHONE_CODE as $countryCode)
                                                <option
                                                    value="{{ $countryCode['code'] }}" {{ $currentUserInfo && strpos($countryCode['name'], $currentUserInfo->countryName) !== false ? 'selected' : '' }}>{{ $countryCode['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <input type="tel" id="phone" name="phone" class="form-control __input-grp-input"
                                               value="{{ old('phone') }}"
                                               oninput="this.value = this.value.replace(/[^+\d]+$/g, '').replace(/(\..*)\./g, '$1');"
                                               placeholder="{{translate('Ex : 171*******')}}" required>
                                    </div>
                                    <div class="text-danger text-end fs-12" id="show-error-message"></div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label for="email">{{translate('Email')}}*</label>
                                    <input type="email" name="email" id="email" placeholder="Ex: yourmail@mail.com"
                                           class="form-control" value="{{ old('email') }}" required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="mb-4">
                                    <label class="input-label">{{translate('Occupation')}}</label>
                                    <input type="text" name="occupation" class="form-control"
                                           value="{{ old('occupation') }}"
                                           placeholder="{{translate('Ex : Businessman')}}"
                                           required>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <label class="input-label">{{translate('PIN')}}</label>
                                <div class="input-group input-group-merge mb-4">
                                    <input type="password" name="password"
                                           class="js-toggle-password form-control form-control input-field"
                                           placeholder="{{translate('4 digit PIN')}}" required maxlength="4" min="4"
                                           max="4"
                                           data-hs-toggle-password-options='{
                                                "target": "#changePassTarget",
                                                "defaultClass": "tio-hidden-outlined",
                                                "showClass": "tio-visible-outlined",
                                                "classChangeTarget": "#changePassIcon"
                                                }'>
                                    <div id="changePassTarget" class="input-group-append">
                                        <a class="input-group-text" href="javascript:">
                                            <i id="changePassIcon" class="tio-visible-outlined"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="" class="mb-3">{{translate('Select Gender')}}</label>
                                <ul class="option-select-btn">
                                    <li>
                                        <label class="basic-box-shadow">
                                            <input type="radio" name="gender" hidden="" checked="">
                                            <span><i class="bi bi-gender-male"></i></span>
                                        </label>
                                        <span>{{translate('Male')}}</span>
                                    </li>
                                    <li>
                                        <label class="basic-box-shadow">
                                            <input type="radio" name="gender" hidden="">
                                            <span><i class="bi bi-gender-female"></i></span>
                                        </label>
                                        <span>{{translate('Female')}}</span>
                                    </li>
                                    <li>
                                        <label class="basic-box-shadow">
                                            <input type="radio" name="gender" hidden="">
                                            <span><i class="bi bi-gender-trans"></i></span>
                                        </label>
                                        <span>{{translate('Other')}}</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-3 mt-4">
                                    <button type="reset" class="px-sm-5 btn btn-secondary">reset</button>
                                    <button type="submit" class="px-sm-5 btn btn-primary">submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal fade" id="success-modal" tabindex="-1" aria-labelledby="exampleModalLabel"
                 aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header border-0 pb-0">
                            <button type="button" class="btn-close btn-close-btn" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>
                        <div class="modal-body d-flex align-items-center justify-content-center">
                            <div class="row align-items-center pb-5">
                                <div class="col-12 text-center">
                                    <div class="d-flex justify-content-center mb-4">
                                        <img width="172"
                                             src="{{asset('public/assets/landing/img/media/otp-success.png')}}" alt="">
                                    </div>
                                    <h3 class="text-center mb-2">{{ translate('Registration_Successful !') }}</h3>
                                    <p class="text-muted fs-13 text-center mx-auto max-w-410">
                                        {{translate('Your account has been created. Please Download the agent
                                        app and login to your account and complete the verification process
                                        to enjoy all the features .')}}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        @include('landing.agent.partials.otp-modal')
    </section>
@endsection

@push('script_2')
    <script>
        "use strict";

        var timer;

        function checkPhoneNumber() {
            var phone = $('#country_code').val() + $('#phone').val();
            clearTimeout(timer);

            timer = setTimeout(function () {
                $.ajax({
                    type: 'POST',
                    url: '{{ route('agent.phone-number-check') }}',
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'phone': phone
                    },
                    success: function (response) {
                        if (response.available) {
                            $('#show-error-message').text('');
                        } else {
                            $('#show-error-message').text('This number is already used in another account');
                        }
                    }
                });
            }, 500);
        }

        $('#phone').on('keyup', checkPhoneNumber);

        function resendOtp(phone) {

            $.ajax({
                type: 'POST',
                url: '{{ route('agent.resend-otp') }}',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'phone': phone
                },
                success: function (data) {
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        if (data.status === 'success') {
                            toastr.success(data.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            countdown();
                        } else if (data.status === 'error') {
                            toastr.error(data.message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    }
                }
            });
        }

    </script>

    <script>

        function countdown() {
            var counter = $(".verifyCounter");
            var seconds = counter.data("second");

            function tick() {
                var m = Math.floor(seconds / 60);
                var s = seconds % 60;
                seconds--;
                counter.html(m + ":" + (s < 10 ? "0" : "") + String(s));
                if (seconds > 0) {
                    setTimeout(tick, 1000);
                    $(".resend-otp-button").attr("disabled", true);
                    $(".resend_otp_custom").slideDown();
                } else {
                    $(".resend-otp-button").removeAttr("disabled");
                    $(".verifyCounter").html("0:00");
                    $(".resend_otp_custom").slideUp();
                }
            }

            tick();
        }

        function otp_verification() {
            $(".otp-form button[type=submit]").attr("disabled", true);
            $(".otp-form *:input[type!=hidden]:first").focus();
            let otp_fields = $(".otp-form .otp-field"),
                otp_value_field = $(".otp-form .otp_value");
            otp_fields
                .on("input", function (e) {
                    $(this).val(
                        $(this)
                            .val()
                            .replace(/[^0-9]/g, "")
                    );
                    let otp_value = "";
                    otp_fields.each(function () {
                        let field_value = $(this).val();
                        if (field_value != "") otp_value += field_value;
                    });
                    otp_value_field.val(otp_value);
                    if (otp_value.length === 4) {
                        $(".otp-form button[type=submit]").attr("disabled", false);
                    } else {
                        $(".otp-form button[type=submit]").attr("disabled", true);
                    }
                })
                .on("keyup", function (e) {
                    let key = e.keyCode || e.charCode;
                    if (key == 8 || key == 46 || key == 37 || key == 40) {
                        $(this).prev().focus();
                    } else if (key == 38 || key == 39 || $(this).val() != "") {
                        $(this).next().focus();
                    }
                })
                .on("paste", function (e) {
                    let paste_data = e.originalEvent.clipboardData.getData("text");
                    let paste_data_splitted = paste_data.split("");
                    $.each(paste_data_splitted, function (index, value) {
                        otp_fields.eq(index).val(value);
                    });
                });
        }

    </script>

    <script>

        $('#agent-form-store').on('submit', function (event) {
            event.preventDefault();

            var form = $('#agent-form-store')[0];
            var formData = new FormData(form);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('agent.store-registration')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'POST',

                success: function (data) {
                    responseData = data;
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        if (data.flag === 'open_otp') {
                            $('#exampleModal').modal('show');
                            $('#activateData').empty().html(data.view);

                            countdown();
                            otp_verification();

                            document.getElementById('resend_otp').addEventListener('click', function (event) {
                                event.preventDefault(); // Prevent the default behavior
                                var phone = $('.otp-form #phone_number').val();
                                resendOtp(phone);
                            });

                        } else if (data.flag === 'phone_exists') {
                            toastr.error('{{ translate("This phone number is already taken.") }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        } else if (data.flag === 'failed_otp') {
                            toastr.error('{{ translate("Otp Failed!") }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        } else {
                            form.reset();
                            $('#show-error-message').text('');
                            $('#success-modal').modal('show');
                        }
                    }
                },
                complete: function (jqXHR, textStatus) {
                },
                error: function (jqXHR, exception) {
                    toastr.error(jqXHR.responseJSON.message);
                }
            });
        });
    </script>

    <script>

        $(document).on('submit', '#agent_otp_verify', function (event) {
            event.preventDefault();

            var form = $('#agent_otp_verify')[0];
            var formData = new FormData(form);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            var responseData;

            $.ajax({
                url: "{{route('agent.verify-otp')}}",
                data: formData,
                processData: false,
                contentType: false,
                type: 'POST',
                beforeSend: function () {
                    $('.otp-modal').addClass('d-none');
                    $('#loader_otp').removeClass('d-none');
                },
                success: function (data) {
                    responseData = data;
                    $('#agent-form-store')[0].reset();
                    if (data.errors) {
                        for (var i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        if (data.flag === 'failed_otp') {
                            toastr.error('{{ translate("Otp not found!") }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        } else {
                            $('.reg-success').removeClass('d-none');
                            $('.otp-modal').addClass('d-none');
                        }
                    }
                },
                complete: function (jqXHR, textStatus) {
                    $('#loader_otp').addClass('d-none');

                    if (textStatus === "success") {
                        if (responseData && responseData.flag !== 'failed_otp') {
                            $('.reg-success').removeClass('d-none');
                            $('.otp-modal').addClass('d-none');
                        } else {
                            toastr.error('{{ translate("Otp not found!") }}', {
                                CloseButton: true,
                                ProgressBar: true
                            });
                            $('.otp-modal').removeClass('d-none');
                            form.reset();
                        }
                    } else if (textStatus === "error") {
                        toastr.error(jqXHR.responseJSON.message);
                        $('.otp-modal').removeClass('d-none');
                    }
                },
                error: function (jqXHR, exception) {
                    toastr.error(jqXHR.responseJSON.message);
                }
            });
        });

    </script>

@endpush
