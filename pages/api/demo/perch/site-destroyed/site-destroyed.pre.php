<?php
	/*
		This is called when a demo node has successfully removed a demo site and its data.

		The job of this script is to then mark the demo as DEAD.

	 */
	

	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['demo'];

	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) { // && $_SERVER['HTTP_X_ATC_CLIENT']==$_POST['node']
		$Demos = Factory::get('Demos');
		$Demo = $Demos->get_one_by('demoHost', $_POST['host']);
	}else{
		$Demo = false;
	}

	$result = [
			'result' => 'ERROR',
		];


	if (is_object($Demo)) {

		if ($Demo->kill($_POST['node'])) {
			$result = [
				'result' => 'OK',
			];
		}	
	}
