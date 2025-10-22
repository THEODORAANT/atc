<?php
	/*
		Get a list of sites for a license
	 */
	
	$Page->layout = 'json';
	$Conf->debug = 0;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Customers 	   = Factory::get('Customers');
		$LicensedSites = Factory::get('LicensedSites');
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$slug 			= filter_input(INPUT_POST, 'licenseSlug', FILTER_SANITIZE_STRING); 
		$siteID			= filter_input(INPUT_POST, 'siteID', FILTER_SANITIZE_NUMBER_INT); 

		$Customer  = $Customers->find($customerID);
		$License   = false;

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();

				if ($slug) {
					
					if ($siteID) {

						// Just one site

						$Site = $LicensedSites->find_for_license($siteID, $slug, $Customer);
						if (is_object($Site)) {
							$out = $Site->to_array();
						}

					}else{

						// List of sites

						$sites = $LicensedSites->get_for_license($slug, $Customer);

						if (Util::count($sites)) {
							foreach($sites as $Site) {
								$out[] = $Site->to_array();
							}
						}	
					
					}

					
	
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
