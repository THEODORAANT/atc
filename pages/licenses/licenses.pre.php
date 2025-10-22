<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);


	$Licenses = Factory::get('Licenses');

	switch($Page->arg(1)) {

		case 'perch':
			$licenses = $Licenses->get_by('productID', PROD_PERCH, false, $Paging);
			break;

		case 'runway':
			$licenses = $Licenses->get_by('productID', PROD_RUNWAY, false, $Paging);
			break;

		case 'runwaydev':
			$licenses = $Licenses->get_by('productID', PROD_RUNWAYDEV, false, $Paging);
			break;

		default:
			$licenses = $Licenses->get_all($Paging);
			break;

	}


	
