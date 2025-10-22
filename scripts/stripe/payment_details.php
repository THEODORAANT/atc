#!/usr/bin/env php
<?php
	use \Stripe\Stripe;
	use \Stripe\Charge as Stripe_Charge;

    include(__DIR__.'/../env.php');


	if ($Conf->payment_gateway['test_mode']) {
		Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
	}else{
		Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
	}

	$Orders = Factory::get('Orders');
    $Order  = $Orders->find(28369);

	$Charge = Stripe_Charge::retrieve($Order->orderStripeChargeID());

	print_r($Charge);
