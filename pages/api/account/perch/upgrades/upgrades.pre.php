<?php
	/*
		Get a list of the available upgrades

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
		
		$customerID      = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token           = filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$status          = filter_input(INPUT_POST, 'status', FILTER_UNSAFE_RAW); 
		$forProductID    = filter_input(INPUT_POST, 'productID', FILTER_SANITIZE_NUMBER_INT); 
		$forVersionMajor = filter_input(INPUT_POST, 'versionMajor', FILTER_SANITIZE_NUMBER_INT); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$Upgrades = Factory::get('Upgrades');

				$upgrades = $Upgrades->get_for_customer($customerID, $forProductID, $status, $forVersionMajor);

				$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					'upgrades'		=> $upgrades,
				];
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
