<?php
	/*
		Create a new demo
	*/

	ini_set('display_errors', 'On');
	error_reporting(E_ALL);
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['demo'];

	$result = [
		'result' => 'ERROR',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Demos 	   = Factory::get('Demos');
		$DemoUsers = Factory::get('DemoUsers');
		$Customers = Factory::get('Customers');

    	// Grab data in
		$firstname        = $_POST['firstname'];
		$lastname         = $_POST['lastname'];

		// simple spam prevention - names shouldn't contain links
		if (strpos($firstname, 'http://')!==false || strpos($lastname, 'http://')!==false) {
			echo json_encode($result);
			die;
		}


		$email            = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
		$permissionToMail = (bool) filter_input(INPUT_POST, 'permissiontomail', FILTER_SANITIZE_NUMBER_INT);
		$sites 			  = explode(',', filter_input(INPUT_POST, 'sites', FILTER_SANITIZE_STRING));
		$referral		  = trim(filter_input(INPUT_POST, 'referrer', FILTER_SANITIZE_STRING));


		// Find or create new customer as a Prospect account
		$Customer = $Customers->find_by_email($email);
		if (!is_object($Customer)) {
			$Customer = $Customers->create([
				'customerFirstName'	 => $firstname,
				'customerLastName'	 => $lastname,
				'customerEmail'	 	 => $email,
				'customerActive'	 => '1',
				'customerIsProspect' => '1',
				]);
		}

		// Attempt to find existing Demo user
		$User = $DemoUsers->get_one_by('userEmail', $email);
	
		if (!is_object($User)) {

			// Create a new user
			$User = $DemoUsers->create([
				'userFirstName'        => $firstname,
				'userLastName'         => $lastname,
				'userEmail'            => $email,
				'userPermissionToMail' => ($permissionToMail ? '1' : '0'),
				'userCreated'          => Util::time_now(),
				]);


		}else{

			// Update existing user with new info
			$User->update([
				'userFirstName'        => $firstname,
				'userLastName'         => $lastname,
				'userPermissionToMail' => ($permissionToMail ? '1' : '0'),
				]);

		}

		// Add referral
		
		if ($referral!='') {
			$User->log_referral($referral);
		}
		


		// Add user to mailing?
		if ($permissionToMail) {
			MailingList::add_user($email, $firstname, $lastname, 'Demo');
		}




		$out = [];

		// Create the demos
		if (Util::count($sites)) {

			

			// Username
			$username = Util::urlify($firstname).'.'.Util::urlify($lastname);

			// Password
			$clear_pwd = $Demos->generate_password();
			$Hasher    = Factory::get('PasswordHash', 8, true);
			$password  = $Hasher->HashPassword($clear_pwd);    


			foreach($sites as $site) {
				$Demo = $Demos->create([
					'demoHost'      	=> '',
					'demoValidFrom'     => Util::time_now(),
					'demoValidTo'       => '2020-01-01 00:00:00',
					'demoCreated'       => Util::time_now(),
					'demoUsername'      => $username,
					'demoPassword'      => $password,
					'demoPasswordClear' => $clear_pwd,
					'demoStatus'        => 'PENDING',
					'demoNode'          => '*',
					'demoProduct'       => 'perch',
					'demoSite'          => trim($site),
					'userID'            => $User->id(),
					'demoKey'			=> Util::generate_random_string(12),
				]);

				if (is_object($Demo)) {

					$host = Util::urlify($lastname).$Demo->id();

					$Demo->update([
						'demoHost'      => $host,
					]);

					$out[] = ['host'=>$host, 'key'=>$Demo->demoKey()];

				}
			}
		}

		if (Util::count($out)) {
			$result = [
				'result' => 'OK',
				'demos'  => $out,
			];
		}


	}
	

