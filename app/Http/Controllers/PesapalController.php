<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;

use App\Models\Order;
use Illuminate\Http\Request;
// use App\Models\IpnDetail;  
use App\Models\CombinedOrder;
use App\Models\CustomerPackage;
use App\Models\SellerPackage;
use GuzzleHttp\Client;
use Session;
use Redirect;
use Carbon\Carbon;
use App\Models\User;


use App\Http\Controllers\Api\V1\Agent\AgentController;
use App\Http\Controllers\Api\V1\Customer\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Customer\WithdrawController;
use App\Http\Controllers\Api\V1\Agent\AgentWithdrawController;
use App\Http\Controllers\Api\V1\Agent\Auth\PasswordResetController as AgentPasswordResetController;
use App\Http\Controllers\Api\V1\Agent\TransactionController as AgentTransactionController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ConfigController;
use App\Http\Controllers\Api\V1\Customer\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\Customer\TransactionController;
use App\Http\Controllers\Api\V1\GeneralController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OTPController;
use App\Http\Controllers\Api\V1\RegisterController;
use App\Http\Controllers\Payment\Api\PaymentOrderController;
use App\Http\Controllers\Api\V1\Agent\Auth\AgentAuthController;
use Illuminate\Support\Facades\Route;
use App\Models\PaymentRequest;

use App\Traits\Processor;



use Illuminate\Support\Facades\Http;

// use App\Models\PaymentRequest;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Session;
// use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Foundation\Application;

class PesapalController extends Controller
{
    use Processor;
    private $consumerKey;
    private $consumerSecret;
    private $apiUrl;
    private $callbackUrlNew;
    private $tokenUrl;
    // private User $user;

    public function __construct()
    {
        $this->consumerKey = 'Vf06nKZmPojauheEvdJGgCekGYHc3k3q';
        $this->consumerSecret = 'iTLdYU/qckOYZQeqgYDYcd7yojE=';
        $this->apiUrl = env('PESAPAL_API_URL', 'https://pay.pesapal.com/v3');
        $this->callbackUrlNew =  url('pesapal/payment/done'); 
        $this->tokenUrl = 'https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken';
        $this->url =   "https://pay.pesapal.com/v3";
    }


    protected function getAccessToken2()
       {
    // Check if the token and its expiration time are stored in the session
    if (Session::has('pesapal_token') && Session::has('pesapal_token_expires_at')) {
        $token = Session::get('pesapal_token');
        $expiresAt = Session::get('pesapal_token_expires_at');

        // Check if the token has expired
        if (Carbon::now()->lessThan(Carbon::parse($expiresAt))) {
            return $token;
        }

        // Token has expired, remove it from the session
        Session::forget('pesapal_token');
        Session::forget('pesapal_token_expires_at');
    }

    // Request a new token from Pesapal
    $client = new Client();
    $response = $client->post('https://pay.pesapal.com/v3/api/Auth/RequestToken', [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ],
        'json' => [
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret,
        ],
    ]);

    $body = json_decode($response->getBody(), true);

    if (isset($body['token'])) {
        // Store the new token and its expiration time in the session
        $expiresAt = Carbon::now()->addMinutes(5);  // Set to 5 minutes
        Session::put('pesapal_token', $body['token']);
        Session::put('pesapal_token_expires_at', $expiresAt);
        return $body['token'];
    } else {
        Log::error('Pesapal Token Error on access request: ' . json_encode($body));
        throw new \Exception('Could not retrieve Pesapal token.');
    }
}

    
    protected function getAccessToken($userId)
    {
        if (Session::has('pesapal_token') && Session::has('pesapal_token_expires_at') && Session::has('pesapal_user_id')) {
            $token = Session::get('pesapal_token');
            $expiresAt = Session::get('pesapal_token_expires_at');
            $sessionUserId = Session::get('pesapal_user_id');

            if ($userId === $sessionUserId) {
                if (Carbon::now()->lessThan(Carbon::parse($expiresAt))) {
                    return $token;
                }

                Session::forget(['pesapal_token', 'pesapal_token_expires_at', 'pesapal_user_id']);
            } else {
                Session::forget(['pesapal_token', 'pesapal_token_expires_at', 'pesapal_user_id']);
            }
        }
        $tokenUrl = 'https://pay.pesapal.com/v3/api/Auth/RequestToken';
        $client = new Client();
        $response = $client->post($tokenUrl, [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'consumer_key' => 'Vf06nKZmPojauheEvdJGgCekGYHc3k3q',
                'consumer_secret' => 'iTLdYU/qckOYZQeqgYDYcd7yojE=',
            ],
        ]);
        
        
        // $this->consumerKey = env('PESAPAL_CONSUMER_KEY', 'Vf06nKZmPojauheEvdJGgCekGYHc3k3q');
        // $this->consumerSecret = env('PESAPAL_CONSUMER_SECRET', 'iTLdYU/qckOYZQeqgYDYcd7yojE=');

        $body = json_decode($response->getBody(), true);

        if (isset($body['token'])) {
            $expiresAt = Carbon::now()->addMinutes(5);
            Session::put('pesapal_token', $body['token']);
            Session::put('pesapal_token_expires_at', $expiresAt);
            Session::put('pesapal_user_id', $userId);
            return $body['token'];
        } else {
            Log::error('Pesapal Token Error on access request: ' . json_encode($body));
            throw new \Exception('Could not retrieve Pesapal token.');
        }
    }

   public function pay(Request $request): JsonResponse
    {
        // $validator = Validator::make($request->all(), [
        //     'amount' => 'required',
        //     // 'userid' => 'required'
        // ]);
        
        //  if ($validator->fails()) {
        //     return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        // }

        // $addMoneyStatus = Helpers::get_business_settings('add_money_status');

        // if (!$addMoneyStatus)
        //     return response()->json(['message' => translate('add money feature is not activate')], 403);

        // if($request->user()->is_kyc_verified != 1) {
        //     return response()->json(['message' => translate('Verify your account information')], 403);
        // }
        
        $amount = $request->amount;
        
        $amount = $request->amount;
        $bonus = Helpers::get_add_money_bonus($amount, $request->user()->id, 'customer');
        $totalAmount = $amount + $bonus;
        
        
        $description = $description_id . "Pesa pal order";
        $user_id = $request->userid;
        $user = User::find($user_id);
        $user_phone = $this->user->where(['phone' => $phone, 'type' => CUSTOMER_TYPE])->first();
        # CUSTOMER INFORMATION User::find($request->value_d)
            // phone number
            // user id
            // user phone

            
            $client_name = $user->name;
            $client_address = $user->address;
          
            $client_phone = $user->phone;
            $client_email = $user->email;
            
            
            
        $orderDetails = [
            'Amount' => $amount,
            'Description' => $description,
            'Type' => 'MERCHANT',
            'Name' => $client_name,
            'Reference' => auth()->id() .$combined_order->id,
            'PhoneNumber' => $client_phone,
            'Email' => $client_email,
            'Currency' => \App\Models\Currency::findOrFail(get_setting('system_default_currency'))->code,
        ];
        
      

        try {
            $token = $this->getAccessToken($user_id);
            $ipnId = $this->registerIpn($user_id);
            $headers = [
                'Accept' => 'text/plain',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];
            
            
            
            
            $postData = [
            "id"=> $orderDetails['Reference'],
            "currency"=> "UGX",
            "amount"=> $orderDetails['Amount'],
            "description"=>$orderDetails['Description'],
            "callback_url"=> $this->callbackUrlNew,
            "notification_id"=> $ipnId,
            "billing_address"=> [
                "email_address"=> $orderDetails['Email'],
                "phone_number"=> $orderDetails['PhoneNumber'],
                "country_code"=> "UG",
                "first_name"=> $orderDetails['Name'],
                "middle_name"=> "",
                "last_name"=> "",
                "line_1"=> "",
                "line_2"=>"",
                "city"=> "",
                "state"=> "",
                "postal_code"=> "",
                "zip_code"=> ""
                ]
                
    
        ];

        $response = Http::withHeaders($headers)->post($this->url . '/api/Transactions/SubmitOrderRequest', $postData);


        if ($response->failed()) {
            Log::error('Pesapal Get Merchant Order URL Error: ' . $response->body());
            throw new \Exception('Could not get Merchant Order URL.');
        }

        
        $response->json();
        $link = $response["redirect_url"];
        return response()->json(['link' => $link], 200);
        
        } catch (\Exception $ex) {
            Log::error('Pesapal Payment Exception on pay: ' . $ex->getMessage());
            flash(translate('Something went wrong'))->error();
            return redirect()->route('home');
        }
        

    }

   
   public function validateKeys()
    {
            $token = $this->getAccessToken($user_id);
            $client = new Client();
            $response = $client->post('https://pay.pesapal.com/v3/api/Merchant/ValidateKeysExpres', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'json' => [
                    'consumer_key' => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                ],
            ]);
            
            
          
            if ($response->failed()) {
                Log::error('Pesapal Validate Keys Error: ' . $response->body());
                throw new \Exception('Could not validate Pesapal keys.');
            }
    
            return $response->json();
        }
    

   public function registerIpn($user_id)
    {
    $token = $this->getAccessToken($user_id);
    $client = new Client();
    $response = $client->post('https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN', [
        'headers' => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ],
        'json' => [
            'consumer_key' => 'Vf06nKZmPojauheEvdJGgCekGYHc3k3q',
            'consumer_secret' => 'iTLdYU/qckOYZQeqgYDYcd7yojE=',
            'url' => 'https://soko.ug/pesapal/ipn',
            'ipn_notification_type' => 'GET'
        ],
    ]);
    
    $body = json_decode($response->getBody(), true);
    
     $user_id = '24';

    // Creating a new IPN detail record
        IpnDetail::create([
            'user_id' => $user_id,
            'url' => $body["url"],
            'created_date' => $body["created_date"],
            'ipn_id' => $body["ipn_id"],
            'notification_type' => $body["notification_type"],
            'ipn_notification_type_description' => $body["ipn_notification_type_description"],
            'ipn_status' => $body["ipn_status"],
            'ipn_status_description' => $body["ipn_status_decription"],
            'error' => null,
            'status' => $body["status"]
        ]);

    
     return $body["ipn_id"];
}
    
    
   public function getRegisteredIpn()
    {
        $token = $this->getAccessToken($user_id);
        $headers = [
            'Accept' => 'text/plain',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = Http::withHeaders($headers)->get('https://pay.pesapal.com/v3/api/URLSetup/GetIpnList');
        //  https://pay.pesapal.com/v3/api/URLSetup/GetIpnList

        if ($response->failed()) {
            Log::error('Pesapal Get Registered IPN Error: ' . $response->body());
            throw new \Exception('Could not get registered IPN.');
        }

        return $response->json();
    }
    
   public function generateNotificationId($callback, $access_token)
    {
        $token = $this->getAccessToken($user_id);
        $headers = [
            'Accept' => 'text/plain',
            'Content-Type' => 'application/json',
             'Authorization' => 'Bearer ' . $token,
        ];

        $postData = [
            'url' => $callback,
            'ipn_notification_type' => 'GET'
        ];

        $response = Http::withHeaders($headers)->post($this->url . '/api/URLSetup/RegisterIPN', $postData);

        if ($response->failed()) {
            Log::error('Pesapal Generate Notification ID Error: ' . $response->body());
            throw new \Exception('Could not generate IPN Notification ID.');
        }

        return $response->json()['ipn_id'];
    }

  public function callBackUr2(Request $request)
    {
        // Log the incoming request data
        Log::info('Handling payment with request data:', $request->all());

        // Retrieve session data
        $sessionUserId = Session::get('pesapal_user_id');
        $sessionPayId = Session::get('paymentId');
        Log::info('Session Data', ['pesapal_user_id' => $sessionUserId, 'paymentId' => $sessionPayId]);

        // Extract the parameters from the request
        $orderTrackingId = $request->query('OrderTrackingId');
        $orderMerchantReference = $request->query('OrderMerchantReference');
        Log::info('Order tracking ID and Merchant Reference', ['OrderTrackingId' => $orderTrackingId, 'OrderMerchantReference' => $orderMerchantReference]);

        // Save the payment details in JSON format
        $paymentDetails = json_encode($request->all());
        Log::info('Payment details saved as JSON:', ['paymentDetails' => $paymentDetails]);

        // Retrieve the user
        $user = User::where('type', '!=', 0)->find($sessionUserId);
        Log::info('User found:', $user ? $user->toArray() : ['error' => 'User not found']);

        // TRANSACTION VERIFICATION
        $tx = $this->getTransactionStatus($orderTrackingId, $sessionUserId);
        Log::info('Transaction Status data', $tx);

        // Check the status code to determine the transaction result
        if ($tx['payment_status_description'] == 'Completed') {
            // Update the PaymentRequest
            PaymentRequest::where(['id' => $sessionPayId])->update([
                'payment_method' => $tx['payment_method'],
                'is_paid' => 1,
                'transaction_id' => $orderTrackingId,
            ]);
            Log::info('PaymentRequest updated', ['id' => $sessionPayId, 'transaction_id' => $orderTrackingId]);

            // Retrieve the updated PaymentRequest
            $data = PaymentRequest::where(['id' => $sessionPayId])->first();
            Log::info('PaymentRequest found:', $data ? $data->toArray() : ['error' => 'PaymentRequest not found']);

            // Call the success hook if it exists
            if (isset($data) && function_exists($data->success_hook)) {
                call_user_func($data->success_hook, $data);
            }

            // Clear the session data
            Log::info('Clearing session data for pesapal_user_id, paymentId, and pesapal_token');
            Session::forget('pesapal_user_id');
            Session::forget('paymentId');
            Session::forget('pesapal_token');

            // Return the payment response
            return $this->payment_response($data, 'success');
        } else {
            $payment_data = PaymentRequest::where(['id' => $sessionPayId])->first();
            Log::info('Payment failed:', $tx);

            if (isset($payment_data) && function_exists($payment_data->failure_hook)) {
                call_user_func($payment_data->failure_hook, $payment_data);
            }
            return $this->payment_response($payment_data, 'fail');
        }
    }

    /**
     * Get Transaction Status
     */
    public function getTransactionStatus($orderTrackingId, $userId)
    {
        $token = $this->getAccessToken($userId);
        $headers = [
            'Accept' => 'text/plain',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];

        $response = Http::withHeaders($headers)->get('https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus', [
            'orderTrackingId' => $orderTrackingId,
        ]);

        if ($response->failed()) {
            Log::error('Pesapal Get Transaction Status Error: ' . $response->body());
            throw new \Exception('Could not get transaction status.');
        }

        return $response->json();
    }
 


    public function getCancel(Request $request)
    {
        $request->session()->forget('order_id');
        $request->session()->forget('payment_data');
        flash(translate('Payment cancelled'))->success();
        return redirect()->route('home');
    }

    public function getDone(Request $request)
    {
        $trackingId = $request->input('pesapal_transaction_tracking_id');
        $merchantReference = $request->input('pesapal_merchant_reference');
        
        $client = new Client();
        try {
            $token = $this->getAccessToken($user_id);
            $verificationResponse = $client->get("https://pay.pesapal.com/v3/api/Transactions/GetTransactionStatus", [
                'query' => [
                    'orderTrackingId' => $trackingId,
                    'orderMerchantReference' => $merchantReference,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            $verificationResult = json_decode($verificationResponse->getBody(), true);

            if ($verificationResult['status'] === 'COMPLETED') {
                
                if ($request->session()->has('payment_type')) {
                    if ($request->session()->get('payment_type') == 'cart_payment') {
                        return (new CheckoutController)->checkout_done($request->session()->get('combined_order_id'), json_encode($verificationResult));
                    } elseif ($request->session()->get('payment_type') == 'wallet_payment') {
                        return (new WalletController)->wallet_payment_done($request->session()->get('payment_data'), json_encode($verificationResult));
                    } elseif ($request->session()->get('payment_type') == 'customer_package_payment') {
                        return (new CustomerPackageController)->purchase_payment_done($request->session()->get('payment_data'), json_encode($verificationResult));
                    } elseif ($request->session()->get('payment_type') == 'seller_package_payment') {
                        return (new SellerPackageController)->purchase_payment_done($request->session()->get('payment_data'), json_encode($verificationResult));
                    }
                }
                
            } else {
                flash(translate('Payment verification failed'))->error();
                return redirect()->route('home');
            }
        } catch (\Exception $ex) {
            Log::error('Pesapal Verification Exception: ' . $ex->getMessage());
            flash(translate('Something went wrong'))->error();
            return redirect()->route('home');
        }
    }
}
