<?php
	/*
		Check that a promo code is valid
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

		$code     		= filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW); 

		if ($code) {

			$Promos = Factory::get('PromoCodes');
			$Promo  = $Promos->get_valid($code);

			if (is_object($Promo)) {
				$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					'percentage'	=> $Promo->promoDiscount(),
				];
			}
		
		}

	}
