<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Models\UserLoan;
use App\Models\LoanPaymentInstallment;
use Carbon\Carbon;

use App\Models\LoanPayment;
use Illuminate\Support\Str;

class CreateUserLoansForClientsWithBalance extends Command
{
    protected $signature = 'loans:create-for-clients-with-balance';
    protected $description = 'Create user loans for clients with balance greater than 0';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Fetch clients with a balance greater than 0
        // $clients = Client::where('credit_balance', '>', 0)->get();
        $clients = Client::where('credit_balance', '>', 0)
                 ->where('added_by', '!=', 39) // Exclude clients added by user 39
                 ->get();

        foreach ($clients as $client) {
            // Create a user loan for each client
            // (`user_id`, `status`, `amount`, `per_installment`, `installment_interval`, `total_installment`, `given_installment`, `paid_amount`, `final_amount`, `processing_fee`, `updated_at`, `created_at`) 
            
           $loan = UserLoan::create([     
                'user_id'       => $client->added_by,
                'client_id'     => $client->id, 
                'plan_id' => 8, 
                'trx'               => $this->generateUniqueTrx(), // Corrected syntax
                'status'        => 1,
                'amount'        => $client->credit_balance,
                'per_installment' => 10000, // Example value, set accordingly
                'installment_interval' => 30, // Example value, set accordingly
                'total_installment' => 30, // Example value, set accordingly
                'given_installment' => 0,
                'paid_amount'       => 0,
                'final_amount'      => $client->credit_balance, 
                'processing_fee'    => 0,
                'created_at'        => Carbon::now(),
                'updated_at'        => Carbon::now(),
                
                 
        
            ]);

            // Create payment installments for this loan
            $this->createPaymentInstallments($loan);

            $this->info("Created loan and installments for client: {$client->name}");
        }

        $this->info('Loans and installments have been created for all clients with balance greater than 0.');
    }
    
    
       
    function generateUniqueTrx()
        {
            do {
                // Generate a random transaction ID
                $trx = 'TRX' . Str::random(8);
                
                // Check if the trx already exists in the UserLoan table
                $exists = UserLoan::where('trx', $trx)->exists();
            } while ($exists);
        
            return $trx;
        }

    protected function createPaymentInstallments(UserLoan $loan)
    {
        $installmentAmount = $loan->per_installment;
        $installmentInterval = $loan->installment_interval;
        $totalInstallments = $loan->total_installment;

        for ($i = 1; $i <= $totalInstallments; $i++) {
            // Calculate the base installment date
            $installmentDate = now()->addDays($i * $installmentInterval);

            // Adjust the time to 11 AM to ensure it falls within the business day
            $installmentDate->setTime(11, 0);

            // Save the installment
            LoanPaymentInstallment::create([
                'loan_id' => $loan->id,
                'agent_id' => $loan->user_id,
                'client_id' => $loan->client_id,
                'install_amount' => $installmentAmount,
                'date' => $installmentDate,
                'status' => 'pending', // Initially set status as 'pending'
            ]);
        }
    }
}
