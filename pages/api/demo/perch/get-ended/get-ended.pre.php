<?php
	/*
		Gets the finished demos that need cleaning up. (Flagged LIVE with an expired end date.)

		Limits to 10 at a time.

		This is so the demo node can delete the demo site and remove the data. Once that has been done, the site-destroyed method is called by the demo node and the demo is flagged as DEAD.

	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['demo'];

	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) { // && $_SERVER['HTTP_X_ATC_CLIENT']==$_POST['node']
		$Demos = Factory::get('Demos');
		$demos = $Demos->get_ended_needing_cleanup('perch', $_POST['node'], 10);
	}else{
		$demos = false;

	}

	if (Util::count($demos)) {
		$result = [
			'result' 	=> 'OK',
			'demos'  	=> $demos->getArrayCopy(),
		];
	}else{
		$result = [
			'result' => 'OK',
			'demos' => array(),
		];
	}


	