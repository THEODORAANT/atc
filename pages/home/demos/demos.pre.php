<?php

	$Dashboard   = Factory::get('Dashboard');

	$Demos = Factory::get('Demos');
	$demos = $Demos->get_headline_stats();

	//Console::log($demos);

	$today_start = date('Y-m-d 00:00:00');
	$today_end   = date('Y-m-d 23:59:59');

	$yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 DAY'));
	$yesterday_end   = date('Y-m-d 23:59:59', strtotime('-1 DAY'));

	$month_start = date('Y-m-01 00:00:00');
	$month_end   = date('Y-m-d 23:59:59');

	$Page->title = 'Demos';