<?php
	/*
		Add a promo code to the customer's basket
	*/
	
	$Page->layout = 'json';
	$Conf->debug  = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status'		=> '410',
		'message'		=> 'That code is no longer valid.',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Customers 	   = Factory::get('Customers');
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$code     		= filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW); 
	
		$Customer  = $Customers->find($customerID);

		if (is_object($Customer) && $code) {

			if ($Customer->check_session_token($token)) {

				$Baskets = Factory::get('Baskets');
				$Basket  = $Baskets->get_for_customer($customerID);

				$Promos = Factory::get('PromoCodes');
				$Promo  = $Promos->get_valid($code);

				if (is_object($Promo)) {

					if ($Basket->apply_promo_code($Promo)) {
						$result = [
							'result'  		=> 'OK',
							'status'		=> '200',
						];
					}
				}
			}

		}

	}
