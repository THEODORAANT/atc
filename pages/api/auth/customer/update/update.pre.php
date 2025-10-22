<?php
	/*
		Update a customer's account/personal details
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

				
				$data = [];
				$postvars = ['customerFirstName', 'customerLastName', 'customerCompany', 'customerEmail', 'customerStreetAdr1', 'customerStreetAdr2', 'customerLocality', 'customerRegion', 'countryID', 'customerVATnumber', 'customerVATnumberValid', 'customerPostalCode'];
				foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);
							

				// Check that the email address isn't in use
				$ExistingCustomer = $Customers->find_by_email($data['customerEmail']);
				if (is_object($ExistingCustomer) && $ExistingCustomer->id()!=$Customer->id()) {
					
					// If it's not this customer, they can't use that email address

					$result = [
						'result' => 'ERROR',
						'status' => '409',
						'message' => 'An account with that email address already exists.',
					];
				
				} else{

					$Customer->update($data);
					$Customer->geocode();

					$result = [
							'result'  		=> 'OK',
							'status'		=> '200',
						];

				}





	
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
