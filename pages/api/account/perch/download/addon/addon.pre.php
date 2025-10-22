<?php
	/*
		Log that an add-on download has occured. 
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
		$slug 			= filter_input(INPUT_POST, 'slug', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				if ($slug) {
					
					$data = [
						'customerID'       => $customerID,
						'addonSlug'        => $slug,
						'downloadDateTime' => date('Y-m-d H:i:s'),
					];
					
					$DB = Factory::get('DB');
					$DB->insert('tblAddonDownloads', $data);

					$result = [
							'result'  		=> 'OK',
							'status'		=> '200',
						];
				}
			
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
