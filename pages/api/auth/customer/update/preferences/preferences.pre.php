<?php
	/*
		Update a customer's preferences
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

				
				$data = [];
				$postvars = ['customerForumEmailNotifications'];
				foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);

				if (!isset($data['customerForumEmailNotifications'])) $data['customerForumEmailNotifications'] = '0';

				$Customer->update($data);

				$data = [];
				$postvars = ['list:newsletter', 'list:offers', 'list:tips'];
				foreach($postvars as $var) $data[$var] = filter_input(INPUT_POST, $var);

				foreach($postvars as $tag) {
					if (isset($data[$tag]) && $data[$tag]=='1') {
						$Customer->tag($tag);
					}else{
						$Customer->detag($tag);
					}
				}


				$result = [
						'result'  		=> 'OK',
						'status'		=> '200',
					];
	
			}
		}	

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
