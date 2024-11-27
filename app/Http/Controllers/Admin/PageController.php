<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\CentralLogics\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        private BusinessSetting $businessSetting
    ){}

    /**
     * @return Application|Factory|View
     */
    public function termsAndConditions(): Factory|View|Application
    {
        $termsAndCondition = $this->businessSetting->where(['key' => 'terms_and_conditions'])->first();
        $title = $this->businessSetting->where(['key' => 'terms_and_conditions_title'])->first();
        $subTitle = $this->businessSetting->where(['key' => 'terms_and_conditions_sub_title'])->first();
        if (!$termsAndCondition) {
            $this->businessSetting->insert([
                'key' => 'terms_and_conditions',
                'value' => '',
            ]);
        }

        if (!$title) {
            $title = [
                'key' => 'terms_and_conditions_title',
                'value' => '',
            ];
            $this->businessSetting->insert($title);
        }
        if (!$subTitle) {
            $subTitle = [
                'key' => 'terms_and_conditions_sub_title',
                'value' => '',
            ];
            $this->businessSetting->insert($subTitle);
        }

        return view('admin-views.business-settings.page.terms-and-conditions', compact('termsAndCondition', 'title', 'subTitle'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function termsAndConditionsUpdate(Request $request): RedirectResponse
    {
        $this->businessSetting->where(['key' => 'terms_and_conditions'])->update([
            'value' => $request->tnc,
        ]);

        $this->businessSetting->where(['key' => 'terms_and_conditions_title'])->update([
            'value' => $request->terms_and_conditions_title,
        ]);

        $this->businessSetting->where(['key' => 'terms_and_conditions_sub_title'])->update([
            'value' => $request->terms_and_conditions_sub_title,
        ]);

        Toastr::success(translate('Terms and Conditions updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function privacyPolicy(): Factory|View|Application
    {
        $privacyPolicy = $this->businessSetting->where(['key' => 'privacy_policy'])->first();
        $title = $this->businessSetting->where(['key' => 'privacy_policy_title'])->first();
        $subTitle = $this->businessSetting->where(['key' => 'privacy_policy_sub_title'])->first();
        if (!$privacyPolicy) {
            $data = [
                'key' => 'privacy_policy',
                'value' => '',
            ];
            $this->businessSetting->insert($data);
        }

        if (!$title) {
            $title = [
                'key' => 'privacy_policy_title',
                'value' => '',
            ];
            $this->businessSetting->insert($title);
        }
        if (!$subTitle) {
            $subTitle = [
                'key' => 'privacy_policy_sub_title',
                'value' => '',
            ];
            $this->businessSetting->insert($subTitle);
        }
        return view('admin-views.business-settings.page.privacy-policy', compact('title', 'subTitle', 'privacyPolicy'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function privacyPolicyUpdate(Request $request): RedirectResponse
    {
        $this->businessSetting->where(['key' => 'privacy_policy'])->update([
            'value' => $request->privacy_policy,
        ]);

        $this->businessSetting->where(['key' => 'privacy_policy_title'])->update([
            'value' => $request->privacy_policy_title,
        ]);

        $this->businessSetting->where(['key' => 'privacy_policy_sub_title'])->update([
            'value' => $request->privacy_policy_sub_title,
        ]);

        Toastr::success(translate('Privacy policy updated!'));
        return back();
    }

    /**
     * @return Application|Factory|View
     */
    public function aboutUs(): Factory|View|Application
    {
        $aboutUs = $this->businessSetting->where(['key' => 'about_us'])->first();
        $title = $this->businessSetting->where(['key' => 'about_us_title'])->first();
        $subTitle = $this->businessSetting->where(['key' => 'about_us_sub_title'])->first();
        $image = $this->businessSetting->where(['key' => 'about_us_image'])->first();
        if (!$aboutUs) {
            $aboutUs = [
                'key' => 'about_us',
                'value' => '',
            ];
            $this->businessSetting->insert($aboutUs);
        }
        if (!$title) {
            $title = [
                'key' => 'about_us_title',
                'value' => '',
            ];
            $this->businessSetting->insert($title);
        }
        if (!$subTitle) {
            $subTitle = [
                'key' => 'about_us_sub_title',
                'value' => '',
            ];
            $this->businessSetting->insert($subTitle);
        }

        if (!$subTitle) {
            $subTitle = [
                'key' => 'about_us_sub_title',
                'value' => '',
            ];
            $this->businessSetting->insert($subTitle);
        }
        if (!$image) {
            $image = [
                'key' => 'about_us_image',
                'value' => '',
            ];
            $this->businessSetting->insert($image);
        }
        return view('admin-views.business-settings.page.about-us', compact(['aboutUs', 'title', 'subTitle', 'image']));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function aboutUsUpdate(Request $request): RedirectResponse
    {
        $this->businessSetting->where(['key' => 'about_us'])->update([
            'value' => $request->about_us,
        ]);

        $this->businessSetting->where(['key' => 'about_us_title'])->update([
            'value' => $request->about_us_title,
        ]);

        $this->businessSetting->where(['key' => 'about_us_sub_title'])->update([
            'value' => $request->about_us_sub_title,
        ]);


        $currentLogo = $this->businessSetting->where(['key' => 'about_us_image'])->first() ?? '';

        $imageName = $request->has('about_us_image') ? Helpers::update('about-us/', $currentLogo->value ?? '', 'png', $request->file('about_us_image')) : $currentLogo->value;

        $this->businessSetting->query()->updateOrInsert(['key' => 'about_us_image'], [
            'value' => $imageName,
        ]);

        Toastr::success(translate('About us updated!'));
        return back();
    }
}
