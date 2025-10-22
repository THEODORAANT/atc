<?php
	/*
		Get details (especially price) for a given product
	*/
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];

	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {

		$code 			= filter_input(INPUT_POST, 'code', FILTER_UNSAFE_RAW); 
		$qty 			= filter_input(INPUT_POST, 'qty', FILTER_UNSAFE_RAW); 
		$currency    	= filter_input(INPUT_POST, 'currency', FILTER_UNSAFE_RAW); 

		$Products  = Factory::get('Products');
		$Product  = $Products->get_by_item_code($code);

		if (is_object($Product)) {

			$Currencies  = Factory::get('Currencies');
	        $Currency    = $Currencies->find($currency);

			$result = [
						'result' => 'OK',
						'status' => '200',
						'price' => (float)$Product->get_price($currency, $qty),
						'currency_symbol' => $Currency->currencySymbol(),
						'product' => $Product->to_array(),
					];

		}

	}