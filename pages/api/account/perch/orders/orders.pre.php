<?php
	/*
		Get a list of order, or a single order if a orderRef is passed in

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
		$orderRef		= filter_input(INPUT_POST, 'orderRef', FILTER_SANITIZE_STRING); 
		$dir			= filter_input(INPUT_POST, 'dir', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();

				if ($orderRef) {
					$Order = $Customer->get_order($orderRef);
					if (is_object($Order)) {
						$out = $Order->to_array();	
					}
				}else{
					if (!$dir) $dir = 'ASC';

					$orders = $Customer->get_orders($dir);

					if (Util::count($orders)) {
						foreach($orders as $Order) {
							$out[] = $Order->to_array();
						}
					}
				}

				

				$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					'orders'		=> $out
				];
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
