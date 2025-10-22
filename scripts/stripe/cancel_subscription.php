#!/usr/bin/env php
<?php

    include(__DIR__.'/../env.php');
    $Customers 	   = Factory::get('Customers');
 	$Customer  = $Customers->find(30735);
 	$Sub = $Customer->get_subscription(79);
 	//  $Stripe_Sub      = $Stripe_Customer->subscriptions->retrieve($this->subStripeID());
   if (is_object($Sub)) {
            print_r($Sub);
    	if ($Sub->cancel($reason)){

        }
   }

    echo Console::output();
