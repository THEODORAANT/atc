<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(5);

	$Activations = Factory::get('Activations');
	$activations = $Activations->get_failures($Paging);
