@if ($data['feature_section']['status'] == '1')
    <section class="mt-5 pt-lg-4">
        <div id="feature-section"></div>
        <div class="container">
            <div class="section-title">
                <h2 class="text-center">{!! change_text_color_or_bg($data['feature_section']['header_title']['value']) !!}</h2>
            </div>

            <div class="swiper featureSwiper"
                 data-swiper-breakpoints='{"0": {"slidesPerView": "1"}, "576": {"slidesPerView": "2"}, "992": {"slidesPerView": "3"}, "1200": {"slidesPerView": "4"}}'
                 data-swiper-margin="30" data-swiper-loop="false">
                <div class="swiper-wrapper">
                    @foreach ($data['feature_section']['data'] ?? [] as $key => $item)
                        <div class="swiper-slide py-2">
                            <div class="bg--{{ $key % 2 === 0 ? 'light' : 'white' }} shadow-sm rounded h-100"
                                 data-aos="flip-left" data-aos-duration="1000" data-aos-delay="300">
                                @if ($key % 2 === 0)
                                    <div class="img-box px-2 d-flex justify-content-center align-items-center">
                                        <img src="{{ asset('public/assets/landing/img/media/circles.svg') }}"
                                             class="svg bg-circle" alt="{{translate('image')}}">
                                        <img
                                            src="{{ asset('storage/app/public/landing-page/feature/' . $item['image']) }}"
                                            class="banking-ss-img w-75" alt="{{translate('image')}}">
                                    </div>
                                @endif
                                <div
                                    class="p-4 {{ $key % 2 === 0 ? 'mt-3' : 'mb-3' }} text-center feature__content-box">
                                    <h3 class="mb-3">{!! $item['title'] !!}</h3>
                                    <p class="feature__sub-title" data-bs-toggle="tooltip"
                                       title="{!! $item['sub_title'] !!}">{!! $item['sub_title'] !!}</p>
                                </div>
                                @if ($key % 2 !== 0)
                                    <div class="img-box px-2 d-flex justify-content-center align-items-center">
                                        <img src="{{ asset('public/assets/landing/img/media/circles.svg') }}"
                                             class="svg bg-circle" alt="{{translate('image')}}">
                                        <img
                                            src="{{ asset('storage/app/public/landing-page/feature/' . $item['image']) }}"
                                            class="banking-ss-img w-75" alt="{{translate('image')}}">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>

        </div>
    </section>
@endif
