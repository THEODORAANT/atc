<?php
	/*
		Stash a customerID for the logged in user

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
		
		$customerID     = filter_input(INPUT_POST, 'customerID', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'customerToken', FILTER_UNSAFE_RAW); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			$Stash = Factory::get('OAuthStash');
			$url = $Stash->set_customer($token, $customerID);
	
			$result = [
				'result' => 'OK',
				'status' => '200',
				'url'	 => $url,
			];
		
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
