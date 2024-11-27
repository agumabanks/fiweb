@extends('layouts.landing.app')

@section('title', translate('Contact Us'))

@section('content')

    <div class="overflow-hidden" data-bg-img="{{asset('public/assets/landing/img/media/page-header-bg.png')}}">
        <div class="container">
            <div class="page-header text-center">
                <h2 class="text-white mb-4" data-aos="fade-up" data-aos-duration="1000"
                    data-aos-delay="300">{!! change_text_color_or_bg($data['contact_us_section']['data']['title']) !!}</h2>
                <p class="mx-w-480 mx-auto text-white fs-18" data-aos="fade-up" data-aos-duration="1000"
                   data-aos-delay="500">{!! change_text_color_or_bg($data['contact_us_section']['data']['sub_title']) !!}</p>
            </div>
        </div>
    </div>

    <section class="mt-5">
        <div class="container">
            <div class="bg-white shadow-sm rounded-10">
                <div class="row gx-0">
                    <div class="col-lg-5">
                        <div class="overlay p-4 p-lg-5 contact-info-box h-100 contact-opacity"
                             data-bg-img="{{asset('public/assets/landing/img/media/contact-bg.png')}}">
                            <h3 class="fw-normal text-white mb-4 text-center text-sm-start">{{translate('Any question or remarks? Just write us a message! or just reach out to us!')}}</h3>

                            <ul class="contact-list d-flex flex-column gap-4 gap-lg-5 list-unstyled mt-5">
                                <li>
                                    <div class="media align-items-center gap-3">
                                        <div class="contact-icon rounded">
                                            <i class="bi bi-geo-alt"></i>
                                        </div>
                                        <div class="media-body">
                                            <div class="fs-14 text-white">{{translate('Address')}}:</div>
                                            <div
                                                class="fs-14 text-white">{{Helpers::get_business_settings('address')}}</div>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="media align-items-center gap-3">
                                        <div class="contact-icon rounded">
                                            <i class="bi bi-envelope-fill"></i>
                                        </div>
                                        <div class="media-body">
                                            <div class="fs-14 text-white">{{translate('My Email')}}:</div>
                                            <a href="mailto:alifemu2@gmail.com"
                                               class="fs-14 text-white">{{Helpers::get_business_settings('email')}}</a>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <div class="media align-items-center gap-3">
                                        <div class="contact-icon rounded">
                                            <i class="bi bi-telephone-fill"></i>
                                        </div>
                                        <div class="media-body">
                                            <div class="fs-14 text-white">{{translate('Call Me Now')}}:</div>
                                            <a href="tel:+8801616788422"
                                               class="fs-14 text-white">{{Helpers::get_business_settings('phone')}}</a>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="p-4 p-lg-5">
                            <form action="{{route('send-message')}}" method="POST" class="contact-form">
                                @csrf
                                <div class="mb-4">
                                    <label for="name">{{translate('Name')}}*</label>
                                    <input type="text" name="name" id="name" placeholder="{{translate('Ex: John Doe')}}"
                                           class="form-control">
                                </div>
                                <div class="mb-4">
                                    <label for="email">{{translate('Email')}}*</label>
                                    <input type="email" name="email" id="email" placeholder="Ex: jhondoe@gmail.com"
                                           class="form-control">
                                </div>
                                <div class="mb-4">
                                    <label for="subject">{{translate('Subject')}}*</label>
                                    <input type="text" name="subject" id="subject"
                                           placeholder="{{translate('Ex: your subject')}}"
                                           class="form-control">
                                </div>
                                <div class="mb-4">
                                    <label for="message">{{translate('Message')}}*</label>
                                    <textarea type="text" name="message" id="message"
                                              placeholder="{{translate('Ex: your message')}}"
                                              rows="6" class="form-control"></textarea>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">{{translate('submit')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @include('landing.partials.home.download')
@endsection
