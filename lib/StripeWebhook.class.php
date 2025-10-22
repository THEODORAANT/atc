<?php

use \Stripe\Stripe;
use \Stripe\Invoice as Stripe_Invoice;

class StripeWebhook
{

	public function __construct()
	{
    	$Conf = Conf::fetch();

    	if ($Conf->payment_gateway['test_mode']) {
			Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
		}else{
			Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
		}
	}

	public function handle($hook)
	{
		if ($hook) {

			switch($hook->type) {

				case 'charge.succeeded':
					return $this->charge_succeeded($hook->data->object);
					break;

			}
		}
	
	}


	private function charge_succeeded($obj)
	{
		if ($obj->paid) {

			// Find the invoice
			if ($obj->invoice) {
				$invoice = $this->find_invoice($obj->invoice);

				if ($invoice && $invoice->lines) {
					foreach($invoice->lines->data as $item) {

						switch($item->type) {

							case 'subscription':
								return $this->process_subscription($item, $obj);
								break;

						}

					}
				}
			}

		}

		return false;
	}

	private function process_subscription($subscription, $charge)
	{
		$Subscriptions = Factory::get('Subscriptions');

		$Sub = $Subscriptions->get_one_by('subStripeID', $subscription->id);

		if ($Sub) {
			return $Sub->create_new_order_from_stripe($subscription, $charge);
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