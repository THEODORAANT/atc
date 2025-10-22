<?php

use \Stripe\Stripe;
use \Stripe\Customer as Stripe_Customer;
use \Stripe\Charge as Stripe_Charge;

class StripeGateway implements PaymentGateway
{
	public $name = 'STRIPE';


	public function register_transaction(Order $Order, callable $cb_failure)
	{
		// Stripe's interface is a bit different - doesn't use these methods like a traditional gateway
	}

	public function get_updated_order_values(array $response)
	{
		// Stripe's interface is a bit different - doesn't use these methods like a traditional gateway
	}

	public function set_result_url($url)
	{
		// Stripe's interface is a bit different - doesn't use these methods like a traditional gateway
	}

	public function process_refund(Order $Order)
	{
    	$Conf = Conf::fetch();

    	if ($Conf->payment_gateway['test_mode']) {
			Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
		}else{
			Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
		}

		if ($Order->orderStripeChargeID()!='') {
			$Charge = Stripe_Charge::retrieve($Order->orderStripeChargeID());
			if ($Charge) {


				$amount = intval(((float)$Order->orderRefund() + (float)$Order->orderVATrefund())*100);

				try {
					$Refund = $Charge->refund([
						'amount' => $amount,
					]);		
				} catch (Exception $e) {

					GatewayLogger::log([
					    'logGateway' => 'STRIPE',
					    'orderID' => $Order->id(),
					    'logData' => json_encode(['message'=>$e->getMessage()]),
					]);

					$Refund = false;

				}

				if ($Refund) {

					GatewayLogger::log([
					    'logGateway' => 'STRIPE',
					    'orderID' => $Order->id(),
					    'logData' => json_encode($Refund),
					]);

					return true;
				}
			}
			
		}

		return false;
	}

	public static function get_customer($Customer, $token=false)
	{
    	$Conf = Conf::fetch();

    	if ($Conf->payment_gateway['test_mode']) {
			Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
		}else{
			Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
		}
        //echo "get customer";
    	// Get existing Stripe customer, or create new
    	if ($Customer->customerStripeID()) {
    	//echo "existing";
			$stripe_customer_id = $Customer->customerStripeID();
			$customer           = Stripe_Customer::retrieve($stripe_customer_id);
			//$customer->source     = $token;
			$customer->email    = $Customer->customerEmail();
			$customer->save();
    	}else{
    	//echo "new";echo $Customer->customerEmail();
    		$customer = Stripe_Customer::create(array(
			    'email' => $Customer->customerEmail()
			    //'source'  => $token
			));
			$Customer->update(['customerStripeID'=>$customer->id]);	
			$stripe_customer_id = $customer->id;
    	}

    	return $customer;
	}
}
