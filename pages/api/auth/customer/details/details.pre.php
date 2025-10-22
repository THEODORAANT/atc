<?php
	/*
		Validate a customerID and token combination

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
			        $customer_array=$Customer->to_array();
			       /* $orders = $Customer->get_orders();
			        echo "orders";
			        print_r( $orders);
                    if (Util::count($orders)) {
                        $customer_array["customerFirstOrder"]= "2022-04-02";//$orders[0]->orderDate();
                    }*/

				$result = [
					'result'  		 => 'OK',
					'status'		 => '200',
					'customer'		 => $Customer->to_array(),
				];
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
