<?php

	$Page->layout = 'empty';
	$Conf->debug = false;

	$SagePay = Factory::get('SagePay');
   

	$fail = true;

    $number_of_buys 	= 0;
	$number_of_upgrades = 0;
    $reg_dev = false;


	if (isset($_POST['Status']) && $_POST['TxType']='PAYMENT') {

		$trans_id       = filter_input(INPUT_POST, 'VendorTxCode');
	    $status         = filter_input(INPUT_POST, 'Status');
	    $vpstxid      	= filter_input(INPUT_POST, 'VPSTxId');
	    $auth_code		= filter_input(INPUT_POST, 'TxAuthNo');
	    $cv2avs         = filter_input(INPUT_POST, 'AVSCV2');
	    $adr_result     = filter_input(INPUT_POST, 'AddressResult');
	    $pc_result      = filter_input(INPUT_POST, 'PostCodeResult');
	    $cv2_result     = filter_input(INPUT_POST, 'CV2Result');
	    $giftaid      	= filter_input(INPUT_POST, 'GiftAid');
	    $threeDS_status = filter_input(INPUT_POST, '3DSecureStatus');
	    $cavv      		= filter_input(INPUT_POST, 'CAVV');
	    $adr_status     = filter_input(INPUT_POST, 'AddressStatus');
	    $payer_status   = filter_input(INPUT_POST, 'PayerStatus');
	    $card_type      = filter_input(INPUT_POST, 'CardType');
	    $last_4_digits  = filter_input(INPUT_POST, 'Last4Digits');
	    $message        = filter_input(INPUT_POST, 'StatusDetail');
	    $hash 			= filter_input(INPUT_POST, 'VPSSignature');

		$Orders     = Factory::get('Orders');
        $Order      = $Orders->get_one_by('orderRef', $trans_id);

        $redirect_url = 'https://grabaperch.com/buy/error/'.$trans_id;

        if (is_object($Order)) {

        	$redirect_url = $Order->orderRedirectURL().'/'.$trans_id;

        	// real
        	$my_hash = md5($vpstxid.$trans_id.$status.$auth_code.$Conf->sagepay_merchant.$cv2avs.$Order->orderSagePaySecurityKey().$adr_result.$pc_result.$cv2_result.$giftaid.$threeDS_status.$cavv.$adr_status.$payer_status.$card_type.$last_4_digits);
 	    
        	// Check hashes
        	if (strtolower($my_hash) == strtolower($hash)) {

	        	switch($status) {

					case 'OK':
						Console::log('Order is apparently valid');
						if ($Order->orderStatus()=='PAID') {
		                    Console::log('Order has already been paid for. This is odd. Bailing.');
		                }else{
		                    
		                    if ($vpstxid == $Order->orderSagePayVPSTxId()) {
		                        $fail = false;
		                        $Customers 	= Factory::get('Customers');
		                    	$Customer   = $Customers->find($Order->customerID());
		                    	
		                    	$data = array();
								$data['orderSagePayResponse'] = $status;
								$data['orderAuthCode']        = $auth_code;
								$data['orderSecpayMessage']   = $message;
								$data['orderStatus']          = 'PAID';
								$data['orderDate']            = date('Y-m-d H:i:s');
								$data['orderInvoiceNumber']   = $Orders->get_next_invoice_number(1);
								$data['orderCardType']        = $card_type;
		                        $Order->update($data);
		                        
		                        
		                        $order_items = $Order->get_items();
		                        $order_licenses = array();
		                        $order_upgrades = array();
		                        
		                        if (Util::count($order_items)) {
		                            $Licenses   	 = Factory::get('Licenses');
		                            $Products 		 = Factory::get('Products');
		                            $ProductVersions = Factory::get('ProductVersions');
		                            $Upgrades 		 = Factory::get('Upgrades');
		                        	
		                        	foreach($order_items as $OrderItem) {

		                        		$Product = $Products->get_by_item_code($OrderItem->itemCode());
		                        		$ProductVersion = $ProductVersions->get_latest($Product->id());
		                        	    
		                        	    /* -------- PERCH LICENSE --------------------------------------- */
		                        	    if ($OrderItem->itemCode() == 'PERCH') {
		                        	        $qty = (int)$OrderItem->itemQty();
		                        	        for($i=0; $i<$qty; $i++) {
												$License          = $Licenses->create($Product, $ProductVersion, $Order->customerID(), $Order->orderID());
												$order_licenses[] = $License;
												$number_of_buys++;
											}
		                        	    }
		                        	    

		                                /* -------- PERCH 2 UPGRADE --------------------------------------- */
		                                if ($OrderItem->itemCode() == 'P2UPGRADE') {
		                                    $qty = (int)$OrderItem->itemQty();
		                                    for($i=0; $i<$qty; $i++) {                                        
		                                        $Upgrade    = $Upgrades->create([
		                                        					'upgradeDate' => date('Y-m-d H:i:s'),
							                                        'customerID'  => $Order->customerID(),
							                                        'orderID'   => $Order->orderID(),
							                                        'productID'	=> '1',
		                                        				]);
		                                        $order_upgrades[] = $Upgrade;
		                                        $number_of_upgrades++;
		                                    }
		                                }

		                        	    
		                        	    /* -------- REGISTERED DEVELOPER SUBSCRIPTION ------------------- */
		                        	    if ($OrderItem->itemCode() == 'DEVELOPER') {
		                        	        $Developers = Factory::get('RegisteredDevelopers');
		                        	        $Developer = $Developers->get_by_customer($Order->customerID());
		                        	        
		                        	        if (is_object($Developer)) {
		                        	            // if already a developer, extend
		                        	            $Developer->extend(12);
		                        	        }else{
		                        	            // else create new
		                        	            $Developer  = $Developers->create([
																	'customerID'          => $Order->customerID(),
																	'devSubscriptionFrom' => date('Y-m-d H:i:s'),
																	'devSubscriptionTo'   => date('Y-m-d H:i:s', strtotime('+12 MONTHS')),
		                        	            				]);
		                        	        }
		                        	        
		                        	        $number_of_buys++;
		                        	        $reg_dev = true;
		                        	    }
		                        	    
		                        	}
		                        }else{
		                            Console::log('No order items');
		                        }
		                        
		                	    $Order->send_confirmation_email($Customer, $order_licenses, $order_items);
		                	    
		                	    // clear the basket
		                	    $Baskets = Factory::get('Baskets');
								$Basket  = $Baskets->get_for_customer($Order->customerID());
		                	    if (is_object($Basket)) {
		                            $Basket->empty_contents();
		                            $Basket->delete();
		                        }
		                        
		                        echo $SagePay->format_response(array(
		                        		'Status'=>'OK',
		                        		'RedirectURL'=>$redirect_url,
		                        	));
		                   
		        
		                    }
		                }

						break;

				
					default:
						$data = array();
                    	$data['orderSagePayResponse'] = $status;
                    	$data['orderSecpayMessage'] = $message;
                    	$data['orderStatus']        = 'FAILED';
                    	$data['orderDate']          = date('Y-m-d H:i:s');
                        $Order->update($data);

                        echo $SagePay->format_response(array(
		                        		'Status'=>'OK', // This means ok, we've got the message. Not that the order is ok.
		                        		'RedirectURL'=>$redirect_url,
		                        	));

						break;



				} // switch status

			}else{
				Console::log('Hashes do not match');
				echo $SagePay->format_response(array(
		                        		'Status'=>'INVALID',
		                        		'RedirectURL'=>$redirect_url,
		                        		'StatusDetail'=>'Message has been tampered with.'
		                        	));
			} // hash check

        }else{
        	Console::log('Order does not exist.');
        	echo $SagePay->format_response(array(
            		'Status'=>'INVALID',
            		'RedirectURL'=>$redirect_url,
            		'StatusDetail'=>'Not a valid order.'
            	));

        } // is object Order

        $body   = print_r($_POST, 1) . "\n\n\n\n" . print_r($_SERVER, 1) . "\n\n\n\n" . Console::output(true);
	    if ($number_of_buys>0 || $number_of_upgrades>0) {

	    	if ($number_of_upgrades>0) {
	    		$buys = ($number_of_upgrades==1?'an upgrade!':$number_of_upgrades.' upgrades!');
	    	}else{
	    		$buys = ($number_of_buys==1?'a buy!':$number_of_buys.' buys!');
	    	}
	        
	        $subject = 'Sagepay: '.$buys;
	        
	        if ($reg_dev) {
	            $subject = 'Sagepay: a registered developer buy!';
	        }
	        
	    }else{

	        $subject = 'Sagepay callback';
	    }
	    
	    Util::send_email('info@edgeofmyseat.com', 'hello@grabaperch.com', 'Perch', $subject, $body);

	        
	    $PromoCodes = Factory::get('PromoCodes');
	    $PromoCodes->deleted_used_single_promos();



	}else{
		Console::log('Does not look like a SagePay post.');
		echo $SagePay->format_response(array(
        		'Status'=>'ERROR',
        		'StatusDetail'=>'Not a valid order.'
        	));


	} // is a post from sagepay




exit;



?>