<?php
	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);

	$Countries = Factory::get('Countries');
	$countries = $Countries->get_all();

	$Changes = Factory::get('CountryTaxChanges');

	$Form = Factory::get('Form', 'add');

	$req = array();
	$req['countryID']   = "Required";
	$req['changeValue'] = "Required";
	$req['changeDate']  = "Required";
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['countryID', 'changeValue', 'changeDate'];
		$data     = $Form->receive($postvars);
		
		$Change = $Changes->create($data);

		Alert::set('success', 'Rate change added.');
		
	}





	$changes = $Changes->get_by('changeApplied', NULL);
