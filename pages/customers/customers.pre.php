<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);


	$Customers = Factory::get('Customers');

	$with_count = true;

	switch($Page->arg(1)) {

		case 'perch':
			$customers = $Customers->get_with_product(PROD_PERCH, $Paging);
			break;

		case 'runway':
			$customers = $Customers->get_with_product(PROD_RUNWAY, $Paging);
			break;

		case 'runwaydev':
			$customers = $Customers->get_with_product(PROD_RUNWAYDEV, $Paging);
			break;

		default:
			$customers = $Customers->get_all_not_prospects($Paging);
			$with_count = false;
			break;

	}

	
