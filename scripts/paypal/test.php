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

	if ($Conf->payment_gateway['test_mode']) {
		$ApiContext = new ApiContext(new OAuthTokenCredential($Conf->paypal['keys']['test']['client_id'], $Conf->paypal['keys']['test']['secret']));

		$ApiContext->setConfig([
				'mode'                   => 'sandbox',
				'http.ConnectionTimeOut' => 30,
				'log.LogEnabled'         => true,
				'log.FileName'           => 'PayPal.log',
				'log.LogLevel'           => 'FINE'
			]);

	}else{
		$ApiContext = new ApiContext(new OAuthTokenCredential($Conf->paypal['keys']['live']['client_id'], $Conf->paypal['keys']['live']['secret']));
	
		$ApiContext->setConfig([
				'mode'                   => 'live',
				'http.ConnectionTimeOut' => 10,
				'log.LogEnabled'         => false,
			]);
	}


	$payer = new Payer();
	$payer->setPayment_method("paypal");

	// ### Amount
	// Let's you specify a payment amount.
	$amount = new Amount();
	$amount->setCurrency("USD");
	$amount->setTotal("1.00");

	// ### Transaction
	// A transaction defines the contract of a
	// payment - what is the payment for and who
	// is fulfilling it. Transaction is created with
	// a `Payee` and `Amount` types
	$transaction = new Transaction();
	$transaction->setAmount($amount);
	$transaction->setDescription("This is the payment description.");

	// ### Redirect urls
	// Set the urls that the buyer must be redirected to after 
	// payment approval/ cancellation.
	$baseUrl = 'https://grabaperch.com/buy/';
	$redirectUrls = new RedirectUrls();
	$redirectUrls->setReturn_url("$baseUrl/ExecutePayment.php?success=true");
	$redirectUrls->setCancel_url("$baseUrl/ExecutePayment.php?success=false");

	// ### Payment
	// A Payment Resource; create one using
	// the above types and intent as 'sale'
	$payment = new Payment();
	$payment->setIntent("sale");
	$payment->setPayer($payer);
	$payment->setRedirect_urls($redirectUrls);
	$payment->setTransactions(array($transaction));

	// ### Create Payment
	// Create a payment by posting to the APIService
	// using a valid apiContext.
	// (See bootstrap.php for more on `ApiContext`)
	// The return object contains the status and the
	// url to which the buyer must be redirected to
	// for payment approval
	try {
		$payment->create($ApiContext);
	} catch (\PPConnectionException $ex) {
		echo "Exception: " . $ex->getMessage() . PHP_EOL;
		var_dump($ex->getData());	
		exit(1);
	}

	// ### Redirect buyer to paypal
	// Retrieve buyer approval url from the `payment` object.
	foreach($payment->getLinks() as $link) {
		if($link->getRel() == 'approval_url') {
			$redirectUrl = $link->getHref();
		}
	}

	print_r($redirectUrl);