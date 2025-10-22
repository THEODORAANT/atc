<?php

use \Stripe\Stripe;
use \Stripe\Invoice as Stripe_Invoice;
use \Stripe\Webhook as Stripe_Webhook;

class StripeWebhook2021
{

    private $webhook_secret;
    
	public function __construct()
	{
    	$Conf = Conf::fetch();

    	if ($Conf->payment_gateway['test_mode']) {
            Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
            $this->webhook_secret = $Conf->stripe['keys']['test']['webhook_key'];
		}else{
            Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
            $this->webhook_secret = $Conf->stripe['keys']['live']['webhook_key'];
		}
	}

    public function decode($payload, $header) {
        $event = Stripe_Webhook::constructEvent(
            $payload, $header, $this->webhook_secret
          );
        return $event;
    }
    
	public function handle($hook)
	{
	echo "handle";
	//print_r($hook);
		if ($hook) {
print_r($hook->type);
			switch($hook->type) {

				case 'charge.succeeded':
					return $this->charge_succeeded($hook->data->object);
					break;

                case 'checkout.session.completed':
                    return $this->checkout_session_completed($hook->data->object);
                    break;

                 case 'customer.subscription.updated':
                 	return $this->customer_subscription_updated($hook->data->object);
                    break;
                

			}
		}
	
	}


	private function customer_subscription_updated($obj)
    {
         print_r($obj);
         $sub_status = $obj->status;
         //$sub_status="past_due";
         $Subscriptions = Factory::get('Subscriptions');
         echo "change status";print_r($sub_status); echo "****";
         $Sub = $Subscriptions->get_one_by('subStripeID', $obj->id);print_r($obj);
         echo "charge2";
         print_r($Sub);
         if ($Sub) {
            if($sub_status=="canceled" or $sub_status=="unpaid" or $sub_status=="past_due" or $sub_status=="incomplete"){
            	$Sub->cancel_subcription($obj);
            }else if($sub_status=="active" ){
                  //echo "active rrrr";
                 $sub_arr=$Sub->to_array();
                 //print_r( $sub_arr);
                 if($sub_arr["subCancelled"]==1 ){
                    $Sub->reactivate_subcription($obj);
                 }

            }

         }else{
          return false;
         }


         return false;
    }

	private function checkout_session_completed($obj)
    {
        //file_put_contents('session_completed.log',print_r($obj, true)."\n\n");
        $StripePaymentIntents = Factory::get('StripePaymentIntents');
        print_r($obj);
        $charge = false;
        $count = 0;
       /* $PaymentIntent = $StripePaymentIntents->get_one_by('payment_intent', $obj->payment_intent);
        echo "PaymentIntent";
          print_r($PaymentIntent);
        if ($PaymentIntent) {
             $charge = json_decode($PaymentIntent->charge());
         }
         echo "charge";
          print_r($charge);*/
       /* while (!$charge && $count < 600) {
            $PaymentIntent = $StripePaymentIntents->get_one_by('payment_intent', $obj->payment_intent);
            if ($PaymentIntent) {
                $charge = json_decode($PaymentIntent->charge());        
            }
            $count++;
            sleep(1);
        }*/

        $orderRef = $obj->client_reference_id;

        if($obj->mode=="subscription"){

          $this->process_subscription_purchase($orderRef, $obj);
        }else{
             while (!$charge && $count < 600) {
                    $PaymentIntent = $StripePaymentIntents->get_one_by('payment_intent', $obj->payment_intent);
                            echo "PaymentIntent";
                              print_r($PaymentIntent);
                    if ($PaymentIntent) {
                        $charge = json_decode($PaymentIntent->charge());
                    }
                    $count++;
                    sleep(1);
                }
             echo "charge";
             print_r($charge);
          $this->process_one_off_purchase($orderRef, $charge);
        }
        

        
        return false;
    }

	private function charge_succeeded($obj)
	{
        //file_put_contents('change_succeeded.log',print_r($obj, true)."\n\n");
        echo "charge_succeeded";
		if ($obj->paid) {
		echo "invoice";
print_r($obj->invoice);
			// Find the invoice
			if ($obj->invoice) {
				$invoice = $this->find_invoice($obj->invoice);

				if ($invoice && $invoice->lines) {
					foreach($invoice->lines->data as $item) {
print_r($item);
						switch($item->type) {

							case 'subscription':
								return $this->process_subscription($item, $obj);
								break;

						}

					}
				}
			} else {
                // New payment, not a subscription. Store the charge
                echo "store_charge_against_payment_intent";
                $this->store_charge_against_payment_intent($obj);
            }

		}

		return false;
    }
    
    private function store_charge_against_payment_intent($charge)
    {
        $StripePaymentIntents = Factory::get('StripePaymentIntents');
        $StripePaymentIntents->create([
           'payment_intent' => $charge->payment_intent,
           'charge' => json_encode($charge),
        ]);    
    }
    
    private function process_one_off_purchase($orderRef, $charge)
    {
        $Orders = Factory::get('Orders');
        $Order = $Orders->get_one_by('orderRef', $orderRef);

        if ($Order) {
            $Order->complete_pending_order_with_stripe($charge);
        }
    }

     private function process_subscription_purchase($orderRef, $charge)
       {


        $subid=explode('-', $orderRef);
        echo "sss";print_r($subid);
        if( count($subid)==2){
                $Orders = Factory::get('Orders');
                $Order = $Orders->get_one_by('orderRef', $subid[1]);
        }

      // if ($Order) {


                 $Subscriptions = Factory::get('Subscriptions');
                   echo "subbb"; echo $subid[0];
                  $Sub = $Subscriptions->get_one_by('subID', $subid[0]);
                  echo "subbb222";
                  print_r($Sub);
                  if ($Sub) {
                  //"subscription": "sub_1KRyf1CXZLrznbwD714YEOJA",
                           $Sub->update_sub_from_stripe($charge);
                  }
         //  }
     }

	private function process_subscription($subscription, $charge)
	{
		$Subscriptions = Factory::get('Subscriptions');
echo "charge";print_r($subscription); echo "****";
		$Sub = $Subscriptions->get_one_by('subStripeID', $subscription->subscription);
		echo "charge2";
print_r($Sub);
		if ($Sub) {
			return $Sub->create_new_order_from_stripe($subscription, $charge);
		}else{
		 return false;
		}

		return false;
	}


	private function find_customer($customerID)
	{
		$Customers = Factory::get('Customers');
		return $Customers->find($customerID);
	}

	private function find_invoice($stripe_invoice_id)
	{
		return Stripe_Invoice::retrieve($stripe_invoice_id);
	}

}
