<?php
	/*
		Change the customer's password
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
		$current_pwd	= filter_input(INPUT_POST, 'current_password', FILTER_UNSAFE_RAW); 
		$new_pwd		= filter_input(INPUT_POST, 'new_password', FILTER_UNSAFE_RAW); 
		
		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {
				
				$Hasher = Factory::get('PasswordHash', 8, true);

				if ($Hasher->CheckPassword($current_pwd, $Customer->customerPassword())) {

					$Customer->update(['customerPassword'=>$Hasher->HashPassword($new_pwd)]);

					$result = [
							'result'  		=> 'OK',
							'status'		=> '200',
						];


				} else {

					$result = [
						'result' => 'ERROR',
						'status' => '500',
						'message' => 'Current password does not match.',
					];

				}
			}
		}	
	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
