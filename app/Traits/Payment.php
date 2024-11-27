<?php

namespace App\Traits;

use InvalidArgumentException;
use App\Models\PaymentRequest;
use App\Models\User;
use App\Models\IpnDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

trait Payment
{
    private $consumerKey;
    private $consumerSecret;
    private $apiUrl;
    private $callbackUrlNew;
    private $tokenUrl;
    private $url;

    public function __construct()
    {
        $this->consumerKey = env('PESAPAL_CONSUMER_KEY', 'Vf06nKZmPojauheEvdJGgCekGYHc3k3q');
        $this->consumerSecret = env('PESAPAL_CONSUMER_SECRET', 'iTLdYU/qckOYZQeqgYDYcd7yojE=');
        $this->apiUrl = env('PESAPAL_API_URL', 'https://pay.pesapal.com/v3');
        $this->callbackUrlNew = url('/pesapal/callBackUr');
        $this->tokenUrl = 'https://pay.pesapal.com/v3/api/Auth/RequestToken';
        $this->url = "https://pay.pesapal.com/v3";
    }

    public function generate_link(object $payer, object $payment_info, object $receiver)
    {
        if ($payment_info->getPaymentAmount() === 0) {
            throw new InvalidArgumentException('Payment amount cannot be 0');
        }

        if (!in_array(strtoupper($payment_info->getCurrencyCode()), array_column(GATEWAYS_CURRENCIES, 'code'))) {
            throw new InvalidArgumentException('Need a valid currency code');
        }

        if (!in_array($payment_info->getPaymentMethod(), array_column(GATEWAYS_PAYMENT_METHODS, 'key'))) {
            throw new InvalidArgumentException('Need a valid payment gateway');
        }

        if (!is_array($payment_info->getAdditionalData())) {
            throw new InvalidArgumentException('Additional data should be in a valid array');
        }

        $payment = new PaymentRequest();
        $payment->payment_amount = $payment_info->getPaymentAmount();
        $payment->success_hook = $payment_info->getSuccessHook();
        $payment->failure_hook = $payment_info->getFailureHook();
        $payment->payer_id = $payment_info->getPayerId();
        $payment->receiver_id = $payment_info->getReceiverId();
        $payment->currency_code = strtoupper($payment_info->getCurrencyCode());
        $payment->payment_method = $payment_info->getPaymentMethod();
        $payment->additional_data = json_encode($payment_info->getAdditionalData());
        $payment->payer_information = json_encode($payer->information());
        $payment->receiver_information = json_encode($receiver->information());
        $payment->external_redirect_link = $payment_info->getExternalRedirectLink();
        $payment->attribute = $payment_info->getAttribute();
        $payment->attribute_id = $payment_info->getAttributeId();
        $payment->payment_platform = $payment_info->getPaymentPlatForm();
        $payment->save();
        
        $paymentId = 
        
        $paymentUrl = 'https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest'; // Example Pesapal payment URL

        if (!filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException("Invalid payment URL");
        }

        // Assuming $orderDetails is populated with necessary order details
        // $orderDetails = [
        //     'Reference' => $payment_info->getPayerId(),
        //     'Amount' => $payment_info->payment_amount,
        //     'Description' => 'Payment Description',
        //     'Email' => $payer->email,
        //     'PhoneNumber' => $payer->phone,
        //     'Name' => $payer->name,
        // ];

        $user_id = $payment_info->getPayerId();// $payment_info->payer_id; // Assuming $user_id is derived from payer_id
        $sanaaUser = User::where('type', '!=', 0)->find($user_id);
        
        // $user['phone']
        $name = $sanaaUser['f_name'].' '.$sanaaUser['l_name'];
        $phone = $sanaaUser['phone'];
        try {
            
            Session::put('paymentId', $payment->id);
            $token = $this->getAccessToken($user_id);
            $ipnId = $this->registerIpn($user_id);
            $headers = [
                'Accept' => 'text/plain',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];

            $postData = [
                "id" => $payment_info->getPayerId() .Str::uuid(),
                "currency" => "UGX",
                "amount" => $payment_info->getPaymentAmount(),
                "description" => json_encode($payment_info->getAdditionalData()),
                "callback_url" => url('/pesapal/callBackUr'),
                "notification_id" => $ipnId,
                "billing_address" => [
                    "email_address" => 'banks@sanaa.co',
                    "phone_number" => $phone,
                    "country_code" => "UG",
                    "first_name" => $name,
                    "middle_name" => "",
                    "last_name" => "",
                    "line_1" => "",
                    "line_2" => "",
                    "city" => "",
                    "state" => "",
                    "postal_code" => "",
                    "zip_code" => ""
                ]
            ];

            $client = new Client();
             // $token = $this->getAccessToken($user_id);
            $response = $client->post($paymentUrl, [
                'headers' => $headers,
                'body' => json_encode($postData),
                
            ]);

           $responseBody = $response->getBody()->getContents();

            // Decode the response body to an associative array
            $responseData = json_decode($responseBody, true);
            
            // Log the response
            Log::error('Pesapal resposer: ' . $responseBody);
            
            // Return the redirect_url if it exists
            if (isset($responseData['redirect_url'])) {
                return $responseData['redirect_url'];
            } else {
                throw new \Exception('Redirect URL not found in the response.');
            }

        } catch (RequestException $e) {
            Log::error('Pesapal er: ' . $e->getMessage());
            throw new \RuntimeException("Pesapal Payment Exception on pay: " . $e->getMessage());
        } catch (\Exception $e) {
            throw new \RuntimeException("General Payment Exception: " . $e->getMessage());
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





    protected function getAccessToken5($userId)
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

        $client = new Client();
        $response = $client->post($this->tokenUrl, [
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
        $amount = $request->amount;
        $description = $request->user()->id . " Pesa pal order";
        $user = User::find($request->user()->id);

        $client_name = $user->name;
        $client_phone = $user->phone;
        $client_email = $user->email;

        $orderDetails = [
            'Amount' => $amount,
            'Description' => $description,
            'Type' => 'MERCHANT',
            'Name' => $client_name,
            'Reference' => auth()->id() . $request->combined_order_id,
            'PhoneNumber' => $client_phone,
            'Email' => $client_email,
        ];

        try {
            $token = $this->getAccessToken(auth()->id());
            $ipnId = $this->registerIpn(auth()->id());
            $headers = [
                'Accept' => 'text/plain',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];

            $postData = [
                "id" => $orderDetails['Reference'],
                "currency" => "UGX",
                "amount" => $orderDetails['Amount'],
                "description" => $orderDetails['Description'],
                "callback_url" => $this->callbackUrlNew,
                "notification_id" => $ipnId,
                "billing_address" => [
                    "email_address" => $orderDetails['Email'],
                    "phone_number" => $orderDetails['PhoneNumber'],
                    "country_code" => "UG",
                    "first_name" => $orderDetails['Name'],
                    "middle_name" => "",
                    "last_name" => "",
                    "line_1" => "",
                    "line_2" => "",
                    "city" => "",
                    "state" => "",
                    "postal_code" => "",
                    "zip_code" => ""
                ]
            ];

            $response = Http::withHeaders($headers)->post($this->url . '/api/Transactions/SubmitOrderRequest', $postData);

            if ($response->failed()) {
                Log::error('Pesapal Get Merchant Order URL Error: ' . $response->body());
                throw new \Exception('Could not get Merchant Order URL.');
            }

            $responseBody = $response->json();
            $link = $responseBody["redirect_url"];
            return response()->json(['link' => $link], 200);

        } catch (\Exception $ex) {
            Log::error('Pesapal Payment Exception on pay: ' . $ex->getMessage());
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    protected function registerIpn($userId)
    {
       
        try {
            $token = $this->getAccessToken($userId);

            $headers = [
                'Accept' => 'text/plain',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];

            $data = [
                'url' => route('ipn'),
                'ipn_notification_type' => 'GET',
            ];

            $response = Http::withHeaders($headers)->post('https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN', $data);

            if ($response->failed()) {
                Log::error('Pesapal Register IPN Error: ' . $response->body());
                throw new \Exception('Could not register IPN.');
            }

            $responseBody = $response->json();
            $ipnId = $responseBody['ipn_id'];

            // IpnDetail::create([
            //     'user_id' => $userId,
            //     'ipn_id' => $ipnId,
            // ]);   2

            return $ipnId;

        } catch (\Exception $ex) {
            Log::error('Pesapal IPN Registration Exception: ' . $ex->getMessage());
            throw new \Exception('Could not register IPN.');
        }
    }
}
