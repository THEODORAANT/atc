<?php
	/*
		This is called when a demo node has successfully set up a demo site.

		The job of this script is to then mark the demo as active and notify the customer.

	 */
	
	$debug_msg = '';

	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['demo'];

	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) { // && $_SERVER['HTTP_X_ATC_CLIENT']==$_POST['node']
		$Demos = Factory::get('Demos');
		$Demo = $Demos->get_one_by('demoHost', $_POST['host']);
		$debug_msg .= 'secret checks out '.PHP_EOL;
	}else{
		$Demo = false;
	}


	if (is_object($Demo)) {

		$debug_msg .= 'we have a demo '.PHP_EOL;

		if ($Demo->activate($_POST['node'])) {
			$debug_msg .= 'activate success '.PHP_EOL;
			$Demo->send_welcome_email_to_customer();	
		}
		
		$result = [
			'result' => 'OK',
		];
	}else{
		$result = [
			'result' => 'ERROR',
		];
	}

	#$debug_msg .= print_r($_POST, true) . PHP_EOL . PHP_EOL;
	#$debug_msg .= print_r($_SERVER, true) . PHP_EOL . PHP_EOL;
	#$debug_msg .= print_r($_GET, true) . PHP_EOL . PHP_EOL;


	#Util::send_email('drew@edgeofmyseat.com', 'info@edgeofmyseat.com', 'ATC', 'Demo debug', $debug_msg);
	