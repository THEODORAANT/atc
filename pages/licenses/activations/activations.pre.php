<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);

	$Licenses = Factory::get('Licenses');
	$License  = $Licenses->get_one_by('licenseSlug', $Page->arg(1));

	$Activations = Factory::get('Activations');
	$activations = $Activations->get_for_license($License->licenseKey(), $Paging);
