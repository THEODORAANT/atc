<?php
	/*
		Update a customer's developer listing details
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

				$Developers = Factory::get('RegisteredDevelopers');
				$Developer = $Developers->get_by_customer($Customer->id());

				if ($Developer) {

					$data = [];
					$postvars = ['devListingEnabled', 'devTitle', 'devDescRaw', 'devDescHTML', 'devURL', 'devLogo', 'devLocation', 'devProjectLocation', 'devTakesExistingProjects', 'devProductPerch', 'devProductRunway'];
					foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);

					if (!isset($data['devListingEnabled']) || $data['devListingEnabled']=='') 				$data['devListingEnabled'] = 0;
					if (!isset($data['devTakesExistingProjects']) || $data['devTakesExistingProjects']=='') $data['devTakesExistingProjects'] = 0;
					if (!isset($data['devProductPerch']) || $data['devProductPerch']=='') 					$data['devProductPerch'] = 0;
					if (!isset($data['devProductRunway']) || $data['devProductRunway']=='') 				$data['devProductRunway'] = 0;

					$Developer->update($data);

					$result = [
							'result'  		=> 'OK',
							'status'		=> '200',
						];

				}
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
