<?php

use App\Lib\PaymentResponse;

if (!function_exists('digital_payment_success')) {
    function digital_payment_success($data)
    {
        PaymentResponse::success($data);
    }
}

if (!function_exists('digital_payment_fail')) {
    function digital_payment_fail($data)
    {
        PaymentResponse::fail($data);
    }
}

