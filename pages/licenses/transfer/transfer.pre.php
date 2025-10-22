<?php
	$Licenses = Factory::get('Licenses');
	$License  = $Licenses->get_one_by('licenseSlug', $Page->arg(1));

	$Customers = Factory::get('Customers');
	$Customer  = $Customers->find($License->customerID());

	$Orders = Factory::get('Orders');
	$Order  = $Orders->find($License->orderID());


	$Form = Factory::get('Form', 'transfer');

	$req = array();
	$req['new_owner_email']       = "Required";
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['new_owner_email'];
		$data = $Form->receive($postvars);
		
		$NewCustomer = $Customers->get_one_by('customerEmail', $data['new_owner_email']);

		if (!$NewCustomer) {
			Alert::set('danger', 'No customer with that email address');
		}else{

			$License->transfer_to($NewCustomer->id());

			Alert::set('success', 'License transferred.');
		}
		
	}

