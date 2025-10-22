<?php
	/*
		Gets the currently active demos.

	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['demo'];

	$message = '';

	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {  // && $_SERVER['HTTP_X_ATC_CLIENT']==$_POST['node']
		$Demos = Factory::get('Demos');
		$demos = $Demos->get_current('perch', $_POST['node']);
	}else{
		$demos = false;

		if (!isset($_POST['secret'])) {
			$message .= 'No secret. ';
		}

		if (isset($_POST['secret']) && $_POST['secret']!=$secret) {
			$message .= 'Secret does not match. ';
		}
	}


	if (Util::count($demos)) {

		$DemoNodes = Factory::get('DemoNodes');
		$Node = $DemoNodes->get_one_by('nodeName', $_POST['node']);

		if (!is_object($Node)) {
			$Node = $DemoNodes->create([
				'nodeName'     => $_POST['node'],
				'nodeLastSeen' => '2000-01-01 00:00:00',
				]);
		}

		$notify = false;

		foreach($demos as $Demo) {
			if ($Demo->demoStatus()=='PENDING') {
				$notify = true;
			}
		}


		$Node->update([
			'nodeLastSeen' => Util::time_now(),
			]);


		$result = [
			'result' 	=> 'OK',
			'demos'  	=> $demos->getArrayCopy(),
			'new_sites' => $notify,
		];
	}else{
		$result = [
			'result' => 'ERROR',
			'message' => $message,
			'header' => (isset($_SERVER['HTTP_X_ATC_CLIENT']) ? $_SERVER['HTTP_X_ATC_CLIENT'] : 'Missing'),
		];
	}


	