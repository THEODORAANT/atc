<?php
	/*
		Cancel a subscription

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
		$subID			= filter_input(INPUT_POST, 'subID', FILTER_SANITIZE_NUMBER_INT); 
		$reason			= filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();

				if ($subID) {
					$Sub = $Customer->get_subscription($subID);
					if (is_object($Sub)) {
						if ($Sub->cancel($reason)){
							$result = [
								'result'  		=> 'OK',
								'status'		=> '200',
							];
						}
						
					}
				}

				

				
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
