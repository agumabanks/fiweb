@if ($data['intro_section']['status'] == '1')
    <div class="banner overflow-hidden" data-bg-img="{{asset('public/assets/landing/img/media/banner-bg.png')}}">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <h1 class="banner-title text-white text-center text-lg-start">{!! change_text_color_or_bg($data['intro_section']['data']['title']) !!}</h1>
                </div>
                <div class="col-lg-6">
                    <div class="banner-content">
                        <p class="fs-18 text-white text-center text-lg-start">{!! $data['intro_section']['data']['description'] !!}</p>
                        <div class="d-flex justify-content-center justify-content-lg-start gap-3 mt-4">
                            <a href="{{$data['intro_section']['data']['download_link']}}"
                               class="btn btn-warning">{{$data['intro_section']['data']['button_name'] ?? translate('Download')}}</a>
                            <a href="#feature-section" class="btn btn-primary">{{translate('Explore More')}}</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-sm-5">
                <div
                        class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3 flex-wrap banner-middle-content">
                    <div class="bg-white p-3 rounded pe-xl-5 star-opacity">
                        <div class="media gap-2 align-items-center">
                            <img width="40"
                                 src="{{asset('storage/app/public/landing-page/intro-section/'.$data['intro_section']['rating_and_user_data']['review_user_icon'])}}"
                                 class="border border-2 rounded-circle" alt="{{translate('image')}}">
                            <div class="media-body">
                                <div
                                        class="text-white">{{$data['intro_section']['rating_and_user_data']['reviewer_name']}}</div>
                                <div class="star-rating text-warning fs-10">
                                    @php
                                        $rating = $data['intro_section']['rating_and_user_data']['rating'];
                                    @endphp

                                    @if ($rating >= 0.5 && $rating <= 5)
                                        @php
                                            $fullStars = floor($rating);
                                            $halfStar = ($rating - $fullStars) >= 0.5;
                                        @endphp

                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $fullStars)
                                                <i class="bi bi-star-fill"></i>
                                            @elseif ($halfStar && $i == ($fullStars + 1))
                                                <i class="bi bi-star-half"></i>
                                            @else
                                                <i class="bi bi-star"></i>
                                            @endif
                                        @endfor
                                    @else
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="bi bi-star-fill"></i>
                                        @endfor
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="">
                            <img width="48"
                                 src="{{$imageSource['user_image_one']}}"
                                 class="border border-2 rounded-circle" alt="{{translate('image')}}">
                            <img width="48"
                                 src="{{$imageSource['user_image_two']}}"
                                 class="border border-2 rounded-circle ms-n3" alt="{{translate('image')}}">
                            <img width="48"
                                 src="{{$imageSource['user_image_three']}}"
                                 class="border border-2 rounded-circle ms-n3" alt="{{translate('image')}}">
                        </div>

                        <div class="text-white">
                            <h3 class="text-white mb-2">{{format_number($data['intro_section']['rating_and_user_data']['total_user_count'])}}
                                + </h3>
                            <h6 class="fs-12 text-white">{{$data['intro_section']['rating_and_user_data']['total_user_content']}}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <div class="banner-mobile-frame d-flex gap-2 gap-sm-3 justify-content-center">
                <div class="banner-left-frame">
                    <img width="280"
                         src="{{$imageSource['intro_left_image']}}"
                         class="ss-img" alt="{{translate('image')}}">
                    <img width="300" src="{{asset('public/assets/landing/img/media/banner-mobile-frame.png')}}"
                         alt="{{translate('image')}}">
                </div>
                <div class="banner-middle-frame">
                    <img width="280"
                         src="{{$imageSource['intro_middle_image']}}"
                        class="ss-img"
                         alt="{{translate('image')}}">
                    <img width="300" src="{{asset('public/assets/landing/img/media/banner-mobile-frame.png')}}"
                         alt="{{translate('image')}}">
                </div>
                <div class="banner-right-frame">
                    <img width="280"
                         src="{{$imageSource['intro_right_image']}}"
                         class="ss-img"
                         alt="{{translate('image')}}">
                    <img width="300" src="{{asset('public/assets/landing/img/media/banner-mobile-frame.png')}}"
                         alt="{{translate('image')}}">
                </div>
            </div>
        </div>
    </div>
@endif

