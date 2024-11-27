<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NewsLetterController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Gateway\PaymobController;
use App\Http\Controllers\Gateway\PaystackController;
use App\Http\Controllers\Gateway\RazorPayController;
use App\Http\Controllers\Gateway\SenangPayController;
use App\Http\Controllers\Gateway\FlutterwaveController;
use App\Http\Controllers\Gateway\MercadoPagoController;
use App\Http\Controllers\Gateway\BkashPaymentController;
use App\Http\Controllers\Payment\PaymentOrderController;
use App\Http\Controllers\Gateway\PaypalPaymentController;
use App\Http\Controllers\Gateway\StripePaymentController;
use App\Http\Controllers\Gateway\SslCommerzPaymentController;
use App\Http\Controllers\Web\RegistrationController;
use App\Http\Controllers\PesapalController;
use App\Traits\Payment as PaymentTrait;

use App\Http\Controllers\MembershipController;

use App\Http\Controllers\ClientController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\LoanOfferController;
use App\Http\Controllers\LoanApplicationController;


Route::apiResource('loan-applications', LoanApplicationController::class);
Route::apiResource('loan-offers', LoanOfferController::class);

Route::get('/', [LandingPageController::class, 'landingPageHome'])->name('landing-page-home');
Route::post('newsletter/subscribe', [NewsLetterController::class, 'newsLetterSubscribe'])->name('newsletter.subscribe');
Route::post('send-message', [LandingPageController::class, 'contactUsMessage'])->name('send-message');
Route::get('contact-us', [LandingPageController::class, 'contactUs'])->name('contact-us');


Route::post('/membership', [MembershipController::class, 'createMembership']);
Route::put('/membership/{id}/payment-status', [MembershipController::class, 'updateMembershipPaymentStatus']);
Route::get('/membership/{userId}', [MembershipController::class, 'getUserMembership']);

Route::group(['prefix' => 'agent', 'as' => 'agent.'], function () {
    Route::get('registration', [RegistrationController::class, 'agentSelfRegistration'])->name('agent-self-registration');
    Route::post('store-registration', [RegistrationController::class, 'storeAgentData'])->name('store-registration');
    Route::post('phone-number-check', [RegistrationController::class, 'phoneNumberCheck'])->name('phone-number-check');
    Route::post('otp-verify', [RegistrationController::class, 'agentVerifyOtp'])->name('verify-otp');
    Route::post('resend-otp', [RegistrationController::class, 'resendOtp'])->name('resend-otp');
});


Route::get('/home', function() {
    return redirect(\route('admin.auth.login'));
});

Route::group(['prefix' => ''], function () {
    Route::get('/payment', [PaymentController::class, 'payment'])->name('payment-mobile');
    Route::get('set-payment-method/{name}', [PaymentController::class, 'payment'])->name('set-payment-method');
    
    // pesapal
    Route::post('add-m-pesa', [PesapalController::class, 'pay']);
    Route::get('/payment-pesa', [PesapalController::class, 'payment'])->name('payment-pesapal');
    Route::post('/pesapal2', [PesapalController::class, 'pay']);
    
    // callBackUr2
    Route::get('/pesapal/callBackUr', [PesapalController::class, 'callBackUr2'])->name('callback-pesapal');
    // Route::get('/pesapal/callBackUr', [PesapalController::class, 'callBackUr2'])->name('callback-pesapal');
    Route::post('/pesapal/token/{id}', [PesapalController::class, 'callBackUr2'])->name('callback-pesapal');
  
//   ipn 
Route::get('/ipn', [PesapalController::class, 'getRegisteredIpn'])->name('ipn');


});

if (!addon_published_status('Gateways')) {
    Route::group(['prefix' => 'payment'], function () {
        //STRIPE
        Route::group(['prefix' => 'stripe', 'as' => 'stripe.'], function () {
            Route::get('pay', [StripePaymentController::class, 'index'])->name('pay');
            Route::get('token', [StripePaymentController::class, 'payment_process_3d'])->name('token');
            Route::get('success', [StripePaymentController::class, 'success'])->name('success');
        });

        //SSLCOMMERZ
        Route::group(['prefix' => 'sslcommerz', 'as' => 'sslcommerz.'], function () {
            Route::get('pay', [SslCommerzPaymentController::class, 'index'])->name('pay');
            Route::post('success', [SslCommerzPaymentController::class, 'success'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('failed', [SslCommerzPaymentController::class, 'failed'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
            Route::post('canceled', [SslCommerzPaymentController::class, 'canceled'])
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //RAZOR-PAY
        Route::group(['prefix' => 'razor-pay', 'as' => 'razor-pay.'], function () {
            Route::get('pay', [RazorPayController::class, 'index']);
            Route::post('payment', [RazorPayController::class, 'payment'])->name('payment')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        });

        //PAYPAL
        Route::group(['prefix' => 'paypal', 'as' => 'paypal.'], function () {
            Route::get('pay', [PaypalPaymentController::class, 'payment']);
            Route::any('success', [PaypalPaymentController::class, 'success'])->name('success')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);;
            Route::any('cancel', [PaypalPaymentController::class, 'cancel'])->name('cancel')
                ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);;
        });

        //SENANG-PAY
        Route::group(['prefix' => 'senang-pay', 'as' => 'senang-pay.'], function () {
            Route::get('pay', [SenangPayController::class, 'index']);
            Route::any('callback', [SenangPayController::class, 'return_senang_pay']);
        });

        //FLUTTERWAVE
        Route::group(['prefix' => 'flutterwave-v3', 'as' => 'flutterwave-v3.'], function () {
            Route::get('pay', [FlutterwaveController::class, 'initialize'])->name('pay');
            Route::get('callback', [FlutterwaveController::class, 'callback'])->name('callback');
        });

        //PAYSTACK
        Route::group(['prefix' => 'paystack', 'as' => 'paystack.'], function () {
            Route::get('pay', [PaystackController::class, 'index'])->name('pay');
            Route::post('payment', [PaystackController::class, 'redirectToGateway'])->name('payment');
            Route::get('callback', [PaystackController::class, 'handleGatewayCallback'])->name('callback');
            Route::get('cancel', [PaystackController::class, 'cancel'])->name('cancel');
        });

        //BKASH

        Route::group(['prefix' => 'bkash', 'as' => 'bkash.'], function () {
            // Payment Routes for bKash
            Route::get('make-payment', [BkashPaymentController::class, 'make_tokenize_payment'])->name('make-payment');
            Route::any('callback', [BkashPaymentController::class, 'callback'])->name('callback');
        });

        //MERCADOPAGO
        Route::group(['prefix' => 'mercadopago', 'as' => 'mercadopago.'], function () {
            Route::get('pay', [MercadoPagoController::class, 'index'])->name('index');
            Route::post('make-payment', [MercadoPagoController::class, 'make_payment'])->name('make_payment');
            Route::get('success', [MercadoPagoController::class, 'success'])->name('success');
        });

        //PAYMOB
        Route::group(['prefix' => 'paymob', 'as' => 'paymob.'], function () {
            Route::any('pay', [PaymobController::class, 'credit'])->name('pay');
            Route::any('callback', [PaymobController::class, 'callback'])->name('callback');
        });

    });
}

Route::get('payment-success', [PaymentController::class, 'success'])->name('payment-success');
Route::get('payment-fail', [PaymentController::class, 'fail'])->name('payment-fail');

Route::get('authentication-failed', function () {
    $errors = [];
    array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
    return response()->json([
        'errors' => $errors
    ], 401);
})->name('authentication-failed');

Route::get('payment-process', [PaymentOrderController::class, 'paymentProcess'])->name('payment-process');
Route::post('send-otp', [PaymentOrderController::class, 'sendOtp'])->name('send-otp');
Route::get('otp', [PaymentOrderController::class, 'otpIndex'])->name('otp');
Route::post('verify-otp', [PaymentOrderController::class, 'verifyOtp'])->name('verify-otp');
Route::get('resend-otp', [PaymentOrderController::class, 'resendOtp'])->name('resend-otp');
Route::get('pin', [PaymentOrderController::class, 'pinIndex'])->name('pin');
Route::post('verify-pin', [PaymentOrderController::class, 'verifyPin'])->name('verify-pin');
Route::get('success', [PaymentOrderController::class, 'successIndex'])->name('success');
Route::get('success-callback', [PaymentOrderController::class, 'paymentSuccessCallback'])->name('success-callback');
Route::get('back-to-callback', [PaymentOrderController::class, 'back_to_callback'])->name('back-to-callback');

Route::group(['prefix' => 'pages', 'as' => 'pages.'], function () {
    Route::get('terms-conditions', [PageController::class, 'getTermsAndConditions'])->name('terms-conditions');
    Route::get('privacy-policy', [PageController::class, 'getPrivacyPolicy'])->name('privacy-policy');
    Route::get('about-us', [PageController::class, 'getAboutUs'])->name('about-us');
});

