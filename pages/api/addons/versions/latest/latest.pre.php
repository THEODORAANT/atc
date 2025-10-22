<?php

	$Page->layout = 'json';
	$Conf->debug = false;

	$Addons = Factory::get('Addons');
	$addons = $Addons->get_latest();

	echo json_encode($addons);