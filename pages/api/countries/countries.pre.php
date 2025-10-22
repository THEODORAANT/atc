<?php
	/*
		Get a list of countries and US states

	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
	
		$Countries = Factory::get('Countries');

		$countries = $Countries->get_all();
		$us_states = $Countries->get_us_states();

		$result = [
			'result'  		=> 'OK',
			'status'		=> '200',
			'countries'		=> $countries,
			'us_states'		=> $us_states,
		];
		
	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
