<div class="modal-header border-0 pb-0">
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<div class="modal-body d-flex align-items-center justify-content-center otp-modal min-h-500">
    <div class="row align-items-center pb-5">
        <div class="col-12 text-center">
            <div class="d-flex justify-content-center mb-4">
                <img width="172" src="{{asset('public/assets/landing/img/media/otp-verification.png')}}" alt="">
            </div>
            <h3 class="text-center mb-2">{{ translate('OTP_Verification') }}</h3>
            <p class="text-muted fs-13">{{translate('An OTP has been sent to')}}
                <strong>{{translate('your phone number')}}.</strong>
                <br> {{translate('Please enter the OTP in the field below to verify your email')}}.</p>
            @php
                $otpResendTime = \App\Models\BusinessSetting::where(['key' => 'otp_resend_time'])->first()?->value ?? 30;
            @endphp
            <div class="resend_otp_custom d-flex gap-2 justify-content-center align-items-center mb-5 fs-13">
                <p class="text-primary mb-0 ">{{ translate('resend_code_within') }}</p>
                <div class="text-primary mb-0 verifyTimer">
                    <span class="verifyCounter fw-bold" data-second="{{$otpResendTime}}"></span>
                </div>
            </div>

            <form class="otp-form" id="agent_otp_verify" method="POST" action="{{route('agent.verify-otp')}}">
                @csrf
                <div class="d-flex gap-2 gap-sm-3 align-items-end justify-content-center">
                    <input class="otp-field" type="text" name="otp_field[]" maxlength="1"
                           autocomplete="off" required>
                    <input class="otp-field" type="text" name="otp_field[]" maxlength="1"
                           autocomplete="off" required>
                    <input class="otp-field" type="text" name="otp_field[]" maxlength="1"
                           autocomplete="off" required>
                    <input class="otp-field" type="text" name="otp_field[]" maxlength="1"
                           autocomplete="off" required>
                </div>

                <input type="hidden" name="data" value="{{ json_encode($data) }}">
                <input type="hidden" id="phone_number" name="phone_number" value="{{ $phone }}">

                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <input class="otp_value" type="hidden" name="otp">
                <input class="identity" type="hidden" name="identity" value="{{ request('identity') }}">

                <div class="d-flex justify-content-center gap-3 mt-5">
                    <button class="btn btn-outline-primary resend-otp-button" type="button"
                            id="resend_otp">{{ translate('resend_OTP') }}</button>
                    <button class="btn btn-primary px-sm-5" type="submit">{{ translate('verify') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-body d-flex align-items-center justify-content-center loader d-none min-h-500"
     id="loader_otp">
    <div class="d-flex justify-content-center">
        <div class="custom-loader"></div>
    </div>
</div>

<div class="modal-body d-flex align-items-center justify-content-center reg-success d-none min-h-500">
    <div class="row align-items-center pb-5">
        <div class="col-12 text-center">
            <div class="d-flex justify-content-center mb-4">
                <img width="172" src="{{asset('public/assets/landing/img/media/otp-success.png')}}" alt="">
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
