<?php
	/*
		Add an item to the customer's basket
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
		$currency 		= filter_input(INPUT_POST, 'currency', FILTER_UNSAFE_RAW); 
		$client_ip 		= filter_input(INPUT_POST, 'ip', FILTER_VALIDATE_IP); 
	
		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

					$Baskets = Factory::get('Baskets');
					$Basket  = $Baskets->get_for_customer($customerID);

					if ($client_ip) {
						$Basket->set_client_ip($client_ip);
					}

					$contents 	 = $Basket->get_contents($currency);

					$result = [
						'result' => 'OK',
						'status' => '200',
						'basket' => $contents,
					];

			}

		}

	}


	//$result['debug']  = Console::output();
