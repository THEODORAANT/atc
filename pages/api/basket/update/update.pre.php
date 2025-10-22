<?php
	/*
		Update the customer's basket with adjusted qtys
	*/
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Customers 	   = Factory::get('Customers');
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$items_json     = filter_input(INPUT_POST, 'items', FILTER_UNSAFE_RAW); 
	
		$Customer  = $Customers->find($customerID);

		if (is_object($Customer) && $items_json) {

			if ($Customer->check_session_token($token)) {

				$items = json_decode($items_json);

				if (Util::count($items)) {

					$Baskets = Factory::get('Baskets');
					$Basket  = $Baskets->get_for_customer($customerID);

					foreach($items as $Item) {
						$Basket->update_qty($Item->code, $Item->qty);
					}

					$result = [
						'result'  		=> 'OK',
						'status'		=> '200',
					];
				}

			}

		}

	}
