#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    use PayPal\Rest\ApiContext;
    use PayPal\Auth\OAuthTokenCredential;
    use PayPal\Api\Address;
    use PayPal\Api\Amount;
    use PayPal\Api\Payer;
    use PayPal\Api\Payment;
    use PayPal\Api\FundingInstrument;
    use PayPal\Api\RedirectUrls;
    use PayPal\Api\Transaction;
    use PayPal\Api\ExecutePayment;
    use PayPal\Api\PaymentExecution;
    use PayPal\Api\Sale;
    use PayPal\Api\Refund;



    $Paypal = Factory::get('PayPal');

    $ApiContext = $Paypal->get_api_context();

    $payments = Payment::all(array('count' => 10, 'start_index' => 0), $ApiContext);

    print_r($payments);

    echo PHP_EOL;