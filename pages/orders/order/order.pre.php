<?php
	$Orders    = Factory::get('Orders');
	$Order     = $Orders->get_one_by('orderInvoiceNumber', $Page->arg(1));
	if(	$Order  == null){
	$Order     = $Orders->get_one_by('orderID', $Page->arg(1));
	}
	
	$Customers = Factory::get('Customers');
	$Customer  = $Customers->find($Order->customerID());
	
	$Evidence  = Factory::get('OrderTaxEvidenceItems');
	$evidence  = $Evidence->get_by('orderID', $Order->id());
	
	$Countries = Factory::get('Countries');
