@if ($data['screenshots_section']['status'] == '1' && $data['screenshots_section']['data'])
    <section class="mt-5 py-5 bg-light overflow-hidden">
        <div class="swiper screenshot-slider" data-swiper-loop="true" data-swiper-items="auto" data-swiper-margin="50"
             data-swiper-center="true">
            <div class="slide-center-frame">
                <img width="250" src="{{ asset('public/assets/landing/img/media/banner-mobile-frame.png') }}"
                     alt="{{translate('image')}}">
            </div>
            <div class="swiper-wrapper">
                @foreach ($data['screenshots_section']['data'] ?? [] as $item)
                    <div class="swiper-slide">
                        <img src="{{asset('storage/app/public/landing-page/screenshots')}}/{{$item['image']}}"
                             alt="{{translate('image')}}">
                    </div>
                @endforeach
            </div>
            <div class="swiper-pagination position-relative mt-5"></div>
        </div>
    </section>
@endif
