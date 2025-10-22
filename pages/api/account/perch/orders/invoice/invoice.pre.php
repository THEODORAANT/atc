<?php
	/*
		Get a single order invoice as HTML

	 */

	$Page->layout = 'json';
	$Conf->debug = false;

	$secret = $Conf->api_secrets['auth'];

	$result = [
		'result' => 'ERROR',
		'status' => '500',
	];


	if (isset($_POST['secret']) && $_POST['secret']==$secret && isset($_SERVER['HTTP_X_ATC_CLIENT'])) {
		$Customers 	   = Factory::get('Customers');

		$customerID     = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		$token 			= filter_input(INPUT_POST, 'token', FILTER_UNSAFE_RAW);
		$orderRef		= filter_input(INPUT_POST, 'orderRef', FILTER_SANITIZE_STRING);

		$Customer  = $Customers->find($customerID);

		if (is_object($Customer)) {

			if ($Customer->check_session_token($token)) {

				$out = array();

				if ($orderRef) {
					$Order = $Customer->get_order($orderRef);

					if (is_object($Order)) {

						$order = $Order->to_array();
				    	$items = $Order->get_items();
				    	$customer = $Customer->to_array();


				    	$currency_rating = $order['orderCurrencyRate'];

				    	if ($currency_rating == 0) {
				    		$no_invoice = true;
				    		$result['message'] = 'Zero currency rating';
				    	}else{

				    		//customer
							$out['firstname'] = $customer['customerFirstName'];
							$out['lastname']  = $customer['customerLastName'];
							$out['company']   = $customer['customerCompany'];
							$out['address1']  = $customer['customerStreetAdr1'];
							$out['address2']  = $customer['customerStreetAdr2'];
							$out['locality']  = $customer['customerLocality'];
							$out['region']    = $customer['customerRegion'];
							$out['postcode']  = $customer['customerPostalCode'];

							$Countries = Factory::get('Countries');
							$Country   = $Countries->find($customer['countryID']);

							$out['country_name'] = $Country->countryName();

				    		if($customer['customerVATnumber'] && $customer['customerVATnumber'] != '') {
					    		$out['customer_vat_number'] = $customer['customerVATnumber'];
				    		}

				    		//order
							$out['currency_rating'] = $currency_rating;
							$out['card_type']       = $order['orderCardType'];
							$out['vat_number']      = $order['orderVATnumber'];
							$out['vat_rate']        = number_format($order['orderVATrate'],2);
							$out['vat_amount']      = number_format($order['orderVAT'],2);
							$out['subtotal']        = number_format($order['orderItemsTotal'],2);
							$out['total']           = number_format($order['orderValue'],2);
							$out['currency']        = $order['orderCurrency'];
							$out['invoice_number']  = $order['orderInvoiceNumber'];
							$out['ref']             = $order['orderRef'];
							$out['date']            = $order['orderDate'];

				    		if($order['orderCurrency'] != 'GBP') {
				    			$out['gbp_total'] = number_format($order['orderValue'] / $currency_rating,2);
				    			$out['gbp_vat_amount'] = number_format($order['orderVAT'] / $currency_rating,2);
				    		}


				    		$out['items'] = array();
				    		foreach($items as $Item) {
				    			$out['items'][] = $Item->to_array();
				    		}

    						$result = [
								'result'  		=> 'OK',
								'status'		=> '200',
								'invoice'		=> $out
							];

						}
					}else{
						$result['message'] = 'Order not found.';
					}
				}else{
					$result['message'] = 'No order ref.';
				}
			}
		}
	}

	header('HTTP/1.0 '.$result['status'], true, (int)$result['status']);
