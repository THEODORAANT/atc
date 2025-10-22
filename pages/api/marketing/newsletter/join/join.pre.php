<?php
	/*
		Add the email address to the mailing lists.

		This is done either by finding the corresponding Customer account or by creating a new one as a Prospect.
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
		
		$email 		= filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL); 
	
		$Customer  = $Customers->find_by_email($email);

		if (!is_object($Customer)) {
			// Create as a prospect
			
			$Customer = $Customers->create([
					'customerFirstName'	 => '',
					'customerLastName'	 => '',
					'customerEmail'	 	 => $email,
					'customerActive'	 => '1',
					'customerIsProspect' => '1',
					]);
		}

		if (is_object($Customer)) {

			$Customer->tag_for_default_mailing_lists();

			$result = [
				'result' => 'OK',
				'status' => '200',
			];
		}

	}


	//$result['debug']  = Console::output();
