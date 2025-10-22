<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);


	$Customers = Factory::get('Customers');
	$customers = $Customers->get_top($Paging);
