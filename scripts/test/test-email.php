#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

	$Demos = Factory::get('Demos');

	$Demo = $Demos->find('5715');

	$Demo->send_welcome_email_to_customer();


	echo Console::output();