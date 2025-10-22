<?php
	/*
		Get the details of a marketing campaign referral code.
	*/
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {

		$ref 			= filter_input(INPUT_POST, 'ref', FILTER_UNSAFE_RAW); 

		$ReferralCodes  = Factory::get('ReferralCodes');
		$ReferralCode  = $ReferralCodes->get_one_by('codeRef', $ref);

		if (is_object($ReferralCode)) {


			$result = [
						'result' => 'OK',
						'status' => '200',
						'code' => $ReferralCode->to_array(),
					];

		}

	}
