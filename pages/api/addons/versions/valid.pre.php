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

	$Addons = Factory::get('Addons');
	$addons = $Addons->get_latest();

	//echo json_encode($addons);
    $return_addons=[];
	$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW);
    $license_key 	= filter_input(INPUT_POST, 'license_key', FILTER_UNSAFE_RAW);
    $version 	= filter_input(INPUT_POST, 'version', FILTER_UNSAFE_RAW);


	$Customer  = $Customers->find($customerID);

	if (is_object($Customer)) {

	    if ($Customer->check_session_token($token)) {

	    	$Licenses = Factory::get('Licenses');
        	$licenses = $Licenses->search($license_key);
             $ProductVersions = Factory::get('ProductVersions');
            foreach($licenses as $License) {

                $ProductVersion  = $ProductVersions->get_latest($License->productID());
                if($ProductVersion->versionMajor()==4  && $License->subscriptionID()!=null ){

                     $result = [
                     					'result'  		=> 'OK',
                     					'status'		=> '200'

                     				];


                }else{

                $result = [
					'result'  		=> 'EROOR',
					'status'		=> '403',

				];


                }

            }



        }

    }
