<?php
	/*
		Gets the status of a demo by host name and key

	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['demo'];

	$result = [
		'result' => 'ERROR',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Demos 	   = Factory::get('Demos');
		$DemoUsers 	   = Factory::get('DemoUsers');

		$host        = filter_input(INPUT_POST, 'host', FILTER_SANITIZE_STRING);
		$key         = filter_input(INPUT_POST, 'key', FILTER_SANITIZE_STRING);

		$Demo = $Demos->get_one_by('demoHost', $host);

		if (is_object($Demo)) {

			$DemoUser = $DemoUsers->find($Demo->userID());

			if ($Demo->demoKey()==$key) {

				if ($Demo->demoStatus()=='LIVE') {
					$out = [
						'status'     => 'LIVE',
						'url'        => $Demo->url(),
						'login_url'  => $Demo->login_url(),
						'username'   => $Demo->demoUsername(),
						'password'   => $Demo->demoPasswordClear(),
						'expires'    => $Demo->demoValidTo(),
						'site'       => $Demo->demoSite(),
						'first_name' => $DemoUser->userFirstName(),
						'last_name'  => $DemoUser->userLastName(),
						'email'      => $DemoUser->userEmail(),
					];
				}else{
					$out = ['status'=>$Demo->demoStatus()];
				}


				if (Util::count($out)) {
					$result = [
						'result' => 'OK',
						'site'  => $out,
					];
				}

			}

		}

	}
