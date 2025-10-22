<?php
	/*
		Get a list of subscriptions

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
		$subID			= filter_input(INPUT_POST, 'subID', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();

				if ($subID) {
					$Sub = $Customer->get_subscription($subID);
					if (is_object($Sub)) {
						$out = $Sub->to_array();	
					}
				}else{
					$subs = $Customer->get_subscriptions();

					if (Util::count($subs)) {

						$Products = Factory::get('Products');

						foreach($subs as $Sub) {
							$Product = $Products->get_by_item_code($Sub->subItem());

							$tmp = $Sub->to_array();
							$tmp = array_merge($tmp, $Product->to_array());

							$out[] = $tmp;
						}
					}
				}

				

				$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					'subscriptions'		=> $out
				];
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
