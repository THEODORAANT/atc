<?php
	/*
		Get the Stripe keys

	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
    
        $pk = null;
        $sk = null;
        
		if ($Conf->stripe['enabled']) {

			if ($Conf->payment_gateway['test_mode']) {
                $pk = $Conf->stripe['keys']['test']['publishable'];
				$sk = $Conf->stripe['keys']['test']['secret'];
			}else{
                $pk = $Conf->stripe['keys']['live']['publishable'];
				$sk = $Conf->stripe['keys']['live']['secret'];
			}
			
		}

		$result = [
			'result'  		=> 'OK',
            'publishable'	=> $pk,
			'secret'        => $sk,
			'status'        => 200,
		];
		
	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
