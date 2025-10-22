<?php
	/*
		Update a license
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
		$slug 			= filter_input(INPUT_POST, 'licenseSlug', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				if ($slug) {
					$License = $Customer->get_license(PROD_PERCH, $slug);
					if (!is_object($License)){
						$License = $Customer->get_license(PROD_RUNWAY, $slug);

						if (!is_object($License)){
							$License = $Customer->get_license(PROD_RUNWAYDEV, $slug);
						}
					}
					if (is_object($License)) {
						
						$data = [];
						$postvars = ['licenseDesc', 'licenseDomain1', 'licenseDomain2', 'licenseDomain3'];
						foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);
							
						

						$License->update($data);

						$result = [
								'result'  		=> 'OK',
								'status'		=> '200',
							];
					}
				}
			
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
