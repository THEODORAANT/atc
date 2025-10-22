<?php
	$Orders = Factory::get('Orders');
	$Order  = $Orders->get_one_by('orderInvoiceNumber', $Page->arg(1));

	$Customers = Factory::get('Customers');
	$Customer = $Customers->find($Order->customerID());

	$OrderItems = Factory::get('OrderItems');
	$items = $OrderItems->get_for_order($Order->id());