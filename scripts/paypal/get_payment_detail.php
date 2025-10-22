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


	$payment_id = 'PAY-4WT810708L819904LKSIV4WI'; // method: paypal
	$payment_id = 'PAY-30168936F5235323TKSDSARQ';


    $Paypal = Factory::get('PayPal');

    $ApiContext = $Paypal->get_api_context();



    $payment      = Payment::get($payment_id, $ApiContext);
    //$transactions = $payment->getTransactions();
    $payer 		= $payment->getPayer();
    $payer_info = $payer->getPayerInfo();
    $adr 		= $payer_info->getShippingAddress();

    print_r($adr->country_code);


    echo PHP_EOL;