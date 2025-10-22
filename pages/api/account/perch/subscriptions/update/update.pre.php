<?php
	/*
		Update a subscription with new payment details

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
		$Subscriptions = Factory::get('Subscriptions');
		
		$customerID   = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token        = filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$subID        = filter_input(INPUT_POST, 'subID', FILTER_SANITIZE_NUMBER_INT); 
		$stripe_token = filter_input(INPUT_POST, 'stripe_token', FILTER_UNSAFE_RAW); 
		
		$Customer     = $Customers->find($customerID);


		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();
				
				$Sub = $Customer->get_subscription($subID);
				if (is_object($Sub)) {
					if ($Sub->update_stripe_token($stripe_token)){
						$result = [
							'result'  		=> 'OK',
							'status'		=> '200',
						];
					}
				}
				
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
