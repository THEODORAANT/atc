#!/usr/bin/env php
<?php
      use \Stripe\Stripe;
    	use \Stripe\Customer as Stripe_Customers;
    use \Stripe\Invoice as Stripe_Invoice;

    include(__DIR__.'/../env.php');


		Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);

    $Customers 	   = Factory::get('Customers');
	$Customer  = $Customers->find(30735);
	  //print_r($Customer);
/* $Stripe_Customer = StripeGateway::get_customer($Customer);
 print_r($Stripe_Customer);*/

// $Sub = $Customer->get_subscription(83);

  /*  $Subs = Factory::get('Subscriptions');
    $Sub = $Subs->get_one_for_customer(30735, 83);

print_r($Sub); echo "<br/>";
$invoices= $Sub->get_stripe_invoices_for_subscription($Sub->subStripeID());
print_r($invoices);*/
 //$StripeInvoice = Stripe_Invoice::search(['query' => 'subscription:sub_1KnOV2CXZLrznbwDg6kaZusE']);
 echo "subb";
  //$StripeInvoice = Stripe_Invoice::all(['subscription' => 'sub_1KnOV2CXZLrznbwDg6kaZusE']);
// print_r($StripeInvoice);

 $Subs = Factory::get('Subscriptions');
$Sub =$Subs->get_one_for_customer(30735, 79);
  print_r($Sub);
   echo "invoices";
  $invoices= $Sub->get_stripe_invoices_for_subscription($Sub->subStripeID());
 //print_r($invoices);
 	$tmp = $invoices;//->to_array();
$out[] = $tmp;
 print_r($out);
