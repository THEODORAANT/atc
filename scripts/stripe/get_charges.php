#!/usr/bin/env php
<?php
	use \Stripe\Stripe;
	use \Stripe\Charge as Stripe_Charge;

    include(__DIR__.'/../env.php');


	if ($Conf->payment_gateway['test_mode']) {
		Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
	}else{
		Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
	}

	    $DB = DB::fetch();

        $Orders    = Factory::get('Orders');
    $Customers = Factory::get('Customers');
       $orders    = $Orders->get_orders_for_stripe();

$all_transactions=Stripe_Charge::all(['created[gt]'=>1638876642]);
$count=0;

	foreach($all_transactions["data"] as $data){

        if($data["status"]=="succeeded"){

                foreach($orders as $order){
                echo $data["amount"];echo"-";
                  $stripeamount=$order->orderValue()*100;
                  $timestamp1 = strtotime($order->orderDate());
//&& strtoupper($order->orderCurrency())==strtoupper($data["currency"])


                  echo  $stripeamount; echo strtoupper($order->orderCurrency());
                  echo "CCCCC==";
                  echo strtoupper($data["currency"]);
                        if($stripeamount==$data["amount"] && strtoupper($order->orderCurrency())==strtoupper($data["currency"])){

                            $Customer   = $Customers->find($order->customerID());
                            //    echo $timestamp1;
                          // echo "-";echo $data["created"];echo "<br>\n";
                          echo "*************************";
                          echo $data["billing_details"]["email"];echo "emaill";
                           $email = $Customer->customerEmail();

                           echo $email;
                                    print_r($order);
                                  print_r($data);
                                  $count++;
                            echo "*************************";
                       /* if($timestamp1==$data["created"]){
                                    echo "*************************";
                                                print_r($order);
                                                print_r($data);
                                                echo "*************************";

                        }*/


                        }
                }
        }

	}

echo "total:  ";echo   $count;
Util::send_email('theodora@mooblu.com','hello@grabaperch.com', 'Perch Stripe Missmatch', 'Stripe webhook in input', "\n\n".$count.'');
