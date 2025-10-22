#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Customers = Factory::get('Customers');


    $customers = $Customers->get_by('customerToReviewAddress', 1);

    if (Util::count($customers)) {
        foreach($customers as $Customer) {
            if ($Customer->customerActive()) {
                $Customer->geocode();
                usleep(500000);        
            }
        }
    }


    echo Console::output();


