<?php
	/*
		Get a list of promocodes that this customer has earned.

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


		$Customer  = $Customers->find($customerID);



		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {


				$out = array();
				$PromoCodes 	   = Factory::get('CustomerPromoCodes');
				$codes = $PromoCodes->get_customer_promo_codes($customerID);

			

				if (Util::count($codes)) {
					foreach($codes as $Promocode) {
						$out[] = $Promocode->to_array();
					}
				}

				

				$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					'promocodes'		=> $out
				];
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
