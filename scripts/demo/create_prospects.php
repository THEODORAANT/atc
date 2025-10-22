#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');
    include(__DIR__.'/../drip/suppression_list.php');

	$DemoUsers = Factory::get('DemoUsers');
	$Customers = Factory::get('Customers');

	$users = $DemoUsers->get_by('customerID', null);

	if (Util::count($users)) {
		foreach($users as $User) {

			$Customer = $Customers->find_by_email($User->userEmail());

			if (!$Customer) {

				echo 'Creating ' . $User->userFirstName().' '.$User->userLastName().PHP_EOL;
				
				$Customer = $Customers->create([
					'customerFirstName'	 => $User->userFirstName(),
					'customerLastName'	 => $User->userLastName(),
					'customerEmail'	 	 => $User->userEmail(),
					'customerActive'	 => '1',
					'customerIsProspect' => '1',
					]);

				if ($Customer) {
					$User->update(['customerID'=>$Customer->id()]);	
				}

				if ($User->userPermissionToMail()==1 && !in_array($User->userEmail(), $suppression_list)) {
					$Customer->tag_for_default_mailing_lists();
				}
				
			}
		}
	}

	#echo Console::output();