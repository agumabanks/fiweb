@extends('layouts.landing.app')

@section('title', translate('Privacy Policy'))

@section('content')
    <div class="overflow-hidden" data-bg-img="{{asset('public/assets/landing/img/media/page-header-bg.png')}}">
        <div class="container">
            <div class="page-header text-center">
                <h2 class="text-white mb-4" data-aos="fade-up" data-aos-duration="1000"
                    data-aos-delay="300">{!! change_text_color_or_bg($data['privacy_policy_section']['title']) !!}</h2>
                <p class="mx-w-480 mx-auto text-white fs-18" data-aos="fade-up" data-aos-duration="1000"
                   data-aos-delay="500">{!! $data['privacy_policy_section']['sub_title'] !!}</p>
            </div>
        </div>
    </div>

    <section class="my-5">
        <div class="container">
            <div class="page-content">
                {!! change_text_color_or_bg(Helpers::get_business_settings('privacy_policy') ?? '') !!}
            </div>
        </div>
    </section>
@endsection
