<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(200);
	
	$Countries = Factory::get('Countries');
	$countries = $Countries->get_all($Paging);
