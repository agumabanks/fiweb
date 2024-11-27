<?php

namespace App\Http\Controllers;

use App\Models\Client;

use App\Models\SanaaCard;
// use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\View as ViewFacade;

// use Barryvdh\DomPDF\Facade\Pdf;
// use Barryvdh\DomPDF\PDF;

// use Spatie\LaravelPdf\Facades\Pdf;
use App\CentralLogics\Helpers;



use Illuminate\Http\Request;
// use App\Models\SanaaCard;
use PDF;

class CardController extends Controller
{
    
    public function getAgentClientCards($agentId)
    {
        try {
            // Retrieve all clients added by the specific agent
            $clients = Client::where('added_by', $agentId)->get();

            // Initialize an array to store the client card data
            $responseData = [];

            // Loop through each client and retrieve their associated card data
            foreach ($clients as $client) {
                $cards = SanaaCard::where('client_id', $client->id)->get();

                foreach ($cards as $card) {
                    // Prepare client data
                    $data = [
                        'clientid' => $client->id,
                        'phone' => $client->phone,
                        'cvv' => $card->cvv,
                        'expiry_date' => $card->expiry_date,
                        'pan' => $card->pan,
                        'card_status' => $card->card_status,
                        'name' => $client->name,
                    ];

                    // Generate QR code for the client data
                    $qr = Helpers::get_qrcode_client($data);

                    // Add client data and QR code to the response data
                    $responseData[] = [
                        'client_data' => $data,
                        'qr_code' => strval($qr),
                    ];
                }
            }

            // Return the response with all client card data and corresponding QR codes
            return response()->json($responseData, 200);

        } 
        catch (\Exception $e) {
            \Log::error('Failed to retrieve client cards for agent: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while retrieving client cards for this agent.'], 500);
        }
    
    }
   
   public function getAllAgents()
   {
        try {
            // Fetch all agents
            $agents = User::where('role', 'agent')->select('id', 'f_name', 'l_name')->get();

            // Return agents list as JSON response
            return response()->json($agents, 200);
        } catch (\Exception $e) {
            \Log::error('Failed to retrieve agents: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while retrieving agents.'], 500);
        }
    }
   
   
   
    
    public function getAllClientsCardApi()
{
    try {
        // Retrieve all Sanaa cards
        $cards = SanaaCard::all();

        // Initialize an array to store the client card data
        $responseData = [];

        // Loop through each card and retrieve the associated client data
        foreach ($cards as $card) {
            $clientId = $card->client_id;
            $client = Client::findOrFail($clientId);

            // Prepare client data
            $data = [
                'clientid' => $client->id,
                'phone' => $client->phone,
                'cvv' => $card->cvv,  // Assuming 'cvv' is part of the card data
                'expiry_date' => $card->expiry_date,
                'pan' => $card->pan,
                'card_status' => $card->card_status,
                'name' => $client->name,
            ];

            // Generate QR code for the client data
            $qr = Helpers::get_qrcode_client($data);

            // Add client data and QR code to the response data
            $responseData[] = [
                'client_data' => $data,
                'qr_code' => strval($qr),
            ];
        }

        // Return the response with all client card data and corresponding QR codes
        return response()->json($responseData, 200);

    } catch (\Exception $e) {
        \Log::error('Failed to retrieve client cards: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while retrieving client cards.'], 500);
    }
}

   
public function generatePdf($id)
{
    try {
        $card = SanaaCard::findOrFail($id);
        $clientId = $card->client_id;
        $client = Client::findOrFail($clientId);

        // Check if the background images are accessible
       
        // Generate HTML
        $frontHtml = view('cards.front', compact('client', 'card'))->render();
        $backHtml = view('cards.back', compact('client', 'card'))->render();

        // Combine with a page break, if necessary
        $combinedHtml = $frontHtml . $backHtml;

        // Define custom paper size
        $customPaper = array(0, 0, 317.74, 199.8);

        // Generate PDF
        $pdf = PDF::loadHTML($combinedHtml)
            ->setPaper($customPaper)
            ->setWarnings(false);

        return $pdf->stream('card_' . $client->name . '.pdf');

    } catch (\Exception $e) {
        \Log::error('Failed to generate PDF: ' . $e->getMessage());
        return response()->json(['error' => 'An error occurred while generating the PDF.'], 500);
    }
}



public function generatePdf22ddd($id)
{
    try {
        // Attempt to retrieve the card and associated client ID
        $card = SanaaCard::findOrFail($id);
        $clientId = $card->client_id;

        // Retrieve the client using the client ID from the card
        $client = Client::findOrFail($clientId);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        // If the card or client is not found, return a 404 response with an error message
        return response()->json(['error' => 'Card or Client not found'], 404);
    }

    try {
        // Define custom paper size (width and height in points)
        $customPaper = [0, 0, 317.74, 199.8];

        // Generate the PDF using the Blade template
        $pdf = PDF::loadView('admin-views.clients.front', compact('client', 'card'))
                  ->setPaper($customPaper)
                  ->setWarnings(false);

        // Return the generated PDF for download with a custom filename
        return $pdf->download('card_' . $client->id . '.pdf');
    } catch (\Exception $e) {
        // Catch any other exceptions during PDF generation and return a 500 response
        return response()->json(['error' => 'Failed to generate PDF: ' . $e->getMessage()], 500);
    }
}


    
    
    
    public function printCard($clientId)
    {
        $client = Client::findOrFail($clientId);
        $card = SanaaCard::where('client_id', $clientId)->firstOrFail();

        // Load the background images (assuming you have separate images for front and back)
        $frontBackgroundImage = 'https://lendsup.sanaa.co/storage/app/public/business/front.png'; // Replace with the actual front image URL
        $backBackgroundImage = 'https://lendsup.sanaa.co/storage/app/public/business/back.png';

        // Pass the variables to the views using compact
        $frontHtml = view('admin-views.clients.front', compact('client', 'card', 'frontBackgroundImage'))->render();
        $backHtml = view('admin-views.clients.back', compact('client', 'card', 'backBackgroundImage'))->render();

        // Apply background images to the HTML content
        // $frontHtmlWithBg = '<div style="background-image: url(\'' . $frontBackgroundImage . '\'); background-size: cover; height: 100%; width: 100%;">' . $frontHtml . '</div>';
        // $backHtmlWithBg = '<div style="background-image: url(\'' . $backBackgroundImage . '\'); background-size: cover; height: 100%; width: 100%;">' . $backHtml . '</div>';

        $combinedHtml = $frontHtmlWithBg . '<div style="page-break-after: always;"></div>' . $backHtmlWithBg;

        $customPaper = [0, 0, 317.74, 199.8];
        $pdf = PDF::loadHTML($combinedHtml)->setPaper($customPaper)->setWarnings(false);

        return $pdf->download('card_' . $client->id . '.pdf');
    }

    
    public function viewCard($clientId): View
    {
        $client = Client::findOrFail($clientId);
        $card = SanaaCard::where('client_id', $clientId)->firstOrFail();
        return view('cards.view', compact('client', 'card'));
    }

    public function printCard23($clientId): Response
    {
        $client = Client::findOrFail($clientId);
        $card = SanaaCard::where('client_id', $clientId)->firstOrFail();

        $frontHtml = ViewFacade::make('cards.print-front', compact('client', 'card'))->render();
        $backHtml = ViewFacade::make('cards.print-back', compact('client', 'card'))->render();
        $combinedHtml = $frontHtml . '<div style="page-break-after: always;"></div>' . $backHtml;

        $customPaper = array(0, 0, 317.74, 199.8);
        $pdf = Pdf::loadHTML($combinedHtml)
            ->setPaper($customPaper)
            ->setWarnings(false);

        return $pdf->download('card_' . $client->id . '.pdf');
    }
    
        public function printCard222($id)
        {
            $client = Client::findOrFail($id);
            $card = SanaaCard::where('client_id', $id)->firstOrFail();
        
            // Generate PDF using the Blade template
            $customPaper = [0, 0, 317.74, 199.8]; // Width and height in points
            $pdf = PDF::loadView('admin-views.clients.front', compact('client', 'card'))
                      ->setPaper($customPaper)
                      ->setWarnings(false);
        
            // Optionally, you can set the PDF filename
            return $pdf->download('card_' . $client->id . '.pdf');
        }

}