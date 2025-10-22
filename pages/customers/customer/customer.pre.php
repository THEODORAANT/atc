<?php
	$Customers = Factory::get('Customers');
	$Customer  = $Customers->find($Page->arg(1));

	$Orders = Factory::get('Orders');
	$orders  = $Orders->get_by('customerID', $Customer->id());

	$Licenses = Factory::get('Licenses');
	$licenses  = $Licenses->get_by('customerID', $Customer->id(), 'licenseDate DESC');

	$CustomerReports = Factory::get('CustomersReports');
	$activity = $CustomerReports->get_tag_activity(false, $Customer->id());