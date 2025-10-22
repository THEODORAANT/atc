<?php
	$Countries = Factory::get('Countries');
	$Country   = $Countries->find($Page->arg(1));

	$Form = Factory::get('Form', 'update');

	$req = array();
	$req['countryVATRate'] = "Required";
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['countryVATRate', 'countryXeroTaxType'];
		$data     = $Form->receive($postvars);
		
		$Country->update($data);

		Alert::set('success', 'Rate updated. <a href="/tax/">Back to list</a>');
		
	}


	$details = $Country->to_array();
