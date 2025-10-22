#!/usr/bin/env php
<?php
	use \Stripe\Stripe;
	use \Stripe\Transfer as Stripe_Transfer;

    include(__DIR__.'/../env.php');

    $Conf->debug = false;
    $debug = false;

    $holding_account = '4B4127B0-1925-4708-916A-A712F24E2D35';
    $HSBC_account 	 = '1E232EE7-6510-4E30-AB02-D1C1010C3C49';

    $Xero = Factory::get('Xero', $Conf->xero_api_key, $Conf->xero_secret, $Conf->xero_key_dir.'/publickey.cer', $Conf->xero_key_dir.'/privatekey.pem');

	Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);

	$BankTransfers = Factory::get('BankTransfers');
	$Orders 	   = Factory::get('Orders');


	// Get the recent transfers from Stripe
	$transfers = Stripe_Transfer::all([
		'limit'  => 10,
		'status' => 'paid',
		'date'   => ['gte'=>strtotime('01 June 2014 00:00:00')],
		]);


	if (count($transfers->data)) {

		foreach($transfers->data as $transfer) {

			if ($debug) echo 'Transfer dated '.date('d F Y', (int)$transfer->date).PHP_EOL;

			if ($debug) echo 'pausing...'.PHP_EOL;
			sleep(1);

			// If the transfer is PAID (we don't care about unpaid ones yet)
			if ($transfer->status == 'paid' && $transfer->livemode == '1') {

				//Console::log($transfer);

				// Have we already processed this transfer?
				$BankTransfer = $BankTransfers->get_one_by('transferRef', $transfer->id);

				// If not, do it now.
				if (!$BankTransfer) {

					$transfer_is_good = true;

					if ($debug) echo $transfer->id. ' ' . $transfer->status . ' ' .date('d F Y H:i', $transfer->created).PHP_EOL;


					// Run through all the transactions (orders, essentially) included in this transfer
	
					foreach($transfer->transactions->data as $transaction) {

						if ($debug) echo 'Transaction: ' .$transaction->id.PHP_EOL;

						if (!$transfer_is_good) break;

						// Find our matching order
						$Order = $Orders->get_one_by('orderStripeChargeID', $transaction->id);

						// If it's not yet been reconciled...
						if ($Order && $Order->orderSentToXero()==1 && $Order->orderReconciledAtXero()==0) {

							if ($debug) echo $Order->orderInvoiceNumber() . PHP_EOL;

							$payment_reconciled = false;
							$fee_reconciled 	= false;

							// If we have the PaymentID logged ... (we can't do this without that. Early ones weren't logged.)
							if ($Order->orderXeroPaymentID()!='') {

								if ($debug) echo 'pausing...'.PHP_EOL;
								sleep(1);

								// Retrieve the payment from Xero
								$xero_payment = $Xero->Payments($Order->orderXeroPaymentID());

								if (is_array($xero_payment) && isset($xero_payment['Status']) && $xero_payment['Status'] == 'OK') {

									// Check the payment amount matches what we have from Stripe
								    if (isset($xero_payment['Payments']['Payment']['Amount'])) {
								        $xero_amount = (float)$xero_payment['Payments']['Payment']['Amount'];
								        $xero_rate   = (float)$xero_payment['Payments']['Payment']['CurrencyRate'];
								        if ($xero_rate) {
								        	if ($debug) echo '==> Rate converting: '.$xero_amount .' @ rate '.$xero_rate.PHP_EOL;
								        	$xero_amount = number_format($xero_amount/$xero_rate, 2);
								        }

								        // (Stripe stores values *100)
								        if ($xero_amount == ((int)$transaction->amount/100)) {
								        	// All matches up

											if (isset($xero_payment['Payments']['Payment']['IsReconciled']) && Util::bool_val($xero_payment['Payments']['Payment']['IsReconciled'])) {
												$payment_reconciled = true; 	
												if ($debug) echo 'Payment reconciled.'.PHP_EOL;
												//print_r($xero_payment['Payments']['Payment']);
											}else{
												if ($debug) echo "\t --> Payment not reconciled. ".PHP_EOL;
												//print_r($xero_payment['Payments']['Payment']);
											}
							        		
								        }else{
								        	if ($debug) echo 'Amounts do not match.'.PHP_EOL;
								        	if ($debug) echo $xero_amount.' vs '.((int)$transaction->net/100).PHP_EOL;
								        }
								    }else{
								    	if ($debug) echo 'No payment amount.'.PHP_EOL;
								    }
								}else{
									if ($debug) echo 'No Xero payment data.'.PHP_EOL;
									print_r($xero_payment);
								}
							}else{
								if ($debug) echo 'Order has no Xero PaymentID, so must be an early one. Skipping.'.PHP_EOL;
							}

							if ($Order && $Order->orderXeroBankTransactionID()) {

								if ($debug) echo 'pausing...'.PHP_EOL;
								sleep(1);

								// Retrieve the Fee (BankTransaction) from Xero
								$xero_banktransaction = $Xero->BankTransactions($Order->orderXeroBankTransactionID());

								if (is_array($xero_banktransaction) && isset($xero_banktransaction['Status']) && $xero_banktransaction['Status'] == 'OK') {

									// Check the payment amount matches what we have from Stripe
								    if (isset($xero_banktransaction['BankTransactions']['BankTransaction']['Total'])) {
								        $xero_amount = (float)$xero_banktransaction['BankTransactions']['BankTransaction']['Total'];
								        $xero_rate   = (float)$xero_banktransaction['BankTransactions']['BankTransaction']['CurrencyRate'];
								        if ($xero_rate) {
								        	if ($debug) echo '==> Rate converting: '.$xero_amount .' @ rate '.$xero_rate.PHP_EOL;
								        	$xero_amount = number_format($xero_amount/$xero_rate, 2);
								        }

								        // (Stripe stores values *100)
								        if ($xero_amount == ((int)$transaction->fee/100)) {
								        	// All matches up
								        	

								        	if (isset($xero_banktransaction['BankTransactions']['BankTransaction']['IsReconciled']) && Util::bool_val($xero_banktransaction['BankTransactions']['BankTransaction']['IsReconciled'])) {
								        		$fee_reconciled = true; 	
								        	}else{
												if ($debug) echo "\t --> Fee not reconciled. ".PHP_EOL;
												//print_r($xero_banktransaction['BankTransactions']['BankTransaction']);
											}
				        	
								        }else{
								        	if ($debug) echo 'Fee amounts do not match.'.PHP_EOL;
								        	if ($debug) echo $xero_amount.' vs '.((int)$transaction->fee/100).PHP_EOL;
								        }
								    }else{
								    	if ($debug) echo 'No bank transtion total amount.'.PHP_EOL;
								    }
								}
							}else{
								if ($debug) echo 'Order has no Xero Bank Transaction ID.'.PHP_EOL;
							}

							if ($payment_reconciled && $fee_reconciled && $transfer_is_good) {
								// It worked!
								$Order->update(['orderReconciledAtXero'=>'1']); 
							}else{

								if ($debug) echo 'Payment or fee not reconciled.'.PHP_EOL;

								if ($debug && !$transfer_is_good) 	echo 'Reason: Transfer is not good.'.PHP_EOL;
								if ($debug && !$payment_reconciled) echo 'Reason: Payment is not reconciled.'.PHP_EOL;
								if ($debug && !$fee_reconciled) 	echo 'Reason: Fee is not reconciled.'.PHP_EOL;

								$transfer_is_good = false;
								
							}
							
						}else{
							if ($debug) echo 'Order for this transaction not found, or not in Xero, or already marked as reconciled locally.'.PHP_EOL;
						}
					}
	

					if ($transfer_is_good) {
						// Move funds from holding account to HSBC account
						$funds_total = (int)$transfer->amount/100; // comes from Stripe in pence

						$new_transfer = [];
			            $new_transfer = array(
							'FromBankAccount' => ['AccountID'=>$holding_account],
							'ToBankAccount'	  => ['AccountID'=>$HSBC_account],
							'Amount'		  => number_format((float) $funds_total, 2, '.', ''),
							'Date'		      => date('Y-m-d', (int)$transfer->date),
						);

						$xero_result = $Xero->BankTransfers([$new_transfer]);


						$BankTransfer = $BankTransfers->create([
								'transferRef'      => $transfer->id,
								'transferType'     => 'STRIPE',
								'transferDateTime' => date('Y-m-d H:i:s', $transfer->date),

							]);

						if (is_array($xero_result) && isset($xero_result['Status']) && $xero_result['Status'] == 'OK') {
							$BankTransfer->update(['transferFundsMovedToBank'=>1]);
						}else{

							$BankTransfer->update(['transferFundsMovedToBank'=>-1]);

							Util::send_email('drew@edgeofmyseat.com', 'hello@grabaperch.com', 'Perch Xero Connector', 'Xero transfer failure', print_r($BankTransfer->to_array(), true)."\n\n".print_r($Order->to_array(), true)."\n\n".print_r($new_transfer, true)."\n\n".print_r($xero_result, true)."\n\n".$Xero->stashed_xml_request, '');
						}
					}else{
						if ($debug) echo 'Transfer not good, skipping.'.PHP_EOL;
					}


				}else{
					if ($debug) echo 'Transfer previously processed, skipping.'.PHP_EOL;
				}

			}else{
				if ($debug) echo 'Transfer not paid yet, skipping.'.PHP_EOL;
			}

			

		}

	}else{
		if ($debug) echo 'No transfers.'.PHP_EOL;
	}

	if ($debug) echo Console::output();	