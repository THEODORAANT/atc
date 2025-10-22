#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $DB = DB::fetch();

    $sql = 'SELECT customerID FROM tblCustomers WHERE (customerLastOrder IS NULL OR customerLastOrder="1970-01-01") AND customerFirstOrder IS NOT NULL';
    $customers = $DB->get_rows($sql);

    if (Util::count($customers)) {
        foreach($customers as $customer) {

            $sql = 'SELECT orderDate FROM tblOrders WHERE customerID='.$DB->pdb($customer['customerID']).' AND orderStatus=\'PAID\' AND orderRefund < orderItemsTotal 
                    ORDER BY orderDate DESC 
                    LIMIT 1';
            $order_date = $DB->get_value($sql);

            if ($order_date) {
                $DB->update('tblCustomers', array('customerLastOrder'=>date('Y-m-d', strtotime($order_date))), 'customerID', $customer['customerID']);    
            }


        }
    }