#!/usr/bin/env php
<?php

    include(__DIR__.'/../env.php');

	$DB = DB::fetch();

	$LocalLicenses = Factory::get('LocalLicenses');
	$Customers = Factory::get('Customers');

	$licenses = $LocalLicenses->get_by('customerID', null);

	if (Util::count($licenses)) {
		foreach($licenses as $License) {
			$License->attempt_to_match_with_customer();

			if (!$License->customerID()) {

				$data = [
					'customerEmail' => $License->licenseEmail(),
				];

				$Customer = $Customers->create_prospect($data);

				if ($Customer) {
					$License->update([
						'customerID' => $Customer->id(),
						]);


					// catch-up for missed customers? (prevents emails being triggered)
					if (strtotime($License->licenseDate()) < strtotime('-7 DAYS')) {
						$Customer->tag('install:catchup');
					}


					if (substr($License->licenseKey(), 0, 1) == 'R') {
						$Customer->tag('install:runway');	
					} else {
						$Customer->tag('install:perch');	
					}
					
				}
			}
		}
	}

	// count activations on unactivated licenses

	$licenses = $LocalLicenses->get_unactivated('1 MONTH');

	if (Util::count($licenses)) {
		foreach($licenses as $License) {

			$activations = $License->count_activations();

			if ($activations > 0) {
				$License->update([
					'licenseActivated' => 1,
				]);

				$Customer = $Customers->find($License->customerID());

				if ($Customer) {
					$Customer->tag('install:login');
				}
			}
		}
	}

	#Console::output();