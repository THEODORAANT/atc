<?php
	/*
		Update a license
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
		$Licenses 	   = Factory::get('Licenses');
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$slug 			= filter_input(INPUT_POST, 'licenseSlug', FILTER_SANITIZE_STRING); 
		$siteID			= filter_input(INPUT_POST, 'siteID', FILTER_SANITIZE_NUMBER_INT); 
		$productID		= filter_input(INPUT_POST, 'productID', FILTER_SANITIZE_NUMBER_INT); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				if ($slug) {

					// Update?
					if ($siteID) {
						$LicensedSite = $LicensedSites->find_for_license($siteID, $slug, $Customer);
					
						if (is_object($LicensedSite)) {
							
							$data = [];
							$postvars = ['licenseDesc', 'licenseDomain1', 'licenseDomain2', 'licenseDomain3', 'delete'];
							foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);

							// Delete?
							if ($data['delete']) {

								$LicensedSite->delete();

							}else{
								unset($data['delete']);

								$LicensedSite->update($data);
								$LicensedSite->set_product($productID);
							}
								
							

							$result = [
									'result'  		=> 'OK',
									'status'		=> '200',
								];
						}	
					}else{
						// Create
						$data = [];
						$postvars = ['licenseDesc', 'licenseDomain1', 'licenseDomain2', 'licenseDomain3'];
						foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);

						$License = $Licenses->get_by_slug($slug, $customerID);
						$data['licenseID'] = $License->id();

						$LicensedSite = $LicensedSites->create($data);
						$LicensedSite->set_product($productID);

						$result = [
									'result'  		=> 'OK',
									'status'		=> '200',
									'created'		=> 'true',
								];

					}
					
				}
			
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
