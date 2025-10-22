<?php

	$CustomersReports = Factory::get('CustomersReports');

	$tag = 'licenses:super';
	$nav = 'new';
	$heading = 'New customers';

	if ($Page->arg('tag')) {
		$tag = 'licenses:'.$Page->arg('tag');
		$nav = $Page->arg('tag');

		switch ($Page->arg('tag')) {
			case 'new':
				$heading = 'New customers';
				break;
			case 'casual':
				$heading = 'Casual customers';
				break;
			case 'committed':
				$heading = 'Committed customers';
				break;
			case 'super':
				$heading = 'Super customers';
				break;


		}
	}

	$customers = $CustomersReports->get_report_by_tag($tag);

