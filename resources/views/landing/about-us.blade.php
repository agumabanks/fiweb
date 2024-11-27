@extends('layouts.landing.app')

@section('title', translate('About Us'))

@section('content')
    <div class="overflow-hidden" data-bg-img="{{asset('public/assets/landing/img/media/page-header-bg.png')}}">
        <div class="container">
            <div class="page-header text-center">
                <h2 class="text-white mb-4" data-aos="fade-up" data-aos-duration="1000"
                    data-aos-delay="300">{!! change_text_color_or_bg($data['about_us_section']['title']) !!}</h2>
                <p class="mx-w-480 mx-auto text-white fs-18" data-aos="fade-up" data-aos-duration="1000"
                   data-aos-delay="500">{!! $data['about_us_section']['sub_title'] !!}</p>
            </div>
        </div>
    </div>

    <section class="mt-5">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <img width="536" class="img-fluid"
                         src="{{asset('storage/app/public/about-us')}}/{{$data['about_us_section']['image']}}" alt="">
                </div>
                <div class="col-lg-6">
                    <div class="text-center text-sm-start">
                        {!! change_text_color_or_bg(Helpers::get_business_settings('about_us') ?? '') !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('landing.partials.home.agent-registration')

    @include('landing.partials.home.business-statistics')
@endsection
