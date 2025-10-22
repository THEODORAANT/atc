<?php

	$Dashboard   = Factory::get('Dashboard');


	$today_start = date('Y-m-d 00:00:00');
	$today_end   = date('Y-m-d 23:59:59');

	$yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 DAY'));
	$yesterday_end   = date('Y-m-d 23:59:59', strtotime('-1 DAY'));

	$month_start = date('Y-m-01 00:00:00');
	$month_end   = date('Y-m-d 23:59:59');

	$last_month_start = date('Y-m-01 00:00:00', strtotime('-1 MONTH'));
	$last_month_end   = date('Y-m-31 23:59:59', strtotime('-1 MONTH'));

	$perch_today = $Dashboard->get_licenses_for_date('PERCH', $today_start, $today_end);
	$runway_today = $Dashboard->get_licenses_for_date('RUNWAY', $today_start, $today_end);

	$perch_downloads = $Dashboard->get_downloads_for_date(PROD_PERCH, $today_start, $today_end);
	$runway_downloads = $Dashboard->get_downloads_for_date(PROD_RUNWAY, $today_start, $today_end);

	$perch_local_licenses = $Dashboard->get_local_licenses_for_date('PERCH', $today_start, $today_end);
	$runway_local_licenses = $Dashboard->get_local_licenses_for_date('RUNWAY', $today_start, $today_end);

	$Page->title = 'ATC ('.$perch_today.'-'.$runway_today.')';