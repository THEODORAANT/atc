#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');
    include(__DIR__.'/../drip/suppression_list.php');

	include('mailchimp_list.php');

	if (Util::count($mailchimp_list)) {

		$Customers = Factory::get('Customers');

		foreach($mailchimp_list as $email) {

			$Customer = $Customers->find_by_email($email);

			if (!$Customer) {

				echo 'Creating ' . $email.PHP_EOL;
				
				$Customer = $Customers->create([
					'customerFirstName'	 => '',
					'customerLastName'	 => '',
					'customerEmail'	 	 => $email,
					'customerActive'	 => '1',
					'customerIsProspect' => '1',
					]);

				$Customer->tag_for_default_mailing_lists();
			
			}
		}
	}

	#echo Console::output();