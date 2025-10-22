<?php
	/*
		Get a list of licenses, or a single license if a slug is passed in

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
		
		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$slug 			= filter_input(INPUT_POST, 'licenseSlug', FILTER_SANITIZE_STRING); 
		$type 			= filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING); 
		$orderRef		= filter_input(INPUT_POST, 'orderRef', FILTER_SANITIZE_STRING); 

		$Customer  = $Customers->find($customerID);
		$License   = false;

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();

				if ($slug) {

					if (!is_object($License)) {
						$License = $Customer->get_license(PROD_PERCH, $slug);
						if (is_object($License)) {
							$tmp = $License->to_array();	
							$tmp['product_code'] = 'PERCH';
							$tmp['legacyFileName3'] = 'perch_v3.2.zip';
							$tmp['legacyFileName'] = 'perch_v2.8.34.zip';


							$out = $tmp;
						}
						
					}

					if (!is_object($License)) {
						$License = $Customer->get_license(PROD_RUNWAY, $slug);
						if (is_object($License)) {
							$tmp = $License->to_array();	
							$tmp['product_code'] = 'RUNWAY';
							$tmp['legacyFileName'] = 'runway_v2.8.34.zip';
							$tmp['legacyFileName3'] = 'runway_v3.2.zip';
							$out = $tmp;
						}
						
					}

					if (!is_object($License)) {
						$License = $Customer->get_license(PROD_RUNWAYDEV, $slug);
						if (is_object($License)) {
							$tmp = $License->to_array();	
							$tmp['product_code'] = 'RUNWAYDEV';
							$tmp['legacyFileName'] = 'runway_v2.8.34.zip';
							$out = $tmp;
						}
						
					}
						
					

				}else{

					if ($orderRef) {

						$licenses = $Customer->get_licenses_for_order($orderRef);

						if (Util::count($licenses)) {
							foreach($licenses as $License) {
								$tmp = $License->to_array();
								switch($License->productID()) {
									case 1:
										$tmp['product_code'] = 'PERCH';
										break;
									case 4:
										$tmp['product_code'] = 'RUNWAY';
										break;
									case 10:
										$tmp['product_code'] = 'RUNWAYDEV';
										break;
								}
								$out[] = $tmp;
							}
						}

					} else {

						if ($type==false || $type=='runway') {
							$licenses = $Customer->get_licenses(PROD_RUNWAY);

							if (Util::count($licenses)) {
								foreach($licenses as $License) {
									$tmp = $License->to_array();
									$tmp['product_code'] = 'RUNWAY';
									$out[] = $tmp;
								}
							}
						}
                    if ($type==false || $type=='upgraded') {
							$licenses = $Customer->get_licenses(PROD_R2SUBUPGRADE);

							if (Util::count($licenses)) {
								foreach($licenses as $License) {
									$tmp = $License->to_array();
									$tmp['product_code'] = 'RUNWAY';
									$out[] = $tmp;
								}
							}
						}
						if ($type==false || $type=='runwaydev') {
							$licenses = $Customer->get_licenses(PROD_RUNWAYDEV);

							if (Util::count($licenses)) {
								foreach($licenses as $License) {
									$tmp = $License->to_array();
									$tmp['product_code'] = 'RUNWAYDEV';
									$out[] = $tmp;
								}
							}
						}

						if ($type==false || $type=='perch') {
							$licenses = $Customer->get_licenses(PROD_PERCH);

							if (Util::count($licenses)) {
								foreach($licenses as $License) {
									$tmp = $License->to_array();
									$tmp['product_code'] = 'PERCH';
									$out[] = $tmp;
								}
							}
						}
					}

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
