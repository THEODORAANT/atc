#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

	$DemoUsers = Factory::get('DemoUsers');
	$Customers = Factory::get('Customers');

	$users = $DemoUsers->get_by('customerID', null);

	if (Util::count($users)) {
		foreach($users as $User) {

			echo $User->userFirstName().' '.$User->userLastName().PHP_EOL;

			$Customer = $Customers->find_by_email($User->userEmail());

			if ($Customer) {
				$User->update(['customerID'=>$Customer->id()]);
			}
		}
	}

	#echo Console::output();