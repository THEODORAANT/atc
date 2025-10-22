#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Customers = Factory::get('Customers');
    $customers = $Customers->get_for_tagging(1000);

    if (Util::count($customers)) {

    	foreach($customers as $Customer) {

    		echo 'Tagging '.$Customer->customerFirstName(). ' ' .$Customer->customerLastName();

            $Customer->update_order_interval();

    		/* License tags */

    			// Do they have a license?
    			$licenses_purchased = $Customer->get_license_purchase_count();

    			$tag = false;

    			if ($licenses_purchased==1) {
    				$tag = 'licenses:new';
    			}

    			if ($licenses_purchased>1 && $licenses_purchased<10) {
    				$tag = 'licenses:casual';	
    			}

    			if ($licenses_purchased>=10 && $licenses_purchased<=39) {
    				$tag = 'licenses:committed';	
    			}

    			if ($licenses_purchased>=40) {
    				$tag = 'licenses:super';	
    			}


    			if ($tag) {
    				$Customer->update_tag($tag);
    				echo ' as '.$tag;


    			}

    			echo PHP_EOL;

    		$Customer->update(['customerTagsUpdated'=>date('Y-m-d H:i:s')]);
    	}

    }

    //echo Console::output();
