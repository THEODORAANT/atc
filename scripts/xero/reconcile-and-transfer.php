#!/usr/bin/env php
<?php
	use \Stripe\Stripe;
	use \Stripe\Transfer as Stripe_Transfer;

    include(__DIR__.'/../env.php');

    die('Does not work.');

    $Conf->debug = true;

    $holding_account = '4B4127B0-1925-4708-916A-A712F24E2D35';
    $HSBC_account 	 = '1E232EE7-6510-4E30-AB02-D1C1010C3C49';

    $Xero = Factory::get('Xero', $Conf->xero_api_key, $Conf->xero_secret, $Conf->xero_key_dir.'/publickey.cer', $Conf->xero_key_dir.'/privatekey.pem');

	Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);

	$BankTransfers = Factory::get('BankTransfers');
	$Orders 	   = Factory::get('Orders');


	// Get the recent transfers from Stripe
	$transfers = Stripe_Transfer::all(['limit'=>10]);


	if (count($transfers->data)) {

		foreach($transfers->data as $transfer) {

			// If the transfer is PAID (we don't care about unpaid ones yet)
			if ($transfer->status == 'paid' && $transfer->livemode == '1') {

				//Console::log($transfer);

				// Have we already processed this transfer?
				$BankTransfer = $BankTransfers->get_one_by('transferRef', $transfer->id);

				// If not, do it now.
				if (!$BankTransfer) {

					echo $transfer->id. ' ' . $transfer->status . ' ' .date('d F Y H:i', $transfer->created).PHP_EOL;

					$BankTransfer = $BankTransfers->create([
							'transferRef'      => $transfer->id,
							'transferType'     => 'STRIPE',
							'transferDateTime' => date('Y-m-d H:i:s', $transfer->date),

						]);

					// Run through all the transactions (orders, essentially) included in this transfer
	
					foreach($transfer->transactions->data as $transaction) {

						// Find our matching order
						$Order = $Orders->get_one_by('orderStripeChargeID', $transaction->id);

						// If it's not yet been reconciled...
						if ($Order && $Order->orderSentToXero()==1 && $Order->orderReconciledAtXero()==0) {

							echo $Order->orderInvoiceNumber() . PHP_EOL;

							$payment_reconciled = false;
							$fee_reconciled 	= false;

							// If we have the PaymentID logged ... (we can't do this without that. Early ones weren't logged.)
							if ($Order->orderXeroPaymentID()!='') {

								// Retrieve the payment from Xero
								$xero_payment = $Xero->Payments($Order->orderXeroPaymentID());

								if (is_array($xero_payment) && isset($xero_payment['Status']) && $xero_payment['Status'] == 'OK') {

									// Check the payment amount matches what we have from Stripe
								    if (isset($xero_payment['Payments']['Payment']['Amount'])) {
								        $xero_amount = (float)$xero_payment['Payments']['Payment']['Amount'];

								        // (Stripe stores values *100)
								        if ($xero_amount == ((int)$transaction->amount/100)) {
								        	// All matches up
								        	

								        	/********************************************************************
								        		This bit doesn't work as Xero doesn't allow updating of payments.
								        		In fact, turns out there's no way to reconcile via the API. Hmph.
								        	 */

								        	$xero_result = $Xero->Payments([
								        	 	'PaymentID' => $Order->orderXeroPaymentID(),
								        	 	'IsReconciled' => true,
								        	 	]);

								        	if (is_array($xero_result) && isset($xero_result['Status']) && $xero_result['Status'] == 'OK') {
								        		$payment_reconciled = true; 
								        	}else{
								        		echo 'Reconciliation failed.'.PHP_EOL;
								        		print_r($xero_result);
								        	}			        	
								        }else{
								        	echo 'Amounts do not match.'.PHP_EOL;
								        	echo $xero_amount.' vs '.((int)$transaction->net/100).PHP_EOL;
								        }
								    }else{
								    	echo 'No payment amount.'.PHP_EOL;
								    }
								}
							}

							if ($Order && $Order->orderXeroBankTransactionID()) {

								// Retrieve the Fee (BankTransaction) from Xero
								$xero_banktransaction = $Xero->BankTransactions($Order->orderXeroBankTransactionID());

								if (is_array($xero_banktransaction) && isset($xero_banktransaction['Status']) && $xero_banktransaction['Status'] == 'OK') {

									// Check the payment amount matches what we have from Stripe
								    if (isset($xero_banktransaction['BankTransactions']['BankTransaction']['Total'])) {
								        $xero_amount = (float)$xero_banktransaction['BankTransactions']['BankTransaction']['Total'];

								        // (Stripe stores values *100)
								        if ($xero_amount == ((int)$transaction->fee/100)) {
								        	// All matches up
								        	
								        	$xero_result = $Xero->BankTransactions([
								        	 	'BankTransactionID' => $Order->orderXeroBankTransactionID(),
								        	 	'IsReconciled' => true,
								        	 	]);

								        	if (is_array($xero_result) && isset($xero_result['Status']) && $xero_result['Status'] == 'OK') {
								        		$fee_reconciled = true; 
								        	}						        	
								        }else{
								        	echo 'Fee amounts do not match.'.PHP_EOL;
								        	echo $xero_amount.' vs '.((int)$transaction->fee/100).PHP_EOL;
								        }
								    }else{
								    	echo 'No bank transtion total amount.'.PHP_EOL;
								    }
								}
							}

							if ($payment_reconciled && $fee_reconciled) {
								// It worked!
								$Order->update(['orderReconciledAtXero'=>'1']); 
							}else{
								echo 'Payment or fee not reconciled.'.PHP_EOL;
							}
							
						}
					}
	


					// Move funds from holding account to HSBC account
					$funds_total = (int)$transfer->amount/100; // comes from Stripe in pence

					$new_transfer = [];
		            $new_transfer['BankTransfers'][]['BankTransfer'] = array(
						'FromBankAccount' => ['AccountID'=>$holding_account],
						'ToBankAccount'	  => ['AccountID'=>$HSBC_account],
						'Amount'		  => number_format((float) $funds_total, 2, '.', ''),
					);

					$xero_result = $Xero->BankTransfers([$new_transfer]);


					if (is_array($xero_result) && isset($xero_result['Status']) && $xero_result['Status'] == 'OK') {
						$BankTransfer->update(['transferFundsMovedToBank'=>1]);
					}else{

						$BankTransfer->update(['transferFundsMovedToBank'=>-1]);

						Util::send_email('drew@edgeofmyseat.com', 'hello@grabaperch.com', 'Perch Xero Connector', 'Xero transfer failure', print_r($BankTransfer->to_array(), true)."\n\n".print_r($Order->to_array(), true)."\n\n".print_r($new_transfer, true)."\n\n".print_r($xero_result, true), '');
					}



				}

				// for testing, only do 1.
				break;

			}

			

		}

	}else{
		echo 'No transfers.'.PHP_EOL;
	}

	echo Console::output();	