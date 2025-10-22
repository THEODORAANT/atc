<?php
	/*
		Get list of registered developers
	*/
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {

		$Developers  = Factory::get('RegisteredDevelopers');
		$devs = $Developers->get_listing();

		if (Util::count($devs)) {

			$array_developers = [];

			foreach($devs as $dev) {

				$array_developers[] = [
					'id'               => $dev->devID(),
					'title'            => $dev->devTitle(),
					'desc_raw'         => $dev->devDescRaw(),
					'desc'             => $dev->devDescHTML(),
					'url'              => $dev->devURL(),
					'logo'             => $dev->devLogo(),
					'location'         => $dev->devLocation(),
					'project_location' => $dev->devProjectLocation(),
					'takes_existing'   => ($dev->devTakesExistingProjects() ? true : false),
					'perch_projects'   => ($dev->devProductPerch() ? true : false),
					'runway_projects'  => ($dev->devProductRunway() ? true : false),

				];
			}

			$result = [
						'result' => 'OK',
						'status' => '200',
						'developers' => $array_developers,
					];

		}

	}






