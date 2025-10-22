<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);


	$Orders = Factory::get('Orders');

	$Orders->count_matching_tax_evidence();
	
	$orders = $Orders->get_with_missing_evidence($Paging);
