<?php
	$query = filter_input(INPUT_GET, 'q');

	$Customers = Factory::get('Customers');
	$customers = $Customers->search($query);

	$Licenses = Factory::get('Licenses');
	$licenses = $Licenses->search($query);

	$Orders = Factory::get('Orders');
	$orders = $Orders->search($query);