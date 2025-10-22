<?php

use \Stripe\Stripe;
use \Stripe\Customer as Stripe_Customer;
use \Stripe\Charge as Stripe_Charge;
use \Stripe\Invoice as Stripe_Invoice;
use \Stripe\Subscription as Stripe_Subscription;
use \Stripe\BalanceTransaction as Stripe_BalanceTransaction;


class Subscription extends Core_Base 
{
	protected $table  = 'tblSubscriptions';
    protected $pk     = 'subID';

        public function update_sub_from_stripe($charge)
        {
        //echo "update_sub_from_stripe";
        //print_r($charge);
	      $this->update([
    		'subStripeID' => $charge->subscription
    		]);
        }

      /**
       * Send a subscription info email to customers
       * @param  [type] stripe_obj [description]
       * @return [type]            [description]
      */

      public function send_subscription_update_email($Customer ,$stripe_obj)
      {

          $template = 'subscription_update_email.html';
          $Email = Factory::get('Email', $template, $use_twig=true);
         // $Email->recipientEmail("theodora@mooblu.com");
          $Email->recipientEmail($Customer->customerEmail());
          $Email->set('subscription', $this->to_array());
          $Email->set('customer', $Customer->to_array());

          $Email->send();
       }

     /**
         * Send a subscription info email to customers
         * @param  [type] stripe_obj [description]
         * @return [type]            [description]
        */

     public function send_subscription_termination_email($Customer ,$stripe_obj)
     {

            $template = 'subscription_termination_email.html';
            $Email = Factory::get('Email', $template, $use_twig=true);
            //$Email->recipientEmail("theodora@mooblu.com");
            $Email->recipientEmail($Customer->customerEmail());
            $Email->set('subscription', $this->to_array());
            $Email->set('customer', $Customer->to_array());

            $Email->send();
      }

      public function send_subscription_reactivate_email($Customer)
      {

             $template = 'subscription_reactivate_email.html';
             $Email = Factory::get('Email', $template, $use_twig=true);
             //$Email->recipientEmail("theodora@mooblu.com");
             $Email->recipientEmail($Customer->customerEmail());
             $Email->set('subscription', $this->to_array());
             $Email->set('customer', $Customer->to_array());

             $Email->send();
       }

      public function reactivate_subcription($stripe_obj){
          $Customers = Factory::get('Customers');
          $Customer  = $Customers->find($this->customerID());
         /* $startepoch = $stripe_obj->current_period_start;
          $startdt = new DateTime("@$startepoch");*/
         // $endepoch = $stripe_obj->current_period_end;
         // $enddt = new DateTime("@$endepoch");


          $newEndDate = date('Y-m-d H:i:s', $stripe_obj->current_period_end);
       // echo "newEndDate"; echo  $newEndDate;
          $this->update([
             'subEnds'=> $newEndDate,
             'subCancelled' => 0
                 		]);

            $this->send_subscription_reactivate_email($Customer);
      }

     public function cancel_subcription($stripe_obj)
     {
          $sub_status = $stripe_obj->status;
          // $sub_status = "past_due";
          $Customers = Factory::get('Customers');
          $Customer  = $Customers->find($this->customerID());
          $this->update([
           		'subCancelled' => 1,
           		'subCancellationReason'=>"Stripe Update: ".$sub_status
           		]);
           	//	echo "*******************here";
           		//echo $sub_status;
           if($sub_status=="past_due"){
           		  $this->send_subscription_update_email($Customer ,$stripe_obj);
            }else if($sub_status=="canceled" or  $sub_status=="unpaid"){
                    $this->send_subscription_termination_email($Customer ,$stripe_obj);
            }


     }

    /**
      * Get a list of invoices for subStripeID
      * @param  integer subStripeID [description]
      * @return [type]             [description]
     */
     public function get_stripe_invoices_for_subscription($subStripeID)
         {

            $Conf = Conf::fetch();

            if ($Conf->payment_gateway['test_mode']) {
                Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
            }else{
                Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
            }

            $StripeInvoice = Stripe_Invoice::all(['subscription' =>$subStripeID]);
            return  $StripeInvoice->data;

     }

    public function create_new_order_from_stripe($subscription, $charge)
    {
    	$Baskets    = Factory::get('Baskets');
    	$Basket     = $Baskets->get_for_customer($this->customerID(), true);

    	if (!$Basket) return false;

    	$Basket->empty_contents();

    	$Basket->add($this->subItem(), $this->subQty());

    	$Plans  = Factory::get('SubscriptionPlans');
    	$Plan   = $Plans->find($this->planID());

       echo "Plan";
        print_r( $Plan);
        // get the invoice to balance the totals
        $StripeInvoice = Stripe_Invoice::retrieve($charge->invoice);
        echo "StripeInvoice";
        print_r( $StripeInvoice);

        if ($StripeInvoice->discount) {
            $Basket->basket_discounts[] = ['percentage' => (int)$StripeInvoice->discount->coupon->percent_off];
        }


    	$Orders = Factory::get('Orders');
    	$Order  = $Orders->create_pending($Basket, $this->customerID(), $Plan->planCurrency(), false,'stripe');


        GatewayLogger::log([
                'logGateway' => 'STRIPE',
                'orderID' => $this->id(),
                'logData' => json_encode((array)$charge),
                ]);


    	$balance = Stripe_BalanceTransaction::retrieve($charge->balance_transaction);

    	$rate = ((float)$charge->amount / (float)$balance->amount);

    	$Order->update([
    	    'orderStatus'         => 'PAID',
    	    'orderType'           => 'STRIPE',
    	    'orderStripeChargeID' => $charge->id,
    	    'orderFeesGBP'        => ($balance->fee/100),
    	    'orderCurrencyRate'   => $rate,
    	    'orderFundsAvailable' => date('Y-m-d H:i:s', $balance->available_on),
    	    'subscriptionID'	  => $this->id(),
    	]);


    	$Order->process_order();

        $StripeCharge = Stripe_Charge::retrieve($charge->id);

        $StripeCharge->metadata = [
                    'invoice_number'=>$Order->orderInvoiceNumber(),
                    'order_ref'=>$Order->orderRef(),
                ];
        $StripeCharge->save();


    	$newEndDate = date('Y-m-d H:i:s', $subscription->period->end);

    	$this->update([
    		'subEnds' => $newEndDate
    		]);

        // Log evidence
        try {
            $retrieved_charge = Stripe_Charge::retrieve($charge->id);
            if ($retrieved_charge) {
                if ($retrieved_charge->card && $retrieved_charge->card->country) {
                    $Countries = Factory::get('Countries');
                    $Country = $Countries->get_one_by('countryCode', $retrieved_charge->card->country);
                    $countryID = 0;
                    if ($Country) {
                        $countryID = $Country->id();
                    }

                    // Log Tax Evidence
                    $TaxEvidenceItems = Factory::get('OrderTaxEvidenceItems'); 
                    $TaxEvidenceItems->log($Order->id(), 'CARD_ADDRESS', $retrieved_charge->card->country, 'Stripe', $countryID);
                }
            }
        } catch (Exception $e) {

            GatewayLogger::log([
                'logGateway' => 'STRIPE',
                'orderID' => $Order->id(),
                'logData' => json_encode(['message'=>$e->getMessage()]),
            ]);

        }

    	return true;
    }

    public function cancel($reason)
    {
    	$Customers = Factory::get('Customers');
    	$Customer  = $Customers->find($this->customerID());
    	echo "subStripeID";
            echo $this->subStripeID();
    	if ($this->subStripeID()) {
    		
	    	$Conf = Conf::fetch();

	    	if ($Conf->payment_gateway['test_mode']) {
				Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
			}else{
				Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
			}

    		$subscription = Stripe_Subscription::retrieve($this->subStripeID());
    		//echo "Stripe_Subscription retrieve";
    		//print_r($subscription);
    		//echo "cancel--->";
    		$sub_cancelled= $subscription->cancel();
    		//print_r($sub_cancelled);
    		//echo $sub_cancelled->status;
    		if($reason==""){
    		  $reason=" Customer request";
    		}
    		if($sub_cancelled->status=="canceled"){
    			$this->update([
                		'subCancelled'=>1,
                		'subCancellationReason'=>$reason,
                		]);
    		}

    	}
    	


    	return true;
    }

    public function update_stripe_token($token)
    {
        $Customers = Factory::get('Customers');
        $Customer  = $Customers->find($this->customerID());

        $Conf = Conf::fetch();

        if ($Conf->payment_gateway['test_mode']) {
            Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
        }else{
            Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
        }

        $Stripe_Customer = Stripe_Customer::retrieve($Customer->customerStripeID());
        $Stripe_Sub      = $Stripe_Customer->subscriptions->retrieve($this->subStripeID());

        if ($Stripe_Sub) {
            $Stripe_Sub->source = $token;
            $Stripe_Sub->save();
            return true;
        }

        return false;
    }

}
