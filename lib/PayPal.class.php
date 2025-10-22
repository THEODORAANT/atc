<?php

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Address;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\FundingInstrument;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Sale;
use PayPal\Api\Refund;

class PayPal implements PaymentGateway
{
	public $name = 'PAYPAL';

	private $result_url;

	public function get_api_context()
	{
		$Conf = Conf::fetch();

		if ($Conf->payment_gateway['test_mode']) {
			$ApiContext = new ApiContext(new OAuthTokenCredential($Conf->paypal['test']['client_id'], $Conf->paypal['test']['secret']));

			$ApiContext->setConfig([
					'mode'                   => 'sandbox',
					'http.ConnectionTimeOut' => 30,
					'log.LogEnabled'         => true,
					'log.FileName'           => 'PayPal.log',
					'log.LogLevel'           => 'FINE'
				]);

		}else{
			$ApiContext = new ApiContext(new OAuthTokenCredential($Conf->paypal['live']['client_id'], $Conf->paypal['live']['secret']));
		
			$ApiContext->setConfig([
					'mode'                   => 'live',
					'http.ConnectionTimeOut' => 10,
					'log.LogEnabled'         => false,
				]);
		}

		return $ApiContext;
	}

	public function get_callback_url()
	{
		$Conf = Conf::fetch();
		if ($Conf->payment_gateway['test_mode']) {
			return rtrim($Conf->paypal['test']['callback'], '/');
		} else {
			return rtrim($Conf->paypal['live']['callback'], '/');
		}
	}

	public function set_result_url($url) 
	{
		$this->result_url = rtrim($url, '/');
	}


	public function register_transaction(Order $Order, callable $cb_failure)
	{
		$Order->update([
					'orderStatus'      => 'PENDING',
					'orderType'        => 'PAYPAL',
				]);

		$OrderItems = Factory::get('OrderItems');

		$payer = new Payer();
		$payer->setPaymentMethod("paypal");

		// ### Amount
		// Let's you specify a payment amount.
		$amount = new Amount();
		$amount->setCurrency($Order->orderCurrency());
		$amount->setTotal(number_format($Order->orderValue(), 2));

		// ### Transaction
		// A transaction defines the contract of a payment - what is the payment for and who
		// is fulfilling it. Transaction is created with a `Payee` and `Amount` types
		$transaction = new Transaction();
		$transaction->setAmount($amount);
		$transaction->setDescription($OrderItems->get_description($Order->id()));

		// ### Redirect urls
		// Set the urls that the buyer must be redirected to after payment approval/ cancellation.
		$baseUrl = $this->get_callback_url();
		$redirectUrls = new RedirectUrls();
		$redirectUrls->setReturnUrl($baseUrl.'/'.$Order->orderRef().'/'.$Order->orderVerifyKey().'/success');
		$redirectUrls->setCancelUrl($baseUrl.'/'.$Order->orderRef().'/'.$Order->orderVerifyKey().'/failure');

		// ### Payment
		// A Payment Resource; create one using the above types and intent as 'sale'
		$payment = new Payment();
		$payment->setIntent('sale');
		$payment->setPayer($payer);
		$payment->setRedirectUrls($redirectUrls);
		$payment->setTransactions(array($transaction));

		// ### Create Payment
		// Create a payment by posting to the APIService  using a valid apiContext.
		// The return object contains the status and the url to which the buyer must be redirected to
		// for payment approval
		try {
			$payment->create($this->get_api_context());
		} catch (Exception $ex) {
			$cb_failure($ex->getData());
			return false;
		}

		// ### Redirect buyer to paypal
		// Retrieve buyer approval url from the `payment` object.
		foreach($payment->getLinks() as $link) {
			if($link->getRel() == 'approval_url') {
				$redirectUrl = $link->getHref();
			}
		}

		$out = $payment->toArray();
		$out['NextURL'] = $redirectUrl;
		$out['Payment'] = $payment;

		return $out;
	}

	public function complete_transaction(Order $Order, array $details, callable $cb_failure)
	{
		$ApiContext = $this->get_api_context();

		$payment_id = $Order->orderPayPalPaymentID();

		try {
			$payment    = Payment::get($payment_id, $ApiContext);
		} catch (Exception $ex) {
			$cb_failure($ex->getMessage());
			return false;
		}
		
		// PaymentExecution object includes information necessary  to execute a PayPal account payment. 
		// The payer_id is added to the request query parameters when the user is redirected from paypal back to your site
		
		try {
			$execution = new PaymentExecution();
			$execution->setPayerId($details['payer_id']);

			// Execute the payment
			$payment = $payment->execute($execution, $ApiContext);
		}catch(Exception $ex) {
			$err_out = $payment->toArray();
			$err_out['exception_message'] = $ex->getMessage();
			$err_out['exception_data'] = $ex->getData();

			$cb_failure($err_out);
			return false;
		}
		

		if ($payment->state == 'approved') {

			// mark it paid
			$Order->update([
					'orderStatus'         => 'PAID',
				]);

			// cheeky log
			GatewayLogger::log([
				'logGateway' => 'PAYPAL',
				'orderID' => $Order->id(),
				'logData' => json_encode($payment->toArray()),
			]);

			// Get the payment to find the fees

			try {
				$payment      = Payment::get($payment_id, $ApiContext);
				$transactions = $payment->getTransactions();
				$resources    = $transactions[0]->getRelatedResources();		
				$sale_id      = $resources[0]->getSale()->getId();
				
				$Order->update([
						'orderPayPalSaleID'         => $sale_id,
					]);


			} catch (Exception $ex) {
				// It doesn't matter so much.
			}
			

			// Process the order
			$Order->process_order();


			// Log Tax Evidence
			try {
				$payer        = $payment->getPayer();
				$payer_info   = $payer->getPayerInfo();
				$adr          = $payer_info->getShippingAddress();
				$country_code = $adr->country_code;

				$Countries = Factory::get('Countries');
				$Country   = $Countries->get_one_by('countryCode', $country_code);
				$countryID = 0;
				if ($Country) {
				    $countryID = $Country->id();
				}

				// Log Tax Evidence
				$TaxEvidenceItems = Factory::get('OrderTaxEvidenceItems'); 
				$TaxEvidenceItems->log($Order->id(), 'CARD_ADDRESS', $country_code, 'PayPal', $countryID);

			} catch (Exception $ex) {
				// It doesn't matter so much.
			}


			return true;

		} else {
			$cb_failure($payment->toArray());

			return false;
		}

	}

	public function get_updated_order_values(array $response)
	{
		return [
			'orderPayPalPaymentID' => $response['Payment']->getId(),
		];
	}

	public function process_refund(Order $Order)
	{
		$ApiContext = $this->get_api_context();

		if ($Order->orderPayPalSaleID()!='') {

			$amount = ((float)$Order->orderRefund() + (float)$Order->orderVATrefund());
			$amount = number_format($amount, 2, '.', '');

			$sale = Sale::get($Order->orderPayPalSaleID(), $ApiContext);

			if ($sale) {

				$amt = new Amount();
				$amt->setCurrency($Order->orderCurrency());
				$amt->setTotal($amount);

				$refund = new Refund();
				$refund->setAmount($amt);

				$refund = $sale->refund($refund, $ApiContext);

				if ($refund && $refund->state!='failed') {
					return true;
				}

			}

		}

		return false;
	}

}