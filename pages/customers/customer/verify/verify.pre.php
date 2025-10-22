<?php
	$Customers = Factory::get('Customers');
	$Customer  = $Customers->find($Page->arg(1));

	$Form = Factory::get('Form', 'verify');

	$req = array();
	$req['customerAdrManuallyVerified']       = "Required";
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['customerAdrManuallyVerified'];
		$data = $Form->receive($postvars);
		$data['customerToReviewAddress'] = '0';
		$data['customerNeedsGeocoding'] = '0';
		
		$Customer->update($data);

		Alert::set('success', 'Address verified. <a href="/customers/customer/'.$Customer->id().'">Back to customer</a>');
		
	}

