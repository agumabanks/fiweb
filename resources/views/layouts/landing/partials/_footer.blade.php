<div class="footer">
    <div class="footer-main overflow-hidden">
        <img src="{{asset('public/assets/landing/img/media/footer-bg.svg')}}" alt="" class="svg footer-bg">
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4">
                    <div class="widget widget__about mb-4 text-center text-sm-start mx-auto mx-sm-0"
                         data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
                        <img width="120" class="mb-3"
                             src="{{asset('storage/app/public/business') . '/' . \App\Models\BusinessSetting::where(['key' => 'landing_page_logo'])->first()->value}}"
                             alt="">
                        <p>{{\App\CentralLogics\Helpers::get_business_settings('business_short_description')}}</p>
                    </div>
                    <div class="widget widget__socials justify-content-center justify-content-sm-start"
                         data-aos="fade-left" data-aos-duration="1000" data-aos-delay="300">
                        @php($socialMedia = \App\Models\SocialMedia::where('status', 1)->get())
                        @if (isset($socialMedia))
                            @foreach ($socialMedia as $social)
                                <a href="{{ $social->link }}" target="_blank"><i class="bi bi-{{ $social->name }}"></i></a>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="row gy-5">
                        <div class="col-sm-4">
                            <div class="widget widget__links text-center text-sm-start" data-aos="fade-left"
                                 data-aos-duration="1000" data-aos-delay="400">
                                <h4 class="widget__title">{{translate('Quick Links')}}</h4>

                                <ul class="d-flex flex-column gap-2">
                                    <li><a class="{{Request::is('/') ? 'active' : ''}}"
                                           href="{{route('landing-page-home')}}">{{translate('Home Page')}}</a></li>
                                    <li><a class="{{Request::is('pages/about-us') ? 'active' : ''}}"
                                           href="{{route('pages.about-us')}}">{{translate('About Us')}}</a></li>
                                    <li>
                                        <a href="{{Request::is('/') ? '#how-it-works-section' : route('landing-page-home') . '#how-it-works-section' }}">{{translate('How It Works')}}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="widget widget__links text-center text-sm-start" data-aos="fade-left"
                                 data-aos-duration="1000" data-aos-delay="500">
                                <h4 class="widget__title">{{translate('Help & Support')}}</h4>

                                <ul class="d-flex flex-column gap-2">
                                    <li><a class="{{Request::is('contact-us') ? 'active' : ''}}"
                                           href="{{route('contact-us')}}">{{translate('Contact Us')}}</a></li>
                                    <li><a class="{{Request::is('pages/privacy-policy') ? 'active' : ''}}"
                                           href="{{route('pages.privacy-policy')}}">{{translate('Privacy Policy')}}</a>
                                    </li>
                                    <li><a class="{{Request::is('pages/terms-conditions') ? 'active' : ''}}"
                                           href="{{route('pages.terms-conditions')}}">{{translate('Terms & Condition')}}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="widget widget__links text-center text-sm-start" data-aos="fade-left"
                                 data-aos-duration="1000" data-aos-delay="600">
                                <h4 class="widget__title">{{translate('Get It Now')}}</h4>

                                <div class="d-flex flex-column gap-3">
                                    @if ($data['download_section']['data']['play_store_link'] != "")
                                        <a href="{{$data['download_section']['data']['play_store_link']}}"><img
                                                width="130"
                                                src="{{asset('public/assets/landing/img/media/google_play_btn.png')}}"
                                                alt=""></a>
                                    @endif
                                    @if ($data['download_section']['data']['app_store_link'] != "")
                                        <a href="{{$data['download_section']['data']['app_store_link']}}"><img
                                                width="130"
                                                src="{{asset('public/assets/landing/img/media/app_store_btn.png')}}"
                                                alt=""></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom text-center">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <p class="text-center text-md-left mb-2 mb-md-0">
                        &copy; {{\App\CentralLogics\Helpers::get_business_settings('business_name')}}.
                        <span>{{\App\CentralLogics\Helpers::get_business_settings('footer_text')}}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
