<?php
	/*
		Gets stats on demos for dashboard etc

	 */
	
	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = '18e8b2520e72ceb1339ebb007b8935e3';

	$result = false;

	if (filter_input(INPUT_GET, 'secret')==$secret) {
		$Demos = Factory::get('Demos');
		$result = $Demos->get_headline_stats();
	}

