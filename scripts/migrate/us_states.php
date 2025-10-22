#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Customers = Factory::get('Customers');
    $customers = $Customers->get_by('countryID', 163);

    if (Util::count($customers)) {
        foreach($customers as $Customer) {
            $Customer->update([
                'customerRegion' => $Customer->customerUsState(),
                ]);
        }
    }


    echo Console::output();