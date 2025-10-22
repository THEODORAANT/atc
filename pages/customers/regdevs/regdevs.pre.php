<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);

	$Regdevs = Factory::get('RegisteredDevelopers');
	$active = $Regdevs->get_active();
	$lapsed = $Regdevs->get_lapsed();
