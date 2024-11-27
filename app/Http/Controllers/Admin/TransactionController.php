<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Exceptions\TransactionFailedException;
use App\Http\Controllers\Controller;
use App\Models\EMoney;
use App\Models\RequestMoney;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalMethod;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function App\CentralLogics\translate;
use App\Models\PaymentTransaction;
use App\Models\Client;


class TransactionController extends Controller
{
    public function __construct(
        private EMoney $eMoney,
        private RequestMoney $requestMoney,
        private Transaction $transaction,
        private User $user,
        private WithdrawalMethod $withdrawalMethod
    ){}


    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function index2(Request $request): View|Factory|Application
    {
        $transactionType = $request->has('trx_type') ? $request['trx_type'] : 'all';
        $search = $request['search'];
        $queryParam = [];
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
                    $q->orWhereIn('from_user_id', $users)
                        ->orWhereIn('to_user_id', $users)
                        ->orWhere('transaction_id', 'like', "%{$value}%")
                        ->orWhere('transaction_type', 'like', "%{$value}%");
                }
            })
            ->when($request['trx_type'] != 'all', function ($query) use ($request) {
                if ($request['trx_type'] == 'debit') {
                    return $query->where('debit', '!=', 0);
                } else {
                    return $query->where('credit', '!=', 0);
                }
            });

        $queryParam = ['search' => $search, 'trx_type' => $transactionType];

        $transactions = $transactions->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
        return view('admin-views.transaction.index', compact('transactions', 'search',  'transactionType'));
    }
    
    public function index(Request $request): View|Factory|Application
{
    $transactionType = $request->has('trx_type') ? $request['trx_type'] : 'all';
    $search = $request['search'];
    $queryParam = [];
    $key = explode(' ', $request['search']);

    $clients = Client::where(function ($q) use ($key) {
        foreach ($key as $value) {
            $q->orWhere('id', 'like', "%{$value}%")
                ->orWhere('phone', 'like', "%{$value}%")
                ->orWhere('name', 'like', "%{$value}%")
                ->orWhere('nin', 'like', "%{$value}%")
                ->orWhere('email', 'like', "%{$value}%");
        }
    })->get()->pluck('id')->toArray();

    $transactions = PaymentTransaction::when($request->has('search'), function ($q) use ($key, $clients) {
        foreach ($key as $value) {
            $q->orWhereIn('client_id', $clients)
                ->orWhere('transaction_id', 'like', "%{$value}%")
                ->orWhere('payment_type', 'like', "%{$value}%");
        }
    })
    ->when($request['trx_type'] != 'all', function ($query) use ($request) {
        if ($request['trx_type'] == 'debit') {
            return $query->where('payment_type', 'savings');
        } else {
            return $query->where('payment_type', 'loan');
        }
    });

    $queryParam = ['search' => $search, 'trx_type' => $transactionType];

    $transactions = $transactions->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);

    return view('admin-views.transaction.index', compact('transactions', 'search', 'transactionType'));
}



    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function requestMoney(Request $request): Factory|View|Application
    {
        $queryParam = [];
        $search = $request['search'];
        if ($request->has('search')) {
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

            $requestMoney = $this->requestMoney->where(function ($q) use ($key, $users) {
                foreach ($key as $value) {
                    $q->orWhereIn('from_user_id', $users)
                        ->orWhere('to_user_id', $users)
                        ->orWhere('type', 'like', "%{$value}%")
                        ->orWhere('amount', 'like', "%{$value}%")
                        ->orWhere('note', 'like', "%{$value}%");
                }
            });
            $queryParam = ['search' => $request['search']];
        } else {
            $requestMoney = $this->requestMoney;
        }

        if($request->has('withdrawal_method') && $request->withdrawal_method != 'all') {
            $requestMoney = $requestMoney->where('withdrawal_method_id', $request->withdrawal_method);
        }
        $withdrawalMethods = $this->withdrawalMethod->get();

        $requestMoney = $requestMoney->with('withdrawal_method')->where('to_user_id', Helpers::get_admin_id())->latest()->paginate(Helpers::pagination_limit())->appends($queryParam);
        return View('admin-views.transaction.request_money_list', compact('requestMoney', 'search', 'withdrawalMethods'));
    }

    /**
     * @param Request $request
     * @param $slug
     * @return RedirectResponse
     * @throws \Exception
     */
    public function requestMoneyStatusChange(Request $request, $slug): RedirectResponse
    {
        $requestMoney = $this->requestMoney->find($request->id);

        if($requestMoney->to_user_id != $request->user()->id) {
            Toastr::error(translate('unauthorized request'));
            return back();
        }

        if (strtolower($slug) == 'deny') {
            try {
                $requestMoney->type = 'denied';
                $requestMoney->save();
            } catch (\Exception $e) {
                Toastr::error(translate('Something went wrong'));
                return back();
            }

            Helpers::send_transaction_notification($requestMoney->from_user_id, $requestMoney->amount, 'denied_money');
            Helpers::send_transaction_notification($requestMoney->to_user_id, $requestMoney->amount, 'denied_money');

            Toastr::success(translate('Successfully changed the status'));
            return back();

        } elseif (strtolower($slug) == 'approve') {

            DB::beginTransaction();
            $data = [];
            $data['from_user_id'] = $requestMoney->to_user_id;
            $data['to_user_id'] = $requestMoney->from_user_id;

            try {
                $sendmoney_charge =0;   //since agent transaction has no change
                $data['user_id'] = $data['from_user_id'];
                $data['type'] = 'debit';
                $data['transaction_type'] = SEND_MONEY;
                $data['ref_trans_id'] = null;
                $data['amount'] = $requestMoney->amount + $sendmoney_charge;

                if (strtolower($data['type']) == 'debit' && $this->eMoney->where('user_id', $data['from_user_id'])->first()->current_balance < $data['amount']) {
                    Toastr::error(translate('Insufficient Balance'));
                    return back();
                }

                $customer_transaction = Helpers::make_transaction($data);

                Helpers::send_transaction_notification($data['user_id'], $data['amount'], $data['transaction_type']);

                if ($customer_transaction == null) {
                    throw new TransactionFailedException('Transaction from sender is failed');
                }

                //customer(receiver) transaction
                $data['user_id'] = $data['to_user_id'];
                $data['type'] = 'credit';
                $data['transaction_type'] = RECEIVED_MONEY;
                $data['ref_trans_id'] = $customer_transaction;
                $data['amount'] = $requestMoney->amount;
                $agent_transaction = Helpers::make_transaction($data);

                Helpers::send_transaction_notification($data['user_id'], $data['amount'], $data['transaction_type']);

                if ($agent_transaction == null) {
                    throw new TransactionFailedException('Transaction to receiver is failed');
                }

                $requestMoney->type = 'approved';
                $requestMoney->save();

                DB::commit();

            } catch (TransactionFailedException $e) {
                DB::rollBack();
                Toastr::error(translate('Status change failed'));
                return back();
            }

            Toastr::success(translate('Successfully changed the status'));
            return back();

        } else {
            Toastr::error(translate('Status change failed'));
            return back();
        }
    }
}
