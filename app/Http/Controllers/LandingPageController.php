<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\ContactMessage;
use App\Models\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;

class LandingPageController extends Controller
{
    public function __construct(
        private BusinessSetting $businessSetting,
        private ContactMessage  $contactMessage,
    )
    {
    }

    public function landingPageHome(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $screenshotsData = $this->businessSetting->where('key', 'screenshots')->first();
        $whyChooseUsData = $this->businessSetting->where('key', 'why_choose_us')->first();
        $featureData = $this->businessSetting->where('key', 'feature')->first();
        $howItWorksData = $this->businessSetting->where('key', 'how_it_works_section')->first();
        $testimonialData = $this->businessSetting->where('key', 'testimonial')->first();
        $businessStatisticsDownloadData = $this->businessSetting->where('key', 'business_statistics_download')->first();

        $data = [
            'intro_section' => [
                'data' => Helpers::get_business_settings('intro_section'),
                'rating_and_user_data' => Helpers::get_business_settings('user_rating_with_total_user_section'),
                'status' => $this->businessSetting->where('key', 'landing_intro_section_status')->value('value'),
                'header_title' => null
            ],
            'feature_section' => [
                'data' => $featureData ? $this->filterStatus($featureData) : null,
                'status' => $this->businessSetting->where('key', 'landing_feature_status')->value('value'),
                'header_title' => $this->businessSetting->where('key', 'landing_feature_title')->first()
            ],
            'screenshots_section' => [
                'data' => $screenshotsData ? $this->filterStatus($screenshotsData) : null,
                'status' => $this->businessSetting->where('key', 'landing_screenshots_status')->value('value'),
                'header_title' => null
            ],
            'why_choose_us_section' => [
                'data' => $whyChooseUsData ? $this->filterStatus($whyChooseUsData) : null,
                'status' => $this->businessSetting->where('key', 'landing_why_choose_us_status')->value('value'),
                'header_title' => $this->businessSetting->where('key', 'landing_why_choose_us_title')->first(),
            ],
            'agent_registration_section' => [
                'data' => Helpers::get_business_settings('agent_registration_section'),
                'status' => $this->businessSetting->where('key', 'landing_agent_registration_section_status')->value('value'),
                'header_title' => $this->businessSetting->where('key', 'landing_agent_registration_section_title')->first(),
            ],
            'how_it_works_section' => [
                'data' => $howItWorksData ? $this->filterStatus($howItWorksData) : null,
                'status' => $this->businessSetting->where('key', 'landing_how_it_works_section_status')->value('value'),
                'header_title' => $this->businessSetting->where('key', 'landing_how_it_works_section_title')->first(),
            ],
            'download_section' => [
                'data' => Helpers::get_business_settings('download_section'),
                'status' => $this->businessSetting->where('key', 'landing_download_section_status')->value('value'),
                'header_title' => null
            ],
            'business_statistics_section' => [
                'testimonial_data' => $testimonialData ? $this->filterStatusTestimonial($testimonialData) : null,
                'download_data' => $businessStatisticsDownloadData ? json_decode($businessStatisticsDownloadData->value, true) : null,
                'status' => $this->businessSetting->where('key', 'landing_business_statistics_status')->value('value'),
                'header_title' => null
            ]
        ];

        $imageSource = [];
        $imageSource['intro_left_image'] = Helpers::onErrorImage($data['intro_section']['data']['intro_left_image'], asset('storage/app/public/landing-page/intro-section') . '/' . $data['intro_section']['data']['intro_left_image'], asset('public/assets/landing/img/media/ss-1.png'), 'landing-page/intro-section/');
        $imageSource['intro_middle_image'] = Helpers::onErrorImage($data['intro_section']['data']['intro_middle_image'], asset('storage/app/public/landing-page/intro-section') . '/' . $data['intro_section']['data']['intro_middle_image'], asset('public/assets/landing/img/media/ss-1.png'), 'landing-page/intro-section/');
        $imageSource['intro_right_image'] = Helpers::onErrorImage($data['intro_section']['data']['intro_right_image'], asset('storage/app/public/landing-page/intro-section') . '/' . $data['intro_section']['data']['intro_right_image'], asset('public/assets/landing/img/media/ss-1.png'), 'landing-page/intro-section/');

        $imageSource['user_image_one'] = Helpers::onErrorImage($data['intro_section']['rating_and_user_data']['user_image_one'], asset('storage/app/public/landing-page/intro-section') . '/' . $data['intro_section']['rating_and_user_data']['user_image_one'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/intro-section/');
        $imageSource['user_image_two'] = Helpers::onErrorImage($data['intro_section']['rating_and_user_data']['user_image_two'], asset('storage/app/public/landing-page/intro-section') . '/' . $data['intro_section']['rating_and_user_data']['user_image_two'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/intro-section/');
        $imageSource['user_image_three'] = Helpers::onErrorImage($data['intro_section']['rating_and_user_data']['user_image_three'], asset('storage/app/public/landing-page/intro-section') . '/' . $data['intro_section']['rating_and_user_data']['user_image_three'], asset('public/assets/admin/img/900x400/img1.jpg'), 'landing-page/intro-section/');

        return view('landing.landing-page-home', compact('data', 'imageSource'));
    }

    public function contactUs(): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $data = [
            'download_section' => [
                'data' => Helpers::get_business_settings('download_section'),
                'status' => $this->businessSetting->where('key', 'landing_download_section_status')->value('value'),
                'header_title' => null
            ],
            'contact_us_section' => [
                'data' => Helpers::get_business_settings('contact_us_section'),
                'status' => null,
                'header_title' => null
            ]
        ];
        return view('landing.contact-us', compact('data'));
    }

    public function contactUsMessage(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email:filter',
            'subject' => 'required',
            'message' => 'required',
        ], [
            'name.required' => translate('Name is required!'),
            'email.required' => translate('Email is required!'),
            'email.filter' => translate('Must be a valid email!'),
            'message.required' => translate('Message is required!'),
            'subject.required' => translate('Subject is required!'),
        ]);

        $email = Helpers::get_business_settings('email');

        $messageData = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
        ];

        $contactMessage = $this->contactMessage;
        $contactMessage->name = $request->name;
        $contactMessage->email = $request->email;
        $contactMessage->subject = $request->subject;
        $contactMessage->message = $request->message;
        $contactMessage->save();
        $businessName = Helpers::get_business_settings('business_name') ?? '6Cash';
        $subject = 'Enquiry from ' . $businessName;

        try {
            if (config('mail.status')) {
                Mail::to($email)->send(new ContactMail($messageData, $subject));
                Toastr::success(translate('Thanks_for_your_enquiry._We_will_get_back_to_you_soon.'));
            }
        } catch (\Exception $ex) {
            Toastr::warning(translate('Mail_config_error.'));
            info($ex->getMessage());
        }
        return redirect()->back();
    }

    protected function filterStatus($data): array
    {
        return collect(json_decode($data->value, true))->filter(function ($item) {
            return $item['status'] == 1 || $item['status'] == '1';
        })->all();
    }

    protected function filterStatusTestimonial($data)
    {
        return collect(json_decode($data->value, true))->filter(function ($item) {
            return $item['status'] == 1 || $item['status'] == '1';
        })->take(3);
    }
}
