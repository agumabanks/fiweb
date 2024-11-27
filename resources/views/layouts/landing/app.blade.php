<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <title>@yield('title')</title>
    <meta name="_token" content="{{csrf_token()}}">

    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="keywords" content="">

    <link rel="shortcut icon"
          href="{{asset('storage/app/public/favicon')}}/{{Helpers::get_business_settings('favicon') ?? null}}"/>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700;800;900&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="{{asset('public/assets/landing/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/landing/css/bootstrap-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/landing/plugins/swiper/swiper-bundle.min.css')}}">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <link rel="stylesheet" href="{{asset('public/assets/landing/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/landing/css/custom.css')}}">

    <link rel="stylesheet" href="{{asset('public/assets/admin')}}/css/toastr.css">

    @stack('css_or_js')
</head>

<body>
<div class="preloader">
    <div class="spinner-grow" role="status">
        <span class="visually-hidden">{{translate('Loading')}}...</span>
    </div>
</div>

@include('layouts.landing.partials._header')

<main class="main-content d-flex flex-column">
    @yield('content')
</main>

<section class="bg-white pt-5">
    <div class="newsletter-area" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="300">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="p-4 p-sm-5 rounded-10"
                         data-bg-img="{{asset('public/assets/landing/img/media/newsletter-bg.png')}}">
                        <div
                            class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 gap-md-5">
                            <div class="d-flex flex-column gap-3">
                                <h3 class="text-white">{{translate('Subscribe Newsletter')}}</h3>
                                <p class="text-white">{{translate('get the latest')}} {{App\Models\BusinessSetting::where('key', 'business_name')->value('value') ?? '6cash'}} {{translate('offers delivered to your inbox')}}</p>
                            </div>

                            <form action="{{route('newsletter.subscribe')}}" method="POST"
                                  class="newsletter-form flex-grow-1 mx-w w-22-rem">
                                @csrf
                                <div class="d-flex form-control px-1">
                                    <input type="email" name="email"
                                           class="border-0 px-2 text-dark bg-transparent w-100"
                                           placeholder="Enter your email">
                                    <button type="submit"
                                            class="btn btn-secondary rounded px-3">{{translate('Submit')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.landing.partials._footer')
</section>

<div class="progress-wrap">
    <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98"/>
    </svg>
</div>

<aside class="aside d-flex flex-column d-xl-none">
    <div class="aside-overlay"></div>
    <div class="aside-header">
        <div class="d-flex pb-3 justify-content-between">
            <a href="index.html">
                <img width="100"
                     src="{{asset('storage/app/public/business') . '/' . \App\Models\BusinessSetting::where(['key' => 'landing_page_logo'])->first()->value}}"
                     alt="">
            </a>
            <button class="aside-close-btn border-0 bg-transparent">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    <div class="aside-body custom-scrollbar">
        <ul class="main-nav nav">
            <li><a class="{{Request::is('/') ? 'active' : ''}}"
                   href="{{route('landing-page-home')}}">{{translate('Home')}}</a></li>
            <li><a class="{{Request::is('pages/privacy-policy') ? 'active' : ''}}"
                   href="{{route('pages.privacy-policy')}}">{{translate('Privacy Policy')}}</a></li>
            <li><a class="{{Request::is('pages/terms-conditions') ? 'active' : ''}}"
                   href="{{route('pages.terms-conditions')}}">{{translate('Terms & Condition')}}</a></li>
            <li><a class="{{Request::is('pages/about-us') ? 'active' : ''}}"
                   href="{{route('pages.about-us')}}">{{translate('About Us')}}</a></li>
            <li><a class="{{Request::is('contact-us') ? 'active' : ''}}"
                   href="{{route('contact-us')}}">{{translate('Contact Us')}}</a></li>
        </ul>
    </div>
</aside>

<script src="{{asset('public/assets/landing/js/jquery-3.6.0.min.js')}}"></script>
<script src="{{asset('public/assets/landing/js/bootstrap.bundle.min.js')}}"></script>
<script src="{{asset('public/assets/landing/plugins/swiper/swiper-bundle.min.js')}}"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="{{asset('public/assets/landing/js/main.js')}}"></script>

<script src="{{asset('public/assets/admin')}}/js/vendor.min.js"></script>

@stack('script_2')

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


<script src="{{asset('public/assets/admin')}}/js/toastr.js"></script>
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
</body>
</html>
