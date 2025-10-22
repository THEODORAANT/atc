<?php
	/*
		Apply an upgrade to a Perch 1 license to Perch 2
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
		$Upgrades 	   = Factory::get('Upgrades');
	
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$slug 			= filter_input(INPUT_POST, 'licenseSlug', FILTER_SANITIZE_STRING); 
		$productID 		= filter_input(INPUT_POST, 'productID', FILTER_SANITIZE_NUMBER_INT); 
		$versionMajor   = filter_input(INPUT_POST, 'versionMajor', FILTER_SANITIZE_NUMBER_INT); 
		$target   		= filter_input(INPUT_POST, 'target', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				if ($slug) {
					$License = $Customer->get_license($productID, $slug);
					if (is_object($License)) {

						// Runway Dev upgrade?
						if (isset($target) && $target=='RUNWAYDEV') {

							$Upgrade = $Upgrades->get_evergreen_upgrade($productID, $versionMajor, PROD_RUNWAYDEV);

							if ($Upgrade) {

								$License->apply_upgrade($Upgrade);
								
							    Slack::notify('A Runway Developer Crossgrade! ('.$Customer->customerFirstName().' '.$Customer->customerLastName().')', '#buys');
		
								$result = [
									'result'  		=> 'OK',
									'status'		=> '200',
									'license_key'   => $License->licenseKey(),
								];
							}else{
								$result['Message'] ='No available evergreen upgrades.';
							}

						}else{

							$upgrades = $Upgrades->get_for_customer($Customer->id(), $productID, 'UNSPENT', $versionMajor);

							if (Util::count($upgrades)) {

								$Upgrade = $upgrades->current();

								$License->apply_upgrade($Upgrade);
							
								$result = [
									'result'  		=> 'OK',
									'status'		=> '200',
									'license_key'   => $License->licenseKey(),
								];

							}else{
								$result['Message'] ='No available upgrades.';
							}


						}


						
					}
				}
			
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
