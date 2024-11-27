@php($currency=\App\Models\BusinessSetting::where(['key'=>'currency'])->first()->value)

    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>
        {{translate('Payment')}}
    </title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="favicon.icon">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/vendor.min.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/vendor/icon-set/style.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/payment.css')}}">
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/theme.minc619.css?v=1.0')}}">
    <script
        src="{{asset('public/assets/admin/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js')}}"></script>
    <link rel="stylesheet" href="{{asset('public/assets/admin/css/toastr.css')}}">

    <script src="https://polyfill.io/v3/polyfill.min.js?version=3.52.1&features=fetch"></script>
    <script src="https://js.stripe.com/v3/"></script>

    <link rel="stylesheet" href="{{asset('public/assets/admin/css/bootstrap.css')}}">

</head>
<body class="toolbar-enabled">
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="loading">
                <div class="loader-css">
                    <img width="200" src="{{asset('public/assets/admin/img/loader.gif')}}"
                         alt="{{translate('loader')}}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5 mb-2 mb-md-4 d-none">
    <div class="row">
        <div class="col-md-12 mb-5 pt-5">
            <center class="">
                <h1>{{translate('Payment method')}}</h1>
            </center>
        </div>
        <section class="col-lg-12 checkout_details">
            <div class="checkout_details mt-3">
                <div class="row">

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('ssl_commerz_payment'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card" onclick="$('#ssl-form').submit()">
                                <div class="card-body h-70">
                                    <form action="{{ url('/pay-ssl') }}" method="POST" class="needs-validation"
                                          id="ssl-form">
                                        <input type="hidden" value="{{ csrf_token() }}" name="_token"/>
                                        <button class="btn btn-block click-if-alone" type="submit" id="sslcomz-button">
                                            <img width="100"
                                                 src="{{asset('public/assets/admin/img/sslcomz.png')}}"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('razor_pay'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                    @php($config=\App\CentralLogics\Helpers::get_business_settings('razor_pay'))
                                        <?php
                                        $customer = \App\Models\User::find(session('user_id'));
                                        $amount = session('amount');
                                        $name = \App\CentralLogics\Helpers::get_business_settings('title');
                                        $logo = asset('storage/app/public/business') . '/' . \App\CentralLogics\Helpers::get_business_settings('logo');
                                        $f_name = $customer->f_name;
                                        $email = $customer->email ?? '';
                                        ?>
                                    <form
                                        action="{{ route('payment-razor', ['user_id'=>session('user_id'), 'amount'=>session('amount')]) }}"
                                        method="POST">
                                        @csrf
                                        <script src="https://checkout.razorpay.com/v1/checkout.js"
                                                data-key="{{ Config::get('razor.razor_key') }}"
                                                data-amount="{{ $amount*100 }}"
                                                data-buttontext="Pay {{ $amount }} {{\App\CentralLogics\Helpers::currency_code()}}"
                                                data-name="{{ $name }}"
                                                data-image="{{ asset('storage/app/public/restaurant/'. $logo) }}"
                                                data-prefill.name="{{ $f_name }}"
                                                data-prefill.email="{{ $email }}"
                                                data-theme.color="#ff7529">
                                        </script>
                                    </form>
                                    <button class="btn btn-block click-if-alone" type="button" id="razorpay-button"
                                            onclick="{{\App\CentralLogics\Helpers::currency_code()=='INR'?"$('.razorpay-payment-button').click()":"toastr.error('Your currency is not supported by Razor Pay.')"}}">
                                        <img width="100"
                                             src="{{asset('public/assets/admin/img/razorpay.png')}}"
                                             alt="{{translate('razor pay')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif


                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paypal'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                    <form class="needs-validation" method="POST" id="payment-form"
                                          action="{{route('pay-paypal')}}">
                                        {{ csrf_field() }}
                                        <button class="btn btn-block click-if-alone" type="submit" id="paypal-button">
                                            <img width="100"
                                                 src="{{asset('public/assets/admin/img/paypal.png')}}"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif


                    @php($config=\App\CentralLogics\Helpers::get_business_settings('stripe'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                    @php($config=\App\CentralLogics\Helpers::get_business_settings('stripe'))
                                    <button class="btn btn-block click-if-alone" type="button" id="checkout-button">
                                        <i class="czi-card"></i> Credit / Debit card ( Stripe )
                                    </button>
                                    <script type="text/javascript">
                                        // Create an instance of the Stripe object with your publishable API key
                                        var stripe = Stripe('{{$config['published_key']}}');
                                        var checkoutButton = document.getElementById("checkout-button");
                                        checkoutButton.addEventListener("click", function () {
                                            fetch("{{route('pay-stripe')}}", {
                                                method: "GET",
                                            }).then(function (response) {
                                                console.log(response)
                                                return response.text();
                                            }).then(function (session) {
                                                console.log(JSON.parse(session).id)
                                                return stripe.redirectToCheckout({sessionId: JSON.parse(session).id});
                                            }).then(function (result) {
                                                if (result.error) {
                                                    alert(result.error.message);
                                                }
                                            }).catch(function (error) {
                                                console.error("Error:", error);
                                            });
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    @endif


                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paystack'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                        <?php
                                        $customer = \App\Models\User::find(session('user_id'));
                                        $email = $customer->email ?? EXAMPLE_MAIL;
                                        $amount = session('amount');
                                        $currency = \App\CentralLogics\Helpers::get_business_settings('currency') ?? '';
                                        ?>
                                    <form method="POST" action="{{ route('paystack-pay') }}" accept-charset="UTF-8"
                                          class="form-horizontal"
                                          role="form">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-8 col-md-offset-2">
                                                <input type="hidden" name="email"
                                                       value="{{ $email }}">
                                                <input type="hidden" name="amount"
                                                       value="{{$amount*100}}">
                                                <input type="hidden" name="quantity" value="1">
                                                <input type="hidden" name="currency"
                                                       value="{{ $currency }}">
                                                <input type="hidden" name="metadata"
                                                       value="{{ json_encode($array = ['key_name' => 'value',]) }}">
                                                <input type="hidden" name="reference"
                                                       value="{{ Paystack::genTranxRef() }}">
                                                <p>
                                                    <button class="paystack-payment-button click-if-alone d-none"
                                                            type="submit"
                                                            value="Pay Now!" id="paystack-payment-button"></button>
                                                </p>
                                            </div>
                                        </div>
                                    </form>
                                    <button class="btn btn-block" type="button"
                                            onclick="$('.paystack-payment-button').click()">
                                        <img width="100"
                                             src="{{asset('public/assets/admin/img/paystack.png')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('senang_pay'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                    @php($config=\App\CentralLogics\Helpers::get_business_settings('senang_pay'))

                                        <?php
                                        $customer = \App\Models\User::find(session('user_id'));
                                        ?>
                                    @php($secretkey = $config['secret_key'])
                                    @php($data = new \stdClass())
                                    @php($data->merchantId = $config['merchant_id'])
                                    @php($data->amount = session('amount'))
                                    @php($data->name = $customer->f_name)
                                    @php($data->email = $customer->email)
                                    @php($data->phone = $customer->phone)
                                    @php($data->hashed_string = md5($secretkey . urldecode($data->amount) ))

                                    <form name="order" method="post"
                                          action="https://{{env('APP_MODE')=='live'?'app.senangpay.my':'sandbox.senangpay.my'}}/payment/{{$config['merchant_id']}}">
                                        <input type="hidden" name="amount" value="{{$data->amount}}">
                                        <input type="hidden" name="name" value="{{$data->name}}">
                                        <input type="hidden" name="email" value="{{$data->email}}">
                                        <input type="hidden" name="phone" value="{{$data->phone}}">
                                        <input type="hidden" name="hash" value="{{$data->hashed_string}}">
                                    </form>

                                    <button class="btn btn-block click-if-alone" type="button" id="senangpay-button"
                                            onclick="{{\App\CentralLogics\Helpers::currency_code()=='MYR'?"document.order.submit()":"toastr.error('Your currency is not supported by Senang Pay.')"}}">
                                        <img width="100"
                                             src="{{asset('public/assets/admin/img/senangpay.png')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('internal_point'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                    <button class="btn btn-block" type="button" data-toggle="modal"
                                            data-target="#exampleModal">
                                        <i class="czi-card"></i> Wallet Point
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                             aria-labelledby="exampleModalLabel"
                             aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h3 class="modal-title" id="exampleModalLabel">Payment by Wallet Point</h3>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <hr>
                                        @php($value=\App\Models\BusinessSetting::where(['key'=>'point_per_currency'])->first()->value)
                                        @php($order=\App\Models\Order::find(session('order_id')))
                                        @php($point=\App\User::where(['id'=>$order['user_id']])->first()->point)
                                        <span>Order Amount: {{$order['order_amount']}} {{\App\CentralLogics\Helpers::currency_symbol()}}</span><br>
                                        <span>Order Amount in Wallet Point : {{$value*$order['order_amount']}} Points</span><br>
                                        <span>Your Available Points : {{$point}} Points</span><br>
                                        <hr>
                                        <center>
                                            @if(($value*$order['order_amount'])<=$point)
                                                <label class="badge badge-soft-success">You have sufficient balance to
                                                    proceed!</label>
                                            @else
                                                <label class="badge badge-soft-danger">Your balance is
                                                    insufficient!</label>
                                            @endif
                                        </center>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                        </button>
                                        @if(($value*$order['order_amount'])<=$point)
                                            <form action="{{route('internal-point-pay')}}" method="POST">
                                                @csrf
                                                <input class="d-none" name="order_id" value="{{$order['id']}}">
                                                <button type="submit" class="btn btn-primary">Proceed</button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-primary">Sorry! Next time.</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('bkash'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                    <button class="btn btn-block click-if-alone" id="bKash_button"
                                            onclick="BkashPayment()">
                                        <img width="100" src="{{asset('public/assets/admin/img/bkash.png')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('paymob'))
                    @if(isset($config) && $config['status'])
                        @php($amount=100)
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body h-70">
                                    <form class="needs-validation click-if-alone" method="POST" id="payment-form-paymob"
                                          action="{{route('paymob-credit')}}">
                                        {{ csrf_field() }}
                                        <button class="btn btn-block" type="submit" id="paymob-button">
                                            <img width="150" src="{{asset('public/assets/admin/img/paymob.png')}}"
                                                 alt="{{translate('paymob')}}"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('mercadopago'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body pt-2 h-70">
                                    <button class="btn btn-block click-if-alone" id="mercadopago-button"
                                            onclick="location.href='{{route('mercadopago.index')}}'">
                                        <img width="150"
                                             src="{{asset('public/assets/admin/img/MercadoPago_(Horizontal).svg')}}"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    @php($config=\App\CentralLogics\Helpers::get_business_settings('flutterwave'))
                    @if(isset($config) && $config['status'])
                        <div class="col-md-6 mb-4 cursor-pointer">
                            <div class="card">
                                <div class="card-body pt-2 h-70">
                                    <form method="POST" action="{{ route('flutterwave_pay') }}">
                                        {{ csrf_field() }}

                                        <button class="btn btn-block click-if-alone" type="submit"
                                                id="fluterwave-button">
                                            <img width="200"
                                                 src="{{asset('public/assets/admin/img/fluterwave.png')}}"/>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

<script src="{{asset('public/assets/admin/js/custom.js')}}"></script>
<script src="{{asset('public/assets/admin/js/vendor.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/theme.min.js')}}"></script>
<script src="{{asset('public/assets/admin/js/sweet_alert.j')}}s"></script>
<script src="{{asset('public/assets/admin/js/toastr.js')}}"></script>
<script src="{{asset('public/assets/admin/js/bootstrap.min.js')}}"></script>

{!! Toastr::message() !!}

<script>
    $(document).ready(function () {
        let payment_method = "{{$payment_method}}"

        if (payment_method === 'ssl_commerz_payment') {
            $('#sslcomz-button').click();
        } else if (payment_method === 'razor_pay') {
            $('#razorpay-button').click();
        } else if (payment_method === 'paypal') {
            $('#paypal-button').click();
        } else if (payment_method === 'stripe') {
            $('#checkout-button').click();
        } else if (payment_method === 'senang_pay') {
            $('#senangpay-button').click();
        } else if (payment_method === 'paystack') {
            $('#paystack-payment-button').click();
        } else if (payment_method === 'bkash') {
            $('#bKash_button').click();
        } else if (payment_method === 'paymob') {
            $('#paymob-button').click();
        } else if (payment_method === 'flutterwave') {
            $('#fluterwave-button').click();
        } else if (payment_method === 'mercadopago') {
            $('#mercadopago-button').click();
        }
    });
</script>

<script>
    setTimeout(function () {
        $('.stripe-button-el').hide();
        $('.razorpay-payment-button').hide();
    }, 10)
</script>

@php($config=\App\CentralLogics\Helpers::get_business_settings('bkash'))
@if(isset($config) && $config['status'])
    <script type="text/javascript">
        function BkashPayment() {
            location.href = '{!! route('bkash.make-payment',['order_amount'=>session('amount'),'customer_id'=>session('user_id'),'callback'=>session('callback')]) !!}'
        }
    </script>
@endif

<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
    const mp = new MercadoPago('YOUR_PUBLIC_KEY');
    const cardForm = mp.cardForm({
        amount: "{{$amount??0}}",
        autoMount: true,
        form: {
            id: "form-checkout",
            cardholderName: {
                id: "form-checkout__cardholderName",
                placeholder: "{{translate('card_holder_name')}}",
            },
            cardholderEmail: {
                id: "form-checkout__cardholderEmail",
                placeholder: "{{translate('card_holder_email')}}",
            },
            cardNumber: {
                id: "form-checkout__cardNumber",
                placeholder: "{{translate('card_number')}}",
            },
            cardExpirationMonth: {
                id: "form-checkout__cardExpirationMonth",
                placeholder: "{{translate('card_expire_month')}}",
            },
            cardExpirationYear: {
                id: "form-checkout__cardExpirationYear",
                placeholder: "{{translate('card_expire_year')}}",
            },
            securityCode: {
                id: "form-checkout__securityCode",
                placeholder: "{{translate('security_code')}}",
            },
            installments: {
                id: "form-checkout__installments",
                placeholder: "{{translate('dues')}}",
            },
            identificationType: {
                id: "form-checkout__identificationType",
                placeholder: "{{translate('document_type')}}",
            },
            identificationNumber: {
                id: "form-checkout__identificationNumber",
                placeholder: "{{translate('document_number')}}",
            },
            issuer: {
                id: "form-checkout__issuer",
                placeholder: "{{translate('issuing_bank')}}",
            },
        },
        callbacks: {
            onFormMounted: error => {
                if (error) return console.warn("Form Mounted handling error: ", error);
                console.log("Form mounted");
            },
            onSubmit: event => {
                event.preventDefault();

                const {
                    paymentMethodId: payment_method_id,
                    issuerId: issuer_id,
                    cardholderEmail: email,
                    amount,
                    token,
                    installments,
                    identificationNumber,
                    identificationType,
                } = cardForm.getCardFormData();

                fetch("{{route('mercadopago.make_payment')}}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        token,
                        issuer_id,
                        payment_method_id,
                        transaction_amount: Number(amount),
                        installments: Number(installments),
                        description: "Descripci贸n del producto",
                        payer: {
                            email,
                            identification: {
                                type: identificationType,
                                number: identificationNumber,
                            },
                        },
                    }),
                });
            },
            onFetching: (resource) => {
                console.log("Fetching resource: ", resource);
                const progressBar = document.querySelector(".progress-bar");
                progressBar.removeAttribute("value");

                return () => {
                    progressBar.setAttribute("value", "0");
                };
            },
        },
    });
</script>

<script>
    function click_if_alone() {
        let total = $('.checkout_details .click-if-alone').length;
        if (Number.parseInt(total) < 2) {
            $('.click-if-alone').click()
            $('.checkout_details').html('<div class="text-center"><h1>{{translate('Redirecting_to_the_payment_page')}}......</h1></div>');
        }
    }

    @if(!$errors->any())
    click_if_alone();
    @endif
</script>
{{-- end --}}
</body>
</html>
