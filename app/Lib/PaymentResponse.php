<?php

namespace App\Lib;

use App\Traits\TransactionTrait;
use App\CentralLogics\Helpers;

class PaymentResponse
{
    public static function success($data)
    {
        $transaction_id = TransactionTrait::add_money_transaction(Helpers::get_admin_id(), $data['payer_id'], $data['payment_amount']);
        Helpers::add_fund($data['payer_id'], $data['payment_amount'], $data['payment_method'], null, 'success');
        /** Update Transaction limits data  */
        Helpers::add_money_transaction_limit_update($data['payer_id'], $data['payment_amount']);
    }

    public static function fail($data)
    {
        Helpers::add_fund($data['payer_id'], $data['payment_amount'], $data['payment_method'], null, 'failed');
    }

}
