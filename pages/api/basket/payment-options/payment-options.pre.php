<?php
	/*
		Get an array of currently action payment options. Hardcoded currently.
	*/
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {

		$subscriptions = false;

		$Customers 	   = Factory::get('Customers');
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 


		// Check for subscriptions. We only take Stripe for subscriptions currently.
		if ($customerID && $token) {
		
			$Customer  = $Customers->find($customerID);

			if (is_object($Customer)) {

				if ($Customer->check_session_token($token)) {

					$Baskets = Factory::get('Baskets');
					$Basket  = $Baskets->get_for_customer($customerID);

					$subscriptions = $Basket->has_subscriptions();

				}
			}
		}

		$opts = [];

		if ($Conf->stripe['enabled']) {

			if ($Conf->payment_gateway['test_mode']) {
				$opts['stripe'] = $Conf->stripe['keys']['test']['publishable'];
			}else{
				$opts['stripe'] = $Conf->stripe['keys']['live']['publishable'];
			}
			
		}

		if ($Conf->paypal['enabled'] && !$subscriptions) {

			$opts['paypal'] = true;
		}
		
		
		// Suspend EU sales (Brexit)
		if ($Conf->suspend_eu_sales && $Customer->is_in_EU()) {
			$opts = [];
		}



		$result = [
			'result' => 'OK',
			'status' => '200',
			'payment_options' => $opts,
		];

	}
