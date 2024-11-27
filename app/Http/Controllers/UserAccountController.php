<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// class UserAccountController extends Controller
// {
//     //
// }

class UserAccountController extends Controller
{
    public function transferFunds(Request $request)
    {
        $request->validate([
            'from_user_id' => 'required|integer',
            'from_account_type' => 'required|string',
            'to_account_type' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $fromUser = User::find($request->from_user_id);
        if (!$fromUser) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $fromAccount = $fromUser->accounts()->where('account_type', $request->from_account_type)->first();
        $toAccount = $fromUser->accounts()->where('account_type', $request->to_account_type)->first();

        if (!$fromAccount || !$toAccount) {
            return response()->json(['error' => 'Invalid account types'], 400);
        }

        if ($fromAccount->balance < $request->amount) {
            return response()->json(['error' => 'Insufficient funds'], 400);
        }

        DB::transaction(function () use ($fromAccount, $toAccount, $request) {
            $fromAccount->balance -= $request->amount;
            $fromAccount->save();

            $toAccount->balance += $request->amount;
            $toAccount->save();

            Transaction::create([
                'user_id' => $fromAccount->user_id,
                'transaction_type' => 'Transfer',
                'amount' => $request->amount,
                'status' => 'Completed',
            ]);
        });

        return response()->json(['message' => 'Transfer successful']);
    }
}
