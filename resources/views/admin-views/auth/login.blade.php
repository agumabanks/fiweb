<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{translate('Admin')}} | {{translate('Login')}}</title>

    <link rel="shortcut icon" href="{{asset('storage/app/public/favicon')}}/{{Helpers::get_business_settings('favicon') ?? null}}"/>

    <link rel="stylesheet" href="{{asset('public/assets/admin/css/fonts/google/google.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/theme.minc619.css?v=1.0')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/toastr.css')}}">

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
<main id="content" role="main" class="main h-100vh d-flex flex-column justify-content-center">
    <div class="position-fixed top-0 right-0 left-0 bottom-0 bg-img-hero h-100"
         style="background-image: url({{asset('public/assets/admin')}}/svg/components/login_background.svg);opacity: 0.5">
    </div>
    <div class="container d-flex justify-content-center py-5">
        <div class="login-card d-inline-block">
            <div class="row no-gutters">
                <div class="col-md-6">
                    <div class="bg-primary h-100 d-flex align-items-center justify-content-center py-5">
                        <div class="text-center">
                            <h1 class="text-white text-uppercase ">{{ translate('SANAA' ) }}</h1>
                            <hr class="bg-white with-40-percent">
                            <div class="text-white text-uppercase">
                                <span class="w-50 d-inline-block">
                                    {{ translate( 'A secured and user-friendly Financial SaaS') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-white h-100 d-flex align-items-center justify-content-center py-5 px-4">
                        <form class="" action="{{route('admin.auth.login')}}" method="post" id="form-id">
                            @csrf
                            <div class="text-center">
                                <div class="mb-5">
                                    <h2 class="text-capitalize">{{translate('sign in')}}</h2>
                                </div>
                            </div>

                            <div class="js-form-message form-group">
                                <input type="text" class="form-control form-control-lg input-field" name="phone"
                                    id="phone" required
                                    tabindex="1" placeholder="{{translate('Enter your phone no.')}}"
                                    data-msg="{{translate('Please enter a valid phone number.')}}">
                            </div>
                            <div class="js-form-message form-group">
                                <div class="input-group input-group-merge">
                                    <input type="password"
                                        class="js-toggle-password form-control form-control-lg input-field"
                                        name="password" id="signupSrPassword"
                                        placeholder="{{translate('Enter your password')}}"
                                        aria-label="8+ characters required" required
                                        data-msg="{{translate('Your password is invalid. Please try again.')}}"
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
                            <!-- @php($recaptcha = Helpers::get_business_settings('recaptcha'))
                            @if(isset($recaptcha) && $recaptcha['status'] == 1)
                                <div class="with-100-percent" id="recaptcha_element" data-type="image"></div>
                                <br/>
                            @else
                                <div class="row p-2">
                                    <div class="col-6 pr-0">
                                        <input type="text" class="form-control form-control-lg border-none" name="default_captcha_value" value=""
                                            placeholder="{{translate('Enter captcha')}}" autocomplete="off">
                                    </div>
                                    <div class="col-6 input-icons bg-white rounded cursor-pointer" data-toggle="tooltip" data-placement="right" title="{{translate('Click to refresh')}}">
                                        <a  class="refresh-recaptcha">
                                            <img src="{{ URL('/admin/auth/code/captcha/1') }}" class="input-field h-75 rounded-10 border-bottom-0 width-90-percent"
                                                 id="default_recaptcha_id" alt="{{ translate('recaptcha') }}">
                                            <i class="tio-refresh icon"></i>

                                        </a>

                                    </div>
                                </div>
                            @endif -->

                           

                            <div class="d-flex justify-content-center mt-5">
                                <button type="submit" class="btn btn-primary  sign-in-button">{{translate('sign_in')}}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="{{asset('public/assets/admin/js/vendor.min.js')}}"></script>

<script src="{{asset('public/assets/admin/js/theme.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/toastr.js')}}"></script>
{!! Toastr::message() !!}

@if ($errors->any())
    <script>
        @foreach($errors->all() as $error)
        toastr.error('{{$error}}', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        @endforeach
    </script>
@endif

<script>
    $(document).on('ready', function () {

        $('.js-toggle-password').each(function () {
            new HSTogglePassword(this).init()
        });

        $('.js-validate').each(function () {
            $.HSCore.components.HSValidation.init($(this));
        });
    });
</script>

@if(isset($recaptcha) && $recaptcha['status'] == 1)
    <script type="text/javascript">
        var onloadCallback = function () {
            grecaptcha.render('recaptcha_element', {
                'sitekey': '{{ \App\CentralLogics\Helpers::get_business_settings('recaptcha')['site_key'] }}'
            });
        };
    </script>
    <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    <script>
        $("#form-id").on('submit',function(e) {
            let response = grecaptcha.getResponse();

            if (response.length === 0) {
                e.preventDefault();
                toastr.error("{{translate('Please check the recaptcha')}}");
            }
        });
    </script>
@else
    <script type="text/javascript">
        $('.refresh-recaptcha').on('click', function() {
            var $url = "{{ URL('/admin/auth/code/captcha') }}";
            var $url = $url + "/" + Math.random();
            document.getElementById('default_recaptcha_id').src = $url;
            console.log('url: '+ $url);
        });
    </script>
@endif

@if(env('APP_MODE')=='demo')
    <script>
        $('#copyButton').on('click', function() {
            $('#phone').val('+8801100000000');
            $('#signupSrPassword').val('12345678');
            toastr.success('Copied successfully!', 'Success!', {
                CloseButton: true,
                ProgressBar: true
            });
            return false;
        });
    </script>
@endif

<!-- IE Support -->
<script>
    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="{{asset('public//assets/admin')}}/vendor/babel-polyfill/polyfill.min.js"><\/script>');
</script>
</body>
</html>
