#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $DB = DB::fetch();

    //$sql = 'SELECT customerID FROM tblCustomers WHERE customerFirstOrder IS NULL';
    $sql = 'Select c.customerID ,o.orderDate,c.customerFirstOrder  FROM dbATC.tblCustomers as c left join dbATC.tblOrders as o on c.customerID =o.customerID
    where o.orderDate is not Null and c.customerFirstOrder is  Null';
    $customers = $DB->get_rows($sql);
echo "updtae first order date";
    if (Util::count($customers)) {
        foreach($customers as $customer) {

            $sql2 = 'SELECT orderDate FROM tblOrders WHERE customerID='.$DB->pdb($customer['customerID']).' AND orderStatus=\'PAID\' AND orderRefund < orderItemsTotal
                    ORDER BY orderDate ASC 
                    LIMIT 1';
                    echo  $sql2 ; echo "<br/>";
            $order_date = $DB->get_value($sql2);
     // echo "customerID";
 // echo  $customer['customerID'];
echo $order_date;
            if ($order_date) {
           // echo  $customer['customerID'];
            echo date('Y-m-d', strtotime($order_date));
                $DB->update('tblCustomers', array('customerFirstOrder'=>date('Y-m-d', strtotime($order_date))), 'customerID', $customer['customerID']);
            }

            

        }
    }
