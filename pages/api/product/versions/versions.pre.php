<?php
	/*
		Output product versions (public - no sensitive data)
	*/
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	$Versions  = Factory::get('ProductVersions');
	$versions  = $Versions->get_by('versionMajor', 2, 'versionDate');

	if (Util::count($versions)) {

		$out = [];

		foreach($versions as $Version) {
			$out[] = [
				'product'=>$Version->versionName(),
				'version'=>$Version->versionCode(),
				'date'=>$Version->versionDate()
			];
		}

		$result = [
					'result' => 'OK',
					'status' => '200',
					'versions' => $out,
				];

	}
