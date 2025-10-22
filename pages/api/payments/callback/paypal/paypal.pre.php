<?php

	$Page->layout = 'empty';
	$Conf->debug = true;

	$PayPal = Factory::get('PayPal');

	$token 		= filter_input(INPUT_GET, 'token', FILTER_UNSAFE_RAW); 
	$payer_id   = filter_input(INPUT_GET, 'PayerID', FILTER_UNSAFE_RAW); 

	$order_ref  = $Page->arg(1);
	$verify_key = $Page->arg(2);

	$Orders 	= Factory::get('Orders');
	$Order 		= $Orders->get_one_by('orderRef', $order_ref);

	if ($Order) {

		$orderID = $Order->id();

		if ($Order->orderStatus()=='PENDING') {
			if ($Order->orderVerifyKey()==$verify_key && $token && $payer_id) {

				$PayPal->complete_transaction($Order, ['payer_id'=>$payer_id], function($result) use ($orderID) {
					// Not OK, so log it.
					GatewayLogger::log([
						'logGateway' => 'PAYPAL',
						'orderID' => $orderID,
						'logData' => json_encode($result),
						]);
				});

			} 
		} else {

			// order wasn't PENDING, which means something odd has happened.

			GatewayLogger::log([
						'logGateway' => 'PAYPAL',
						'orderID' => $orderID,
						'logData' => json_encode(['GET'=>$_GET, 'POST'=>$_POST, 'SERVER'=>$_SERVER]),
						]);

		}



		Util::redirect($Order->orderRedirectUrl().'/'.$Order->orderRef());
	}

