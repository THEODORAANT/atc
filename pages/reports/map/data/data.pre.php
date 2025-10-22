<?php
	$Page->layout = 'empty';
	$Conf->debug = false;

	header('Content-Type: application/javascript'); 

	$Customers = Factory::get('Customers');

	$customers = $Customers->get_for_map_report();