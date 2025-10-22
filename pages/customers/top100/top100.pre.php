<?php

	$Customers = Factory::get('Customers');
	$customers = $Customers->get_top_100();
