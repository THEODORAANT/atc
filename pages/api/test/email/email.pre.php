<?php

	Util::send_email('drew.mclellan@gmail.com', 'hello@grabaperch.com', 'hello@grabaperch.com', 'ATC email test', 'hello hello', false);

	$Conf = Conf::fetch();
	$Conf->debug = true;

	$Page->layout = 'json';
	$Conf->debug = false;

	$email_file   = 'demo_ready_details.html';

    $Email = Factory::get('Email', $email_file, $use_twig=true);
	$Email->senderEmail('hello@grabaperch.com');
	$Email->recipientEmail('drew.mclellan@gmail.com');
    $Email->set('site_string', 'Testing 123');
    $Email->send();

    echo json_encode([
    	'testing' => '123',
    	'debug' => Console::output(),

    ]);