<?php

namespace App\Http\Controllers\Payment;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\EMoney;
use Illuminate\Http\Request;
use App\Models\PaymentRecord;
use App\CentralLogics\helpers;
use App\CentralLogics\SMS_module;
use App\Models\PhoneVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Modules\Gateways\Traits\SmsGateway;
use Stevebauman\Location\Facades\Location;
use Illuminate\Contracts\Foundation\Application;

class PaymentOrderController extends Controller
{
    public function __construct(
        private EMoney            $eMoney,
        private PaymentRecord     $paymentRecord,
        private PhoneVerification $phoneVerification,
        private User              $user
    )
    {
    }

    /**
     * @param Request $request
     * @return Application|Factory|View|RedirectResponse
     */
    public function paymentProcess(Request $request): View|Factory|RedirectResponse|Application
    {
        $ip = env('APP_MODE') == 'live' ? $request->ip() : '61.227.120.82';
        $currentUserInfo = Location::get($ip);

        $paymentId = $request->payment_id;
        $paymentRecord = $this->paymentRecord->where(['id' => $paymentId])->first();
        if (isset($paymentRecord) && $paymentRecord->expired_at > Carbon::now()) {
            $merchantUser = $this->user->with('merchant')->where(['id' => $paymentRecord->merchant_user_id])->first();
            $logo=Helpers::get_business_settings('logo');
            $logo = Helpers::onErrorImage($logo, asset('storage/app/public/business') . '/' . $logo, asset('public/assets/admin/img/160x160/img2.jpg'), 'business/');
            return view('payment.phone', compact('paymentId', 'merchantUser', 'currentUserInfo', 'paymentRecord', 'logo'));
        }
        Toastr::warning(translate('Payment time expired'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'dial_country_code' => 'required|string',
            'phone' => 'required|min:8|max:20',
        ], [
            'phone.required' => translate('Phone is required'),
            'dial_country_code.required' => translate('Country code is required'),
        ]);

        $phone = $request->dial_country_code . $request->phone;
        $paymentId = $request->payment_id;
        $otpStatus = Helpers::get_business_settings('payment_otp_verification');

        if (isset($otpStatus) && $otpStatus == 1) {
            $otp = mt_rand(1000, 9999);
            if (env('APP_MODE') != LIVE) {
                $otp = '1234';
            }

            $user = $this->user->where(['phone' => $phone, 'type' => CUSTOMER_TYPE])->first();

            if (isset($user)) {

                if ($user->is_kyc_verified != 1) {
                    Toastr::warning(translate('User is not verified, please complete your account verification'));
                    return back();
                }

                session()->put('user_phone', $user->phone);

                DB::table('phone_verifications')->updateOrInsert(['phone' => $phone], [
                    'otp' => $otp,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if (addon_published_status('Gateways')) {
                    $response = SmsGateway::send($request['phone'], $otp);
                } else {
                    $response = SMS_module::send($request['phone'], $otp);
                }

                Toastr::success(translate('OTP send !'));
                return redirect()->route('otp', compact('paymentId'));
            }
            Toastr::warning(translate('please enter a valid user phone number'));
            return back();
        }
        return redirect()->route('pin', compact('paymentId'));
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function otpIndex(Request $request): View|Factory|Application
    {
        $paymentId = $request->paymentId;
        $paymentRecord = $this->paymentRecord->where(['id' => $paymentId])->first();
        $frontendCallback = $paymentRecord->callback;
        $logo=Helpers::get_business_settings('logo');
        $logo = Helpers::onErrorImage($logo, asset('storage/app/public/business') . '/' . $logo, asset('public/assets/admin/img/160x160/img2.jpg'), 'business/');
        return view('payment.otp', compact('paymentId', 'frontendCallback', 'logo'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => 'required|min:4|max:4',
        ], [
            'otp.required' => translate('OTP is required'),
            'otp.min' => translate('OTP must be 4 digit'),
            'otp.max' => translate('OTP must be 4 digit'),
        ]);

        $paymentId = $request->payment_id;
        $verify = $this->phoneVerification->where(['phone' => session('user_phone'), 'otp' => $request['otp']])->first();

        if (isset($verify)) {
            $verify->delete();
            Toastr::success(translate('OTP verify success !'));
            return redirect()->route('pin', compact('paymentId'));
        }

        Toastr::warning(translate('OTP verify failed !'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $phone = session('user_phone');

        try {
            $otp = mt_rand(1000, 9999);
            if (env('APP_MODE') != LIVE) {
                $otp = '1234';
            }
            DB::table('phone_verifications')->updateOrInsert(['phone' => $phone], [
                'otp' => $otp,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (addon_published_status('Gateways')) {
                $response = SmsGateway::send($phone, $otp);
            } else {
                $response = SMS_module::send($phone, $otp);
            }

            return response()->json(['message' => 'OTP Send'], 200);

        } catch (Exception $e) {
            return response()->json(['message' => 'OTP Send failed'], 404);
        }
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function pinIndex(Request $request): Factory|View|Application
    {
        $paymentId = $request->paymentId;
        $paymentRecord = $this->paymentRecord->where(['id' => $paymentId])->first();
        $frontendCallback = $paymentRecord->callback;
        $logo=Helpers::get_business_settings('logo');
        $logo = Helpers::onErrorImage($logo, asset('storage/app/public/business') . '/' . $logo, asset('public/assets/admin/img/160x160/img2.jpg'), 'business/');
        return view('payment.pin', compact('paymentId', 'frontendCallback', 'logo'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function verifyPin(Request $request): RedirectResponse
    {
        $request->validate([
            'pin' => 'required|min:4|max:4',
        ], [
            'pin.required' => translate('Pin is required'),
            'pin.min' => translate('Pin must be 4 digit'),
            'pin.max' => translate('Pin must be 4 digit'),
        ]);

        $paymentId = $request->payment_id;
        $user = $this->user->where(['phone' => session('user_phone'), 'type' => CUSTOMER_TYPE])->first();

        if (!isset($user)) {
            Toastr::warning(translate('user not found !'));
            return back();
        }

        if (!Hash::check($request->pin, $user->password)) {
            Toastr::warning(translate('pin mismatched !'));
            return back();
        }

        $paymentRecord = $this->paymentRecord->where(['id' => $paymentId, 'transaction_id' => null, 'is_paid' => 0])->first();

        if (isset($paymentRecord) && $paymentRecord->expired_at > Carbon::now()) {
            $amount = $paymentRecord->amount;
            $merchantUser = $this->user->where('id', $paymentRecord->merchant_user_id)->first();
            $adminUser = $this->user->where('type', 0)->first();
            $userEmoney = $this->eMoney->where('user_id', $user->id)->first();
            $merchantEmoney = $this->eMoney->where('user_id', $paymentRecord->merchant_user_id)->first();
            $adminEmoney = $this->eMoney->where('user_id', $adminUser->id)->first();

            if ($userEmoney->current_balance < $paymentRecord->amount) {
                Toastr::warning(translate('You do not have enough balance. Please generate eMoney first.'));
                return back();
            }

            $transactionId = payment_transaction($user, $merchantUser, $userEmoney, $merchantEmoney, $amount, $adminUser, $adminEmoney);
            session()->put('transaction_id', $transactionId);

            if ($transactionId != null) {
                $paymentRecord->user_id = $user->id;
                $paymentRecord->transaction_id = $transactionId;
                $paymentRecord->is_paid = 1;
                $paymentRecord->save();

                Toastr::success(translate('Payment successful !'));
                return redirect()->route('success', ['payment_id' => $request['payment_id']]);
            }
        }
        Toastr::warning(translate('Payment failed !'));
        return back();
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function successIndex(Request $request): Factory|View|Application
    {
        $paymentId = $request->payment_id;
        return view('payment.success', compact('paymentId'));
    }

    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector
     */
    public function paymentSuccessCallback(Request $request): Redirector|Application|RedirectResponse
    {
        $transactionId = session('transaction_id');
        $paymentRecord = $this->paymentRecord->where(['id' => $request->payment_id])->first();

        $callback = $paymentRecord['callback'];
        $url = $callback . '?transaction_id=' . $transactionId;

        return redirect($url);
    }

}

