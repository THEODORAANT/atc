<?php
	/*
		Create a new customer. Exciting!
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
		$Customers 	   = Factory::get('Customers');
		
		$email     = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
		
		$Customer  = $Customers->find_by_email($email);

		if (is_object($Customer)) {

			// password
			$Hasher 	= Factory::get('PasswordHash', 8, true);
			$clear_pwd  = $Customer->generate_password();
			$pwd  		= $Hasher->HashPassword(md5($clear_pwd));
     

			$Customer->update(['customerPassword'=>$pwd]);

			$Customer->send_password_reset_email(1, $clear_pwd);

			$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
				];
		}
	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
