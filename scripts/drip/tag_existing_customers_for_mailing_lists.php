#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    include('suppression_list.php');
    include('mailchimp_list.php');


    $continue = true;

    $Customers = Factory::get('Customers');
    $customers = $Customers->get_by_sql('SELECT * FROM tblCustomers WHERE customerIsProspect=0 and customerID NOT IN (
                        SELECT customerID FROM tblCustomerTags WHERE tag="list:newsletter"
                    ) ORDER BY customerID');

    if (Util::count($customers)) {
        foreach($customers as $Customer) {
            if (!in_array($Customer->customerEmail(), $suppression_list)) {
                if (in_array($Customer->customerEmail(), $mailchimp_list)) {
                    echo $Customer->customerFirstName().' '.$Customer->customerLastName().PHP_EOL;
                    $Customer->tag_for_default_mailing_lists();    
                }
            }
        }
    }
    



    //echo Console::output();
