<?php
	$Countries = Factory::get('Countries');
	$countries = $Countries->get_all();

	$Changes = Factory::get('CountryTaxChanges');
	$Change  = $Changes->find((int)$Page->arg(1));

	$Form = Factory::get('Form', 'update');

	$req = array();
	$req['countryID']   = "Required";
	$req['changeValue'] = "Required";
	$req['changeDate']  = "Required";
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['countryID', 'changeValue', 'changeDate'];
		$data     = $Form->receive($postvars);
		
		$Change->update($data);

		Alert::set('success', 'Rate change updated.');
		
	}


	$RmForm = Factory::get('Form', 'rm');


	if ($RmForm->posted() && $RmForm->validate()) {
		$Change->delete();
		Util::redirect('/tax/changes/');
	}

	$details = $Change->to_array();
