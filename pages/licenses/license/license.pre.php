<?php
	$Licenses = Factory::get('Licenses');
	$License  = $Licenses->get_one_by('licenseSlug', $Page->arg(1));

	$Customers = Factory::get('Customers');
	$Customer  = $Customers->find($License->customerID());

	$Orders = Factory::get('Orders');
	$Order  = $Orders->find($License->orderID());
