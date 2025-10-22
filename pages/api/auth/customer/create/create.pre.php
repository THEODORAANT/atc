<?php
	/*
		Create a new customer. Exciting!
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
				
		$data = [];
		$postvars = ['customerFirstName', 'customerLastName', 'customerCompany', 'customerEmail', 'customerStreetAdr1', 'customerStreetAdr2', 'customerLocality', 'customerRegion', 'countryID', 'customerVATnumber', 'customerVATnumberValid', 'customerPassword', 'customerPostalCode','customerReferredBy'];
		foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);

		if(isset($data['customerReferredBy']) && $data['customerReferredBy'] != '') {
			//look up referring customer ID
			$ReferringCustomer = $Customers->find_by_referrer($data['customerReferredBy']);
			if(is_object($ReferringCustomer)) {
				$data['customerReferredBy'] = $ReferringCustomer->id();
			}
		} else {
			$data['customerReferredBy'] = null;
		}
				

		// Check that the email address isn't in use, of if it is, it belongs to a prospect
		$ExistingCustomer = $Customers->find_by_email($data['customerEmail']);
		if (is_object($ExistingCustomer) && !$ExistingCustomer->customerIsProspect()) {

			$result = [
				'result' => 'ERROR',
				'status' => '409',
				'message' => 'An account with that email address already exists.',
			];

			
		} else{

			// password
			$Hasher = Factory::get('PasswordHash', 8, true);
			if (isset($data['customerPassword'])) {
	            $data['customerPassword'] = $Hasher->HashPassword($data['customerPassword']);
	        }

	        // Are they a prospect? If so update.
	        if (is_object($ExistingCustomer)) {
	        	$Customer = $ExistingCustomer;
	        	$data['customerIsProspect'] = '0';
	        	$Customer->update($data);
	        }else{
	        	// Else create a new
	        	$Customer = $Customers->create($data);	
	        }

			if ($Customer) {

				$Customer->geocode();

				$Customer->generate_referral_code();

				if (filter_input(INPUT_POST, 'send_email', FILTER_VALIDATE_BOOLEAN)) {
					$Customer->send_welcome_email(1);
				}
			}

			$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					//'debug'			=> Console::output(),
				];

		}






	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
