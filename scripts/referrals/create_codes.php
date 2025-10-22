#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');
    
    $DB = Factory::get('DB');

    $Customers     = Factory::get('Customers');

    // Get customers who do not have a code
    $sql = 'SELECT customerID FROM tblCustomers WHERE customerReferralCode = "" OR customerReferralCode IS NULL';
    $rows = $DB->get_rows($sql);

    if (Util::count($rows)) {
    	foreach($rows as $customer) {
            $Customer = $Customers->find($customer['customerID']);
            $Customer->generate_referral_code();
    	}
    }


    echo Console::output();