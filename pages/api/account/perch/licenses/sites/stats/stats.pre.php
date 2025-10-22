<?php
	/*
		Get a count of the different license types

	 */
	
	$Page->layout = 'json';
	$Conf->debug = 0;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Customers 	    = Factory::get('Customers');
		$Licenses  		= Factory::get('Licenses');
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$slug 			= filter_input(INPUT_POST, 'licenseSlug', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();
				$out['limit'] = 1;
				$out['used']  = 1;
				$out['available'] = 0;

				$License = $Licenses->get_by_slug($slug, $customerID);

				if ($License) {


					if ($License->licenseMultisite()) {
						$out['limit'] = (int) $License->licenseMultisiteLimit();
					}

					$out['used'] = $License->get_site_count();

					$out['available'] = $out['limit'] - $out['used'];

				}


				$result = [
					'result'  	=> 'OK',
					'status'	=> '200',
					'sites'		=> $out
				];
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
