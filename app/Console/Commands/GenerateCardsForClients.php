<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client; // Ensure Client model exists and matches your table
use App\Models\SanaaCard; // Ensure SanaaCard model exists and matches your table
use Carbon\Carbon;
use Illuminate\Support\Str;

class GenerateCardsForClients extends Command
{
    protected $signature = 'generate:cards';

    protected $description = 'Generate Sanaa cards for all clients who do not yet have cards';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Fetch all clients who do not have a card associated with them
        $clientsWithoutCards = Client::whereNotIn('id', SanaaCard::pluck('client_id'))->get();

        if ($clientsWithoutCards->isEmpty()) {
            $this->info('All clients already have cards.');
            return;
        }

        foreach ($clientsWithoutCards as $client) {
            // Generate card details
            $pan = $this->generatePan(); // Placeholder method to generate PAN
            $cvv = $this->generateCvv();
            $issueDate = Carbon::now();
            $expiryDate = $issueDate->copy()->addYears(3); // Card valid for 3 years
            $pinCode = $this->generateNumericPin();//Str::random(4); // Random 4-digit pin code

            // Create the card for the client
            SanaaCard::create([
                'client_id' => $client->id,
                'pan' => $pan,
                'cvv' => $cvv,
                'card_status' => 'active',
                'is_printed' => 0, // Not printed yet
                'card_type' => 'physical', // or 'physical' based on your criteria
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
                'balance' => 0.0,
                'currency' => 'UGX',
                'pin_code' => $pinCode,
                'iin' => '123456', // Your institution identification number
                'nfc_enabled' => 0, // Default to NFC disabled, can be updated later
            ]);

            $this->info("Card generated for client: {$client->name} (ID: {$client->id})");
        }

        $this->info('Card generation completed for all clients without cards.');
    }

    private function generatePan()
    {
        // Generate a random 16-digit number for the card PAN
        return '4000' . mt_rand(100000000000, 999999999999);
    }

    private function generateCvv()
    {
        // Generate a random 3-digit CVV
        return str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);
    }
    
     private function generateNumericPin()
    {
        // Generate a random 4-digit numeric PIN
        return str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }
}
