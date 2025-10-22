#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Customers = Factory::get('Customers');
    $customers = $Customers->get_all();

    if (Util::count($customers)) {

    	foreach($customers as $Customer) {

    		echo 'Updating '.$Customer->customerFirstName(). ' ' .$Customer->customerLastName().PHP_EOL;

            $Customer->update_order_interval();

    	}

    }

    //echo Console::output();
