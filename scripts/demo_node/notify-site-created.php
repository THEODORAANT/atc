#!/usr/bin/env php
<?php
	// Config

	// This node
	$node_domain = 'perchlabs.net';

	// ATC
	$atc_url = 'https://atc.perchcms.com/api/demo/perch/site-created';
	

	if (!isset($argv[1])) die('Missing host argument.');

	$post = array(
		'secret' => 'cbbdfd55f07f37b1d05ccf1129e4c110',
		'node'   => $node_domain,
		'host'   => $argv[1],
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $atc_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-ATC-CLIENT: '.$node_domain));
	$result = curl_exec($ch);



	echo "\n\n\t" . $result . "\n\n";
