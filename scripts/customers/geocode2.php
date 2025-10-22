#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Customers = Factory::get('Customers');


    // $customers = $Customers->get_by('customerLat', null);

    // if (Util::count($customers)) {
    //     foreach($customers as $Customer) {
    //         $Customer->geocode();
    //         usleep(500000);    
    //     }
    // }

    $Customer = $Customers->find('7648');
    $Customer->geocode();

   

    echo Console::output();


