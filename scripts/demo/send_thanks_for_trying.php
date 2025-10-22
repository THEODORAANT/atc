#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

	$Demos = Factory::get('Demos');

	$demos = $Demos->get_current_older_than('perch', 24);

	$sent = 0;

	if (Util::count($demos)) {
		foreach($demos as $Demo) {
			if ($Demo->send_thanks_for_trying_email_to_customer()) {
				$sent++;
			}
		}
	}    

	echo $sent. " emails sent.\n";