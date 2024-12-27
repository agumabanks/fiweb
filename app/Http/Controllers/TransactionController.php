<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;  
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Exception;
use App\Models\UserLoan;

use App\Models\LoanPayment;
use App\Models\Client;
use Illuminate\Support\Facades\Http; // Import the Http facade

use Illuminate\Support\Facades\Log;  // Import the Log facade


class TransactionController extends Controller
{
    /**
     * Display the transaction details.
     *
     * @param int $transactionId
     * @return \Illuminate\Http\Response
     */
    public function show($transactionId)
    {
        // Fetch the transaction details
        $transaction = LoanPayment::findOrFail($transactionId);

        return view('admin-views.transactions.show', compact('transaction'));
    }

    /**
     * Print the transaction receipt on a thermal printer.
     *
     * @param int $transactionId
     * @return \Illuminate\Http\JsonResponse 
     */
    public function printTransactionReceipt($transactionId)
    {
        try {
            // Fetch transaction details using the provided transaction ID
            $transaction = LoanPayment::find($transactionId);

            if (!$transaction) {
                throw new Exception("Transaction not found.");
            }

            // Choose the connector type based on your printer setup

            // Example for USB printer on Windows
            $connector = new WindowsPrintConnector("POS-58");

            // Example for network printer
            // $connector = new NetworkPrintConnector("192.168.0.100", 9100);

            // Initialize Printer
            $printer = new Printer($connector);

            // Print Header
            $printer->setTextSize(2, 2);
            $printer->text("Sanaa Finance\n");
            $printer->setTextSize(1, 1);
            $printer->text("Transaction Receipt\n");
            $printer->text("-----------------------------\n");

            // Print Transaction Details
            $printer->text("Transaction ID: " . $transaction->id . "\n");
            $printer->text("Date: " . $transaction->created_at->format('d M Y H:i:s') . "\n");
            $printer->text("Client: " . $transaction->client->name . "\n");
            $printer->text("Amount: UGX " . number_format($transaction->amount, 0) . "\n");
            $printer->text("Status: " . ucfirst($transaction->status) . "\n");
            $printer->text("-----------------------------\n");

            // Footer
            $printer->feed(2);
            $printer->cut();

            // Close printer connection
            $printer->close();

            return response()->json(['message' => 'Receipt printed successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Could not print receipt: ' . $e->getMessage()], 500);
        }
    }
    
    
     /**
     * Print the full payment statement for a specific client on a thermal printer.
     *
     * @param int $clientId
     * @return \Illuminate\Http\JsonResponse
     */
     

    //  showLoanStatment
    public function showLoanStatment2($loanId)
    {
        // Fetch the transaction details
            $client = Client::findOrFail($clientId);
            $payments = LoanPayment::where('loan_id', $loanId)->get();

            return view('admin-views.transactions.statment', compact('client','payments'));
    }

    


    public function showLoanStatment($loanId)
    {
        // Fetch the loan details along with the associated client and payments using eager loading
        $loan = UserLoan::with(['client', 'payments'])->findOrFail($loanId);
    
        // Access the related client and payments directly from the loan model
        $client = $loan->client;
        $payments = $loan->payments;

         // Calculate total amount paid and remaining balance
         $totalAmountPaid = $payments->sum('amount');
         $remainingBalance = $loan->final_amount - $totalAmountPaid;
    
        // Return the view with the retrieved data
        return view('admin-views.transactions.loanstatment', compact('client', 'payments','loan', 'totalAmountPaid', 'remainingBalance'));
    }

    
     public function showStatment($clientId)
    {
        // Fetch the transaction details
            $client = Client::findOrFail($clientId);
            $payments = LoanPayment::where('client_id', $clientId)->get();

            return view('admin-views.transactions.statment', compact('client','payments'));
    }
    
    public function printClientPaymentStatement($clientId)
    {
        try {
            // Fetch all payments made by the client
            $payments = LoanPayment::where('client_id', $clientId)->get();

            if ($payments->isEmpty()) {
                throw new Exception("No payments found for this client.");
            }

            // Choose the connector type based on your printer setup
            $connector = new WindowsPrintConnector("POS-58");

            // Initialize Printer
            $printer = new Printer($connector);

            // Print Header
            $printer->setTextSize(2, 2);
            $printer->text("Sanaa Finance\n");
            $printer->setTextSize(1, 1);
            $printer->text("Client Payment Statement\n");
            $printer->text("-----------------------------\n");

            // Print Client Details
            $clientName = $payments->first()->client->name;
            $printer->text("Client: " . $clientName . "\n");
            $printer->text("-----------------------------\n");

            // Print Payment Details
            foreach ($payments as $payment) {
                $printer->text("Payment ID: " . $payment->id . "\n");
                $printer->text("Date: " . $payment->created_at->format('d M Y H:i:s') . "\n");
                $printer->text("Amount: UGX " . number_format($payment->amount, 0) . "\n");
                $printer->text("-----------------------------\n");
            }

            // Footer
            $printer->feed(2);
            $printer->cut();

            // Close printer connection
            $printer->close();

            return response()->json(['message' => 'Payment statement printed successfully.']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Could not print payment statement: ' . $e->getMessage()], 500);
        }
    }
    
   
    public function SmsNotification(Request $request)
        {
            try {
                // API Key and Sender from .env file or default values
                $apiKey = env('TRUST_SMS_API_KEY', '6FZKU5'); 
                $sender = env('TRUST_SMS_SENDER_ID', ''); // Empty if no sender ID required
                $url = 'https://portal.trustsmsuganda.com/text_api/';
        
                // Fetch transaction by ID
                $transactionId = $request->route('payment'); 
                $transaction = LoanPayment::find($transactionId);
        
                if (!$transaction) {
                    throw new Exception("Transaction not found.");
                }
        
                // Dynamic SMS content, replace with actual content as needed
                // $smsText = "Dear {$transaction->client->name}, Loan payment notification: {$transaction->amount} paid. Your Balance is {$transaction->credit_balance}. Thank you for choosing {{\App\CentralLogics\Helpers::get_business_settings('business_name')}}! Contact us: {{\App\CentralLogics\Helpers::get_business_settings('phone')}} or {{\App\CentralLogics\Helpers::get_business_settings('hotline_number')}} Generated by Sanaa Co.";
                $smsText = "Dear {$transaction->client->name}, Loan payment notification: {$transaction->amount} paid. Your Balance is {$transaction->credit_balance}. Thank you for choosing " . \App\CentralLogics\Helpers::get_business_settings('business_name') . "! Contact us: " . \App\CentralLogics\Helpers::get_business_settings('phone') . " or " . \App\CentralLogics\Helpers::get_business_settings('hotline_number') . ". Generated by Sanaa Co.";


               // Prepare the query parameters as an associative array
                $params = [
                    'api_key'  => $apiKey,
                    'sender'   => $sender,
                    'contacts' => $transaction->client->phone,
                    'text'     => $smsText,
                ];

                // Determine the proper separator ( '?' or '&' ) based on the base URL
                $separator = (parse_url($url, PHP_URL_QUERY) === null) ? '?' : '&';

                // Build the full URL with encoded query parameters
                $fullUrl = $url . $separator . http_build_query($params);

                // Construct the full API URL with the query parameters
                // $fullUrl = $url . '?api_key=' . $apiKey . '&sender=' . $sender . '&contacts=' . "{$transaction->client->phone}" . '&text=' . urlencode($smsText);
        
                // Send the SMS request using GET
                $response = Http::get($fullUrl);
        
                // Handle API response
                if ($response->successful()) {
                    $responseData = $response->json();
        
                    if (isset($responseData['success']) && strtolower($responseData['success']) === 'true') {
                        // SMS sent successfully, redirect with success message
                        return redirect()->back()->with('status', 'SMS sent successfully. Status: ' . $responseData['status']);
                    } else {
                        // Log error message from API response
                        Log::error('SMS sending failed: ' . ($responseData['res_msg'] ?? 'Unknown error'));
                        return redirect()->back()->with('error', 'SMS sending failed: ' . ($responseData['alert'] ?? 'Unknown error'));
                    }
                } else {
                    // HTTP request failed
                    Log::error('SMS sending HTTP request failed: ' . $response->status());
                    return redirect()->back()->with('error', 'SMS request failed.');
                }
            } catch (Exception $e) {
                // Log any exceptions that occur
                Log::error('SMS sending exception: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to send SMS. Please try again.');
            }
        }

    
    
        public function SmsNotificationX(Request $request)
            {
                try {
                    // API Key and Sender from config or .env file
                    $apiKey = env('TRUST_SMS_API_KEY', '6FZKU5'); 
                    $sender = env('TRUST_SMS_SENDER_ID', '+256706272481'); 
                    $url = 'https://portal.trustsmsuganda.com/text_api/';
            
                    // Fetch transaction by ID
                    $transactionId = $request->route('payment'); // Ensure that 'payment' matches your route parameter
                    $transaction = LoanPayment::find($transactionId);
            
                    if (!$transaction) {
                        throw new Exception("Transaction not found.");
                    }
            
                    // Dynamic SMS content
                    $smsText = "Loan payment notification: {$transaction->amount} paid.";
            
                    // Send SMS via Trust SMS Uganda API
                    $response = Http::get($url, [
                        'api_key'  => $apiKey,
                        'sender'   => $sender,
                        'contacts' => $transaction->client_phone, // Assuming transaction has client phone number
                        'text'     => $smsText,
                    ]);
            
                    // Handle API response
                    if ($response->successful()) {
                        $responseData = $response->json();
            
                        if (isset($responseData['success']) && strtolower($responseData['success']) === 'true') {
                            // SMS sent successfully, redirect back with success message
                            Toastr::success(translate('SMS sent successfully'));
                            return redirect()->back()->with('status', 'SMS sent successfully. Status: ' . $responseData['status']);
                        } else {
                            // Log error message from API response
                            Log::error('SMS sending failed: ' . ($responseData['res_msg'] ?? 'Unknown error'));
                            return redirect()->back()->with('error', 'SMS sending failed: ' . ($responseData['alert'] ?? 'Unknown error'));
                        }
                    } else {
                        // HTTP request failed
                        Log::error('SMS sending HTTP request failed: ' . $response->status());
                        Toastr::success(translate('SMS sending HTTP request failed'));
                        return redirect()->back()->with('error', 'SMS request failed.');
                    }
                } catch (Exception $e) {
                    // Log exceptions
                    Log::error('SMS sending exception: ' . $e->getMessage());
                    return redirect()->back()->with('error', 'Failed to send SMS. Please try again.');
                }
            }

}
