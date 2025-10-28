#!/usr/bin/env php
<?php
  c
	use \Stripe\Charge as Stripe_Charge;



      use \Stripe\Customer as Stripe_Customer;
    include(__DIR__.'/../env.php');


	if ($Conf->payment_gateway['test_mode']) {
		Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
	}else{
		Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
	}
      echo Stripe::getApiVersion();
	  //Stripe::setApiVersion("2020-08-27");

	    $DB = DB::fetch();

        $Orders    = Factory::get('Orders');
    $Customers = Factory::get('Customers');

       $count=0;
    $sql = 'Select c.* ,o.orderDate,c.customerFirstOrder  FROM dbATC.tblCustomers as c left join dbATC.tblOrders as o on c.customerID =o.customerID
    where o.orderDate is not Null and c.customerFirstOrder is  Null and  o.orderStatus="PAID"';
    $customers = $DB->get_rows($sql);
echo "updtae first order date";
    if (Util::count($customers)) {
        foreach($customers as $customer) {
 //print_r($customer);
           if($customer['customerStripeID']==null){
            echo  $customer['customerEmail'];


             $cus2=Stripe_Customers::all(['email' =>  $customer["customerEmail"]]);

            // print_r($cus);
        if(count($cus2["data"])){
               echo "<br/>222";
         print_r($cus2["data"]);
        }
         $sql3 = 'Select * FROM dbATC.tblStripePaymentIntents where charge LIKE "%'.$customer["customerEmail"].'%" ';
         $stripeIntents = $DB->get_rows($sql3);
         echo "3333";
        print_r($stripeIntents);
            }
              //$cus_transactions=Stripe_Charge::search(['customer'=>1638876642]);

        }

        }
       //	foreach($all_transactions["data"] as $data){

        //       if($data["status"]=="succeeded"){



?>
