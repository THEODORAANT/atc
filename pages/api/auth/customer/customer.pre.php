<?php
	/*
		Authenticate a customer by email address and password

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
		$Countries 	   = Factory::get('Countries');

		$email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
		$clear_pwd = filter_input(INPUT_POST, 'pwd', FILTER_UNSAFE_RAW); // don't strip chars

		$Customer  = $Customers->get_one_by('customerEmail', $email);

		if (is_object($Customer)) {

			$Hasher = Factory::get('PasswordHash', 8, true);

			if ($Hasher->CheckPassword($clear_pwd, $Customer->customerPassword())) {

				if ($Customer->customerActive()=='1') {

					$Country = $Countries->find($Customer->countryID());

					$session_token = $Customer->generate_new_session_token();

					$result = [
						'result'  		 => 'OK',
						'status'		 => '200',
						'id'             => $Customer->id(),
						'first_name'     => $Customer->customerFirstName(),
						'last_name'      => $Customer->customerLastName(),
						'email'          => $Customer->customerEmail(),
						'company'        => $Customer->customerCompany(),
						'country_id'     => $Customer->countryID(),
						'country'        => $Country->countryName(),
						'adr_street1'    => $Customer->customerStreetAdr1(),
						'adr_street2'    => $Customer->customerStreetAdr2(),
						'adr_locality'   => $Customer->customerLocality(),
						'adr_region'     => $Customer->customerRegion(),
						'adr_postcode'   => $Customer->customerPostalCode(),
						'vat_number'     => $Customer->customerVATnumber(),
						'vat_rate'       => $Country->countryVATRate(),
						'discount'       => $Customer->customerDiscount(),
						'first_order'    => $Customer->customerFirstOrder(),
						'adr_lat'        => $Customer->customerLat(),
						'adr_lng'        => $Customer->customerLng(),
						'mailchimp_euid' => $Customer->customerMailChimpEUID(),
						'token'			 => $session_token,
					];

					if ($Customer->is_registered_developer()) {
						$result['reg_dev'] = true;
					}



				}else{
					$result = [
						'result'  => 'ERROR',
						'status'  => '410',
						'message' => 'Inactive customer account',
					];
				}

			}else{
				$result = [
					'result'  => 'ERROR',
					'status'  => '401',
					'message' => 'Invalid password',
				];
			}

		}else{
			$result = [
				'result'  => 'ERROR',
				'status'  => '404',
				'message' => 'Unknown email address.',
			];
		}

	}else{


	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);

		#$msg = print_r($_SERVER, true)."\n\n";
		#$msg .= print_r($_POST, true)."\n\n";

		#Util::send_email('drew.mclellan@gmail.com', 'hello@grabaperch.com', 'hello@grabaperch.com', 'ATC debug', $msg, false);