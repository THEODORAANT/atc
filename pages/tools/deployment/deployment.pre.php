<?php
	$ProductVersions = Factory::get('ProductVersions');

	$Form = Factory::get('Form', 'deploy');

	$req = array();
	$req['versionCode']       = "Required";
	//$req['perchPriceGBP']     = "Required";
	//$req['perchPriceUSD']     = "Required";
	//$req['perchPriceEUR']     = "Required";
	$req['runwayPriceGBP']    = "Required";
	$req['runwayPriceUSD']    = "Required";
	$req['runwayPriceEUR']    = "Required";
	//$req['runwaydevPriceGBP'] = "Required";
	//$req['runwaydevPriceUSD'] = "Required";
	//$req['runwaydevPriceEUR'] = "Required";
	
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['versionCode',  'runwayPriceGBP', 'runwayPriceUSD', 'runwayPriceEUR',  'versionOnSale', 'versionAnnounce'];
		$data = $Form->receive($postvars);
	

		$basic_data = [
			'versionVCSTag'   => 'v'.$data['versionCode'],
			'versionCode'     => $data['versionCode'],
			'versionDate'     => date('Y-m-d H:i:s'),
			'versionMajor'    => '4',
			'versionOnSale'   => (isset($data['versionOnSale']) ? '1' : '0'),
			'versionAnnounce' => (isset($data['versionAnnounce']) ? '1' : '0'),
		];

		$versions = [];


		// Perch
		/*$perch = $basic_data;
		$perch['productID']       = 1;
		$perch['versionName']     = 'Perch';
		$perch['versionPriceGBP'] = $data['perchPriceGBP'];
		$perch['versionPriceUSD'] = $data['perchPriceUSD'];
		$perch['versionPriceEUR'] = $data['perchPriceEUR'];
		$perch['versionFileName'] = 'perch_v'.$data['versionCode'].'.zip';

		$versions[] = $ProductVersions->create($perch);*/


		// Runway
		$runway = $basic_data;
		$runway['productID']       = 4;
		$runway['versionName']     = 'Perch Runway';
		$runway['versionPriceGBP'] = $data['runwayPriceGBP'];
		$runway['versionPriceUSD'] = $data['runwayPriceUSD'];
		$runway['versionPriceEUR'] = $data['runwayPriceEUR'];
		$runway['versionFileName'] = 'runway_v'.$data['versionCode'].'.zip';

		$versions[] = $ProductVersions->create($runway);


		// Runway Developer
		/*$runwaydev = $basic_data;
		$runwaydev['productID']       = 10;
		$runwaydev['versionName']     = 'Perch Runway Developer';
		$runwaydev['versionPriceGBP'] = $data['runwaydevPriceGBP'];
		$runwaydev['versionPriceUSD'] = $data['runwaydevPriceUSD'];
		$runwaydev['versionPriceEUR'] = $data['runwaydevPriceEUR'];
		$runwaydev['versionFileName'] = 'runway_v'.$data['versionCode'].'.zip';

		$versions[] = $ProductVersions->create($runwaydev);*/


		if (count($versions)==4) {

			// update older versions to new files

			foreach($versions as $ProductVersion) {
				$ProductVersion->set_as_current_download();
			}			


			Alert::set('success', 'Version created!');

		}


		
	}
