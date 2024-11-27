@if ($data['download_section']['status'] == '1')
    <section class="mt-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="overlay pt-4 text-white text-center rounded-10 pb-100 px-3"
                         data-bg-img="{{asset('public/assets/landing/img/media/access-6cash-bg.png')}}">
                        <div data-aos="fade-down" data-aos-duration="1000" data-aos-delay="300">
                            <h2 class="text-white mb-4">{!! change_text_color_or_bg($data['download_section']['data']['title']) !!}</h2>
                            <p>{!! change_text_color_or_bg($data['download_section']['data']['sub_title']) !!}</p>
                        </div>

                        <div class="app-btns mt-4 d-flex justify-content-center gap-3" data-aos="zoom-in"
                             data-aos-duration="1000" data-aos-delay="300">
                            @if ($data['download_section']['data']['play_store_link'] != "")
                                <a href="{{$data['download_section']['data']['play_store_link']}}"><img width="150"
                                src="{{asset('public/assets/landing/img/media/google_play_btn.png')}}"
                                alt="{{translate('image')}}"></a>
                            @endif
                            @if ($data['download_section']['data']['app_store_link'] != "")
                                <a href="{{$data['download_section']['data']['app_store_link']}}"><img width="150"
                               src="{{asset('public/assets/landing/img/media/app_store_btn.png')}}"
                               alt="{{translate('image')}}"></a>
                            @endif
                        </div>
                    </div>
                    <div class="position-relative z-1" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="300">
                        <img class="w-100 access-6cash-img"
                             src="{{asset('storage/app/public/landing-page/download-section/'.$data['download_section']['data']['image'])}}"
                             alt="{{translate('image')}}">
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
