<?php
	$Demos 	   = Factory::get('Demos');
	$DemoUsers = Factory::get('DemoUsers');
	$Customers = Factory::get('Customers');


	$Form = Factory::get('Form', 'demo');

	$req = array();
	$req['demoSite']          = "Required";
	$req['demoVersion']       = "Required";
	$req['demoUsername']      = "Required";
	$req['demoPasswordClear'] = "Required";
	$req['demoNode']          = "Required";
	$req['demoProduct']       = "Required";
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['demoSite', 'demoVersion', 'demoUsername', 'demoNode', 'demoPasswordClear', 'demoProduct'];
		$data = $Form->receive($postvars);
		
		$firstname = $AuthenticatedUser->userFirstName();
		$lastname = $AuthenticatedUser->userLastName();
		$email = $AuthenticatedUser->userEmail();

		$Customer = $Customers->find_by_email($email);

		// Attempt to find existing Demo user
		$User = $DemoUsers->get_one_by('userEmail', $email);


		if (!is_object($User)) {

			// Create a new user
			$User = $DemoUsers->create([
				'userFirstName'        => $firstname,
				'userLastName'         => $lastname,
				'userEmail'            => $email,
				'userPermissionToMail' => '1',
				'userCreated'          => Util::time_now(),
				]);


		}else{

			// Update existing user with new info
			$User->update([
				'userFirstName'        => $firstname,
				'userLastName'         => $lastname,
				'userPermissionToMail' => '1',
				]);

		}

		$clear_pwd = $data['demoPasswordClear'];
		$Hasher    = Factory::get('PasswordHash', 8, true);
		$password  = $Hasher->HashPassword($clear_pwd); 

		$Demo = $Demos->create([
					'demoHost'      	=> '',
					'demoValidFrom'     => Util::time_now(),
					'demoValidTo'       => '2020-01-01 00:00:00',
					'demoCreated'       => Util::time_now(),
					'demoUsername'      => $data['demoUsername'],
					'demoPassword'      => $password,
					'demoPasswordClear' => $data['demoPasswordClear'],
					'demoStatus'        => 'PENDING',
					'demoNode'          => $data['demoNode'],
					'demoProduct'       => $data['demoProduct'],
					'demoSite'          => $data['demoSite'],
					'userID'            => $User->id(),
					'demoKey'			=> Util::generate_random_string(12),
					'demoVersion'    	=> $data['demoVersion'],
				]);

		if (is_object($Demo)) {

			$host = Util::urlify($lastname).$Demo->id();

			$Demo->update([
				'demoHost'      => $host,
			]);

			Alert::set('success', 'Demo created and queued.');

		}


		
	}


	$pending_demos = $Demos->get_pending_for_admin();