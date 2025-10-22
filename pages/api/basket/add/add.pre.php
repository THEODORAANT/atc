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
		$items_json     = filter_input(INPUT_POST, 'items', FILTER_UNSAFE_RAW); 
	
		$Customer  = $Customers->find($customerID);

		if (is_object($Customer) && $items_json) {

			if ($Customer->check_session_token($token)) {

				$items = json_decode($items_json);

				if (Util::count($items)) {

					$Baskets = Factory::get('Baskets');
					$Basket  = $Baskets->get_for_customer($customerID);
                    $valid_products = array("SUBRUNWAY-1M" ,"R2SUBUPGRADE-1M", "RUNWAYDEV","SUBRUNWAYNEW-1M","YEARSUBRUNWAY-12M");
					foreach($items as $Item) {
                        if ((in_array($Item->code, $valid_products)  ) || ($Item->code=="SUBRUNWAYNEW-1M" && $Customer->customerFirstOrder()==null)) {
                            $Basket->add($Item->code, $Item->qty, $Item->replace);
                        }

					}

					$result = [
						'result'  		=> 'OK',
						'status'		=> '200',
					];
				}

			}

		}

	}
