<?php

namespace App\Http\Controllers\Merchant;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private User        $user,
        private Transaction $transaction
    )
    {
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function transaction(Request $request): Factory|View|Application
    {
        $transactionTypes = $request->has('trx_type') ? $request['trx_type'] : 'all';
        $queryParams = [];
        $search = $request['search'];

        $key = explode(' ', $request['search']);

        $users = $this->user->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('id', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%")
                    ->orWhere('f_name', 'like', "%{$value}%")
                    ->orWhere('l_name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%");
            }
        })->get()->pluck('id')->toArray();

        $transactions = $this->transaction
            ->when($request->has('search'), function ($q) use ($key, $users) {
                foreach ($key as $value) {
                    $q->where('transaction_id', 'like', "%{$value}%");
                }
            })
            ->when($request['trx_type'] != 'all', function ($query) use ($request) {
                if ($request['trx_type'] == 'debit') {
                    return $query->where('debit', '!=', 0);
                } else {
                    return $query->where('credit', '!=', 0);
                }
            });

        $queryParams = ['search' => $search, 'trx_type' => $transactionTypes];
        $transactions = $transactions
            ->where('user_id', auth()->user()->id)
            ->latest()->paginate(Helpers::pagination_limit())->appends($queryParams);

        return view('merchant-views.transaction.list', compact('transactions', 'search', 'transactionTypes'));
    }
}
