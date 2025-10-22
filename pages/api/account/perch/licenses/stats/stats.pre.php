<?php
	/*
		Get a count of the different license types

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

				$out = array();
				$total = 0;
				$multisite_limit = 0;
				$multisite_used = 0;

				$licenses = $Customer->get_licenses(PROD_RUNWAY);
				$out['runway'] = (string) Util::count($licenses);
				$out['runway_unused'] = 0;
				if (Util::count($licenses)) {
					foreach($licenses as $License) {
						if ($License->licenseDomain1() == '') {
							$out['runway_unused']++;
						}
					}
				}
				$total += Util::count($licenses);

				$licenses = $Customer->get_licenses(PROD_RUNWAYDEV);
				$out['runwaydev'] = (string) Util::count($licenses);
				$out['runwaydev_unused'] = 0;
				if (Util::count($licenses)) {
					foreach($licenses as $License) {
						if ($License->licenseDomain1() == '') {
							$out['runwaydev_unused']++;
						}
					}
				}
				$total += Util::count($licenses);


				$licenses = $Customer->get_licenses(PROD_R2SUBUPGRADE);
				$out['upgraded'] = (string) Util::count($licenses);


				$licenses = $Customer->get_licenses(PROD_PERCH);
				$out['perch'] = (string) Util::count($licenses);
				$out['perch_unused'] = 0;
				if (Util::count($licenses)) {
					foreach($licenses as $License) {
						if ($License->licenseDomain1() == '') {
							$out['perch_unused']++;
						}

						if ($License->licenseMultisite()) {
							$multisite_limit += (int) $License->licenseMultisiteLimit();
							$multisite_used += (int) $License->get_site_count();
						}
					}
				}
				$total += Util::count($licenses);

				$Upgrades = Factory::get('Upgrades');
				$out['upgrades'] = (string) $Upgrades->get_count_for_customer($customerID);

				$out['total'] = $total;
				$out['multisite_limit'] = $multisite_limit;
				$out['multisite_used'] = $multisite_used;


				$regdev = $Customer->is_registered_developer();
				if ($regdev) {
					$out['regdev'] = $regdev->devSubscriptionTo();
				}


				$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					'licenses'		=> $out,
				];
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
