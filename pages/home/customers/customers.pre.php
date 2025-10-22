<?php

	$Dashboard   = Factory::get('Dashboard');


	$today_start = date('Y-m-d 00:00:00');
	$today_end   = date('Y-m-d 23:59:59');

	$yesterday_start = date('Y-m-d 00:00:00', strtotime('-1 DAY'));
	$yesterday_end   = date('Y-m-d 23:59:59', strtotime('-1 DAY'));

	$month_start = date('Y-m-01 00:00:00');
	$month_end   = date('Y-m-d 23:59:59');

	$Page->title = 'Customer Dashboard';

	$Paging = Factory::get('Paging');
	$Paging->set_per_page(20);

	$Customers = Factory::get('CustomersReports');
	$customers = $Customers->get_tag_activity($Paging);