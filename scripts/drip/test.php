#!/usr/bin/env php
<?php
	use \DrewM\Drip\Drip;

    include(__DIR__.'/../env.php');

    $Customers = Factory::get('Customers');
    $Customer = $Customers->find(5);

    $params = [
    	'subscribers' => [
    		[
				'email' => $Customer->customerEmail(),
				'custom_fields' => [
									'first_name' => $Customer->customerFirstName(),
									'last_name'  => $Customer->customerLastName(),
								],
			]
		],
	];

	echo json_encode($params, JSON_PRETTY_PRINT);