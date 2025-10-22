<?php
	/*
		Remove a customer from our lists. If they're a prospect, delete them also.
	*/
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Customers 	   = Factory::get('Customers');
		
		$dripID = filter_input(INPUT_POST, 'dripID', FILTER_UNSAFE_RAW); 
	
		// There could in theory be multiple customer rows for the same email address because legacy.
		$customers = $Customers->get_by('customerDripID', $dripID);

		if (Util::count($customers)) {
			foreach($customers as $Customer) {

				if ($Customer->customerIsProspect()=='1') {

					// customer is a prospect and doesn't want our mailings, so no point keeping them.
					// delete!

					$Customer->delete();

				}else{

					$Customer->unsubscribe_from_lists();
				}

			}

			$result = [
				'result' => 'OK',
				'status' => '200',
			];
		}else{
			$result = [
				'result' => 'ERROR',
				'status' => '404',
			];
		}

		

		
		

	}


	//$result['debug']  = Console::output();
