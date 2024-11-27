@if ($data['how_it_works_section']['status'] == '1')
    <section class="section-items overflow-hidden">
        <div class="container" id="how-it-works-section">
            <div class="section-title mt-5">
                <h2 class="text-center">{!! change_text_color_or_bg($data['how_it_works_section']['header_title']['value']) !!}</h2>
            </div>

            <div class="swiper autoWheelSwiper">
                <div class="swiper-wrapper">
                    @foreach ($data['how_it_works_section']['data'] ?? [] as $index => $item)
                        <div class="swiper-slide swiper-slide-item-{{ $index }}">
                            <div class="row gy-4 align-items-center">
                                <div class="col-lg-8">
                                    <div class="how-it-works-img-wrap">
                                        <div class="how-it-works-img bg-contain"
                                             {{$index === 0 ? 'data-aos=fade-right data-aos-duration=1000 data-aos-delay=300' : ''}} data-bg-img="{{asset('public/assets/landing/img/media/how-works-img-bg.png')}}">
                                            <img width="524"
                                                 src="{{ asset('storage/app/public/landing-page/how-it-works/' . $item['image']) }}"
                                                 alt="{{translate('image')}}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div
                                        class="how-it-works-content" {{$index === 0 ? 'data-aos=fade-right data-aos-duration=1000 data-aos-delay=500' : ''}}>
                                        <h3>{!! $item['title'] !!}</h3>
                                        <hr class="w-75">
                                        <p class="text-muted">{!! $item['sub_title'] !!}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination position-static mt-3"></div>
            </div>
        </div>
    </section>
@endif
