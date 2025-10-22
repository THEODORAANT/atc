<?php
	/*
		Get details of latest download file and log if requested
	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		
		$product     = filter_input(INPUT_POST, 'product', FILTER_SANITIZE_STRING);
		$downloadReferrer= filter_input(INPUT_POST, 'downloadReferrer', FILTER_SANITIZE_STRING);
		$versionID= filter_input(INPUT_POST, 'versionID', FILTER_SANITIZE_STRING);
		$token 		 = filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW); 
		$log 		 = filter_input(INPUT_POST, 'log', FILTER_SANITIZE_STRING); 

		$Products = Factory::get('Products');
		$Product  = $Products->get_by_item_code($product);

		if (is_object($Product)) {

			$Versions = Factory::get('ProductVersions');
			$Version = $Versions->get_latest($Product->id());

			if ($Version) {
				$result = [
					'result'  		=> 'OK',
					'status'		=> '200',
					'product'		=> $Version->to_array(),
				];

				if ($log) {
					$Version->log_download($versionID,$downloadReferrer);
				}
			}
			
			
	
	
		}

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
