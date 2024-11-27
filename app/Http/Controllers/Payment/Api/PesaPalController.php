<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerPackageController;
use App\Http\Controllers\SellerPackageController;
use App\Http\Controllers\WalletController;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\IpnDetail;  
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



use Illuminate\Support\Facades\Http;

class PesapalController extends Controller
{
    private $consumerKey;
    private $consumerSecret;
    private $apiUrl;
    private $callbackUrlNew;
    private $tokenUrl;

    public function __construct()
    {
        $this->consumerKey = 'Vf06nKZmPojauheEvdJGgCekGYHc3k3q';
        $this->consumerSecret = 'iTLdYU/qckOYZQeqgYDYcd7yojE=';
        $this->apiUrl = env('PESAPAL_API_URL', 'https://pay.pesapal.com/v3');
        $this->callbackUrlNew =  url('pesapal/payment/done'); 
        $this->tokenUrl = 'https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken';
        $this->url =   "https://pay.pesapal.com/v3";
    }

 
    protected function getAccessToken()
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


     public function validateKeys()
        {
            $token = $this->getAccessToken();
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
    
    
    
    public function registerIpn()
    {
    $token = $this->getAccessToken();
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
    
     $user_id = '21';

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
        $token = $this->getAccessToken();
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
        $token = $this->getAccessToken();
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

    
    
    
    
    /**
    * Get Merchant Order URL
    */
  
   public function callBackUr2(Request $request)
    {
        // dd();
        // Extract the parameters from the request
        $orderTrackingId = $request->query('OrderTrackingId');
        $orderMerchantReference = $request->query('OrderMerchantReference');
        
        
        // Retrieve the combined order using the session data
        $combinedOrderId = Session::get('combined_order_id');
        $combinedOrder = CombinedOrder::findOrFail($combinedOrderId);

        // Save the payment details in JSON format
        $paymentDetails = json_encode($request->all());

        // Update the payment status and details for each order
        foreach ($combinedOrder->orders as $order) {
            $order = Order::findOrFail($order->id);
            $order->payment_status = 'paid';
            $order->payment_details = $paymentDetails;
            $order->save();

            // Perform any additional operations like calculating commissions
            calculateCommissionAffilationClubPoint($order);
        }
        // Store the combined order ID back into the session if needed
        Session::put('combined_order_id', $combinedOrderId);
        
         Session::forget('pesapal_token');
        // Redirect to the order confirmed page
        return redirect()->route('order_confirmed');
    }
    
    public function pay(Request $request)
    {
       
        
        $amount = 0;

    
       
        $description = $description_id . "Pesa pal order";

        
          # CUSTOMER INFORMATION User::find($request->value_d)
            // phone number
            // user id
            // user phone

            $user = User::find($description_id);
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
            $token = $this->getAccessToken();
            $ipnId = $this->registerIpn();
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
        // dd($response->json());
        $iframeUrl = $response["redirect_url"];
        return view('frontend.mpesa.pesapal', compact('iframeUrl'));
        
        } catch (\Exception $ex) {
            Log::error('Pesapal Payment Exception on pay: ' . $ex->getMessage());
            flash(translate('Something went wrong'))->error();
            return redirect()->route('home');
        }
        

    }
    
    
    
     /**
     * Get Transaction Status
     */
    public function getTransactionStatus($orderTrackingId, $access_token)
    {
        $token = $this->getAccessToken();
        $headers = [
            'Accept' => 'text/plain',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ];
        

        $response = Http::withHeaders($headers)->get($this->url . '/api/Transactions/GetTransactionStatus', [
            'orderTrackingId' => $orderTrackingId
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
            $token = $this->getAccessToken();
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
