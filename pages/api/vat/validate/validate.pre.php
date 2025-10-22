<?php
	/*
		Validate a VAT number

	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		
		$vat_number     = filter_input(INPUT_POST, 'vat_number', FILTER_SANITIZE_STRING);

		$vat_number 	= str_replace(' ', '', $vat_number);
        $vat_number 	= str_replace('-', '', $vat_number);
        $vat_number		= strtoupper($vat_number);
        
        if (strlen($vat_number) > 4) {


        	$countries = [ 'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'EL', 'ES', 'FI', 'FR', 'GB', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];

        	$country = substr($vat_number, 0, 2);

        	if (in_array($country, $countries)) {

        		$url = 'http://www.apilayer.net/api/validate?access_key='.$Conf->vat_api_key.'&vat_number='.$vat_number;
        		
        		$ch = curl_init();
	            curl_setopt($ch, CURLOPT_URL, $url);             
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	            $curl_result = curl_exec($ch);
	            curl_close($ch);

	            if ($curl_result) {

	            	$json = json_decode($curl_result);

	            	

	            	if (isset($json->valid) && $json->valid) {
	            		$result = [
							'result'  		=> 'OK',
							'status'		=> '200',
							'vat_number'	=> $json->country_code.$json->vat_number,
						];
	            	}else{
	            		
	            		if (isset($json->database) && $json->database == 'failure') {
	            			$result = [
								'result'  		=> 'ERROR',
								'status'		=> '501',
								'message'		=> 'We are unable to validate the VAT number at this time due to the member state database being offline. You can either try again later, or proceed without a VAT number.',
							];
	            		}else{
	            			$result = [
								'result'  		=> 'ERROR',
								'status'		=> '403',
								'message'		=> 'The European VAT validation service is reporting that this is not a valid VAT number.',
							];
	            		}

	            	}

	            }else{
	            	$result = [
						'result'  		=> 'ERROR',
						'status'		=> '500',
						'message'		=> 'We are unable to validate VAT numbers at this time. You can either try again later, or proceed without a VAT number.',
					];
	            }

        	}else{
        		$result = [
					'result'  		=> 'ERROR',
					'status'		=> '404',
					'message'		=> 'Your VAT number does not begin with a valid two-letter country code.',
				];
        	}



        	
        }
 

        

				

		

	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
