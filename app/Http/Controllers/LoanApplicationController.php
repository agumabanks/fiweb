<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use App\Models\LoanOffer;
use Illuminate\Http\Request;

use App\Models\LoanPaymentInstallment;

class LoanApplicationController extends Controller
{
    public function index()
    {
        $loanApplications = LoanApplication::with(['user', 'loanOffer'])->get();
        return response()->json($loanApplications);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'loan_offer_id' => 'required|exists:loan_offers,id',
            'amount' => 'required|numeric|min:0.01',
            'term' => 'required|integer|min:1',
        ]);

        $loanOffer = LoanOffer::find($request->loan_offer_id);

        $monthlyPayment = $this->calculateMonthlyPayment($request->amount, $request->term, $loanOffer->interest_rate);
        $totalPayment = $monthlyPayment * $request->term;

        $loanApplication = LoanApplication::create([
            'user_id' => $request->user_id,
            'loan_offer_id' => $request->loan_offer_id,
            'amount' => $request->amount,
            'term' => $request->term,
            'status' => 'Pending',
            'interest_rate' => $loanOffer->interest_rate,
            'monthly_payment' => $monthlyPayment,
            'total_payment' => $totalPayment,
        ]);
        
        // Generate payment installments
        $this->createPaymentInstallments($loan);

        return response()->json($loanApplication, 201);
    }
    
    
    protected function createPaymentInstallments(UserLoan $loan)
    {
        $installmentAmount = $loan->per_installment;
        $installmentInterval = $loan->installment_interval;
        $totalInstallments = $loan->total_installment;
    
        for ($i = 1; $i <= $totalInstallments; $i++) {
            $installmentDate = now()->addDays($installmentInterval * $i);
    
            LoanPaymentInstallment::create([
                'loan_id' => $loan->id,
                'agent_id' => $loan->agent_id,
                'client_id' => $loan->client_id,
                'install_amount' => $installmentAmount,
                'date' => $installmentDate,
                'status' => 'pending', // Initially set status as 'pending'
            ]);
        }
        
    }

    public function show($id)
    {
        $loanApplication = LoanApplication::with(['user', 'loanOffer'])->findOrFail($id);
        return response()->json($loanApplication);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'sometimes|required|in:Pending,Approved,Rejected,Disbursed,Completed',
        ]);

        $loanApplication = LoanApplication::findOrFail($id);
        $loanApplication->update($request->only('status'));

        return response()->json($loanApplication);
    }

    public function destroy($id)
    {
        $loanApplication = LoanApplication::findOrFail($id);
        $loanApplication->delete();
        return response()->json(['message' => 'Loan application deleted successfully']);
    }

    private function calculateMonthlyPayment($amount, $term, $interestRate)
    {
        $monthlyRate = $interestRate / 100 / 12;
        $numerator = $monthlyRate * pow(1 + $monthlyRate, $term);
        $denominator = pow(1 + $monthlyRate, $term) - 1;

        return $amount * ($numerator / $denominator);
    }
}
