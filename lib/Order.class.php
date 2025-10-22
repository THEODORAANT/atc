<?php

use \Stripe\Stripe;
use \Stripe\Customer as Stripe_Customer;
use \Stripe\Charge as Stripe_Charge;
use \Stripe\BalanceTransaction as Stripe_BalanceTransaction;

class Order extends Core_Base 
{
	protected $table  = 'tblOrders';
    protected $pk     = 'orderID';

    public $services  = false;

    /**
     * Get the items (products) for this order
     * @return [type] [description]
     */
    public function get_items()
    {
        $OrderItems = Factory::get('OrderItems');
        return $OrderItems->get_for_order($this->id());
    }


    /**
     * Register the order with the payment gateway to get the payment URL to send the customer to.
     * @return [type] [description]
     */
    public function register_for_payment($Gateway)
    {
        $orderID = $this->id();

		$response = $Gateway->register_transaction($this, function($result) use ($orderID) {
			// Not OK, so log it.
			GatewayLogger::log([
                'logGateway' => $Gateway->name,
                'orderID'    => $orderID,
                'logData'    => json_encode($result),
				]);
		});

		if ($response) {
            $updated_data = $Gateway->get_updated_order_values($response);
            if (Util::count($updated_data)) $this->update($updated_data);
			
            GatewayLogger::log([
                'logGateway' => $Gateway->name,
                'orderID'    => $orderID,
                'logData'    => json_encode($response),
                ]);


			return $response['NextURL'];
		}
		
		return false;
    }

    public function take_payment_with_stripe($token)
    {
    	$Conf = Conf::fetch();

    	if ($Conf->payment_gateway['test_mode']) {
			Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
		}else{
			Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
		}

		$OrderItems = Factory::get('OrderItems');

    	$Customers = Factory::get('Customers');
    	$Customer  = $Customers->find($this->customerID());

    	// Set our intentions 
    	$this->update([
					'orderStripeToken' => $token,
					'orderStatus'      => 'PENDING',
					'orderType'        => 'STRIPE',
				]);


    	// Get existing Stripe customer, or create new
    	if ($Customer->customerStripeID()) {
			$stripe_customer_id = $Customer->customerStripeID();
			$customer           = Stripe_Customer::retrieve($stripe_customer_id);
			$customer->card     = $token;
			$customer->email    = $Customer->customerEmail();
			$customer->save();
    	}else{
    		$customer = Stripe_Customer::create(array(
			    'email' => $Customer->customerEmail(),
			    'card'  => $token
			));
			$Customer->update(['customerStripeID'=>$customer->id]);	
			$stripe_customer_id = $customer->id;
    	}

        // Create the charge
        try {
            $charge = Stripe_Charge::create(array(
                'customer' => $stripe_customer_id,
                'amount'   => ($this->orderValue()*100),
                'currency' => $this->orderCurrency(),
            ));
        } catch (Exception $e) {

            GatewayLogger::log([
                'logGateway' => 'STRIPE',
                'orderID' => $this->id(),
                'logData' => json_encode(['message'=>$e->getMessage()]),
            ]);

            $charge = false;
        }


        if ($charge) {

            GatewayLogger::log([
                'logGateway' => 'STRIPE',
                'orderID' => $this->id(),
                'logData' => json_encode((array)$charge),
                ]);

            if ($charge->paid) {

                $balance = Stripe_BalanceTransaction::retrieve($charge->balance_transaction);

                $rate = ((float)$charge->amount / (float)$balance->amount);

                $this->update([
                    'orderStatus'         => 'PAID',
                    'orderStripeChargeID' => $charge->id,
                    'orderFeesGBP'        => ($balance->fee/100),
                    'orderCurrencyRate'   => $rate,
                    'orderFundsAvailable' => date('Y-m-d H:i:s', $balance->available_on),
                ]);

                GatewayLogger::log([
                    'logGateway' => 'STRIPE',
                    'orderID' => $this->id(),
                    'logData' => json_encode((array)$balance),
                ]);

                $this->process_order();

                $charge->metadata = [
                    'invoice_number'=>$this->orderInvoiceNumber(),
                    'order_ref'=>$this->orderRef(),
                ];
                $charge->save();

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
                            $TaxEvidenceItems->log($this->id(), 'CARD_ADDRESS', $retrieved_charge->card->country, 'Stripe', $countryID);
                        }
                    }
                } catch (Exception $e) {

                    GatewayLogger::log([
                        'logGateway' => 'STRIPE',
                        'orderID' => $this->id(),
                        'logData' => json_encode(['message'=>$e->getMessage()]),
                    ]);

                }

                return true;
            }else{

                $this->update([
                    'orderStatus' => 'FAILED',
                    'orderStripeChargeID' => $charge->id,
                    'orderGatewayMessage' => $charge->failure_message,
                ]);

            }
            
        }
        return false;
    	
    	
    }
    
    public function complete_pending_order_with_stripe($charge)
    {
    	$Conf = Conf::fetch();

    	if ($Conf->payment_gateway['test_mode']) {
			Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
		}else{
			Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
		}

        $this->update([
            'orderType'        => 'STRIPE',
        ]);
        
		$OrderItems = Factory::get('OrderItems');

    	$Customers = Factory::get('Customers');
    	$Customer  = $Customers->find($this->customerID());


        // Get existing Stripe customer
        $stripe_customer_id = $charge->customer;
        $Customer->update(['customerStripeID'=>$stripe_customer_id]);	

        if ($charge) {

            GatewayLogger::log([
                'logGateway' => 'STRIPE',
                'orderID' => $this->id(),
                'logData' => json_encode((array)$charge),
                ]);

            if ($charge->paid) {

                $balance = Stripe_BalanceTransaction::retrieve($charge->balance_transaction);

                $rate = ((float)$charge->amount / (float)$balance->amount);

                $this->update([
                    'orderStatus'         => 'PAID',
                    'orderStripeChargeID' => $charge->id,
                    'orderFeesGBP'        => ($balance->fee/100),
                    'orderCurrencyRate'   => $rate,
                    'orderFundsAvailable' => date('Y-m-d H:i:s', $balance->available_on),
                ]);

                GatewayLogger::log([
                    'logGateway' => 'STRIPE',
                    'orderID' => $this->id(),
                    'logData' => json_encode((array)$balance),
                ]);

                $this->process_order();
                
                $retrieved_charge = Stripe_Charge::retrieve($charge->id);

                $retrieved_charge->metadata = [
                    'invoice_number'=>$this->orderInvoiceNumber(),
                    'order_ref'=>$this->orderRef(),
                ];
                $retrieved_charge->save();

                // Log evidence
                try {
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
                            $TaxEvidenceItems->log($this->id(), 'CARD_ADDRESS', $retrieved_charge->card->country, 'Stripe', $countryID);
                        }
                    }
                } catch (Exception $e) {

                    GatewayLogger::log([
                        'logGateway' => 'STRIPE',
                        'orderID' => $this->id(),
                        'logData' => json_encode(['message'=>$e->getMessage()]),
                    ]);

                }

                return true;
            }else{

                $this->update([
                    'orderStatus' => 'FAILED',
                    'orderStripeChargeID' => $charge->id,
                    'orderGatewayMessage' => $charge->failure_message,
                ]);

            }
            
        }
        return false;
    	
    	
    }

    private function create_stripe_subscriptions($Stripe_Customer)
    {
        
        return true;
    }


    

    public function send_confirmation_email($Customer, $order_licenses, $order_items, $number_of_services=0)
    {
        if ($number_of_services>0) {
            $template = 'order_confirmation_services.html';

            if ($this->is_subscription_renewal()) {
                $template = 'order_renew.html';
            }

        }else{
            $template = 'order_confirmation.html';
        }

        $Email = Factory::get('Email', $template, $use_twig=true);
        $Email->recipientEmail($Customer->customerEmail());
        $Email->set('order', $this->to_array());
        $Email->set('customer', $Customer->to_array());
        $Email->set('order_items', $order_items);
        $Email->set('licenses', $order_licenses);

        if ($this->subscriptionID()) {
            $Email->set('subscription', true);
        }
       
        $Email->send();
    }


    /**
     * Do all the things required when an order gets marked as valid
     * @return [type] [description]
     */
    public function process_order()
    {
    	$Orders = Factory::get('Orders');

    	$this->update([
					'orderInvoiceNumber' => $Orders->get_next_invoice_number(1),
				]);

        $fail                    = false;
        $number_of_buys          = 0;
        $number_of_upgrades      = 0;
        $number_of_subscriptions = 0;
        $number_of_services      = 0;
        $reg_dev                 = false;

        $notify_product_codes    = [];

	    $Customers 	= Factory::get('Customers');
		$Customer   = $Customers->find($this->customerID());

    	$order_items = $this->get_items();
        $order_licenses = array();
        $order_upgrades = array();
        
        if (Util::count($order_items)) {
            $Licenses   	 = Factory::get('Licenses');
            $Products 		 = Factory::get('Products');
            $ProductVersions = Factory::get('ProductVersions');
            $Upgrades        = Factory::get('Upgrades');
            $Subscriptions 	 = Factory::get('Subscriptions');
        	
        	foreach($order_items as $OrderItem) {

                $notify_product_codes[] = ucfirst(strtolower($OrderItem->itemCode()));

        		$Product = $Products->get_by_item_code($OrderItem->itemCode());
                $ProductVersion = $Product->get_latest_version();
        	    
        	    /* -------- PERCH LICENSE --------------------------------------- */
        	    if ($OrderItem->itemCode() == 'PERCH') {
        	        $qty = (int)$OrderItem->itemQty();
        	        for($i=0; $i<$qty; $i++) {
						$License          = $Licenses->create($Product, $ProductVersion, $this->customerID(), $this->orderID());
						$order_licenses[] = $License;
						$number_of_buys++;
					}
        	    }
        	    

                /* -------- RUNWAY LICENSE --------------------------------------- */
                if ($OrderItem->itemCode() == 'RUNWAY') {
                    $qty = (int)$OrderItem->itemQty();
                    for($i=0; $i<$qty; $i++) {
                        $License          = $Licenses->create($Product, $ProductVersion, $this->customerID(), $this->orderID());
                        $order_licenses[] = $License;
                        $number_of_buys++;
                    }
                }


                /* -------- RUNWAY DEVELOPER LICENSE --------------------------------------- */
                if ($OrderItem->itemCode() == 'RUNWAYDEV') {
                    $qty = (int)$OrderItem->itemQty();
                    for($i=0; $i<$qty; $i++) {
                        $License          = $Licenses->create($Product, $ProductVersion, $this->customerID(), $this->orderID());
                        $order_licenses[] = $License;
                        $number_of_buys++;
                    }
                }

                /* -------- PERCH 2 RUNWAY UPGRADE --------------------------------------- */
                if ($OrderItem->itemCode() == 'R2UPGRADE') {
                    $qty = (int)$OrderItem->itemQty();
                    for($i=0; $i<$qty; $i++) {                                        
                        $Upgrade    = $Upgrades->create([
                                            'upgradeDate'    => date('Y-m-d H:i:s'),
                                            'customerID'     => $this->customerID(),
                                            'orderID'        => $this->orderID(),
                                            'productID'      => PROD_PERCH,
                                            'versionMajor'   => '2',
                                            'toProductID'    => PROD_RUNWAY,
                                            'toVersionMajor' => '2',
                                        ]);
                        $order_upgrades[] = $Upgrade;
                        $number_of_upgrades++;
                    }
                }


                 /* -------- PERCH 3 Subscriptio RUNWAY UPGRADE --------------------------------------- */
                 /*if ($OrderItem->itemCode() == 'R2SUBUPGRADE') {
                     $qty = (int)$OrderItem->itemQty();
                     for($i=0; $i<$qty; $i++) {
                         $Upgrade    = $Upgrades->create([
                                             'upgradeDate'    => date('Y-m-d H:i:s'),
                                             'customerID'     => $this->customerID(),
                                             'orderID'        => $this->orderID(),
                                             'productID'      => PROD_PERCH,
                                             'versionMajor'   => '3',
                                             'toProductID'    => PROD_R2SUBUPGRADE,
                                             'toVersionMajor' => '4',
                                         ]);
                         $order_upgrades[] = $Upgrade;
                         $number_of_upgrades++;
                     }
                 }*/


                /* -------- RUNWAY DEVELOPER UPGRADE TO FULL --------------------------------------- */
                if ($OrderItem->itemCode() == 'R2DEVUPGRADE') {
                    $qty = (int)$OrderItem->itemQty();
                    for($i=0; $i<$qty; $i++) {                                        
                        $Upgrade    = $Upgrades->create([
                                            'upgradeDate'    => date('Y-m-d H:i:s'),
                                            'customerID'     => $this->customerID(),
                                            'orderID'        => $this->orderID(),
                                            'productID'      => PROD_RUNWAYDEV,
                                            'versionMajor'   => '2',
                                            'toProductID'    => PROD_RUNWAY,
                                            'toVersionMajor' => '2',
                                        ]);
                        $order_upgrades[] = $Upgrade;
                        $number_of_upgrades++;
                    }
                }



                /* -------- PERCH 2 UPGRADE --------------------------------------- */
                if ($OrderItem->itemCode() == 'P2UPGRADE') {
                    $qty = (int)$OrderItem->itemQty();
                    for($i=0; $i<$qty; $i++) {                                        
                        $Upgrade    = $Upgrades->create([
                                            'upgradeDate'    => date('Y-m-d H:i:s'),
                                            'customerID'     => $this->customerID(),
                                            'orderID'        => $this->orderID(),
                                            'productID'      => PROD_PERCH,
                                            'versionMajor'   => '1',
                                            'toProductID'    => PROD_PERCH,
                                            'toVersionMajor' => '2',
                        				]);
                        $order_upgrades[] = $Upgrade;
                        $number_of_upgrades++;
                    }
                }

        	    
        	    /* -------- REGISTERED DEVELOPER SUBSCRIPTION ------------------- */
        	    if ($OrderItem->itemCode() == 'DEVELOPER') {
        	        $Developers = Factory::get('RegisteredDevelopers');
        	        $Developer = $Developers->get_by_customer($this->customerID());
        	        
        	        if (is_object($Developer)) {
        	            // if already a developer, extend
        	            $Developer->extend(12);
        	        }else{
        	            // else create new
        	            $Developer  = $Developers->create([
											'customerID'          => $this->customerID(),
											'devSubscriptionFrom' => date('Y-m-d H:i:s'),
											'devSubscriptionTo'   => date('Y-m-d H:i:s', strtotime('+12 MONTHS')),
        	            				]);
        	        }
        	        
        	        $number_of_buys++;
        	        $reg_dev = true;
        	    }


                /* -------- SERVICES AND SUBSCRIPTIONS -------------------------- */
                if ($Product->productIsService()) {
                    $number_of_services++;
                    $this->services = true;
                }
                if ($Product->productIsSubscription()) {

                  if ($Product->productCode() == 'SUBRUNWAY' || $Product->productCode() =='SUBRUNWAYNEW' || $Product->productCode() =='YEARSUBRUNWAY') {
                         $Subscription = $Subscriptions->find((int)$this->subscriptionID());
                         $Companion = false;
                         if ($Product->companionID()) $Companion = $Products->find($Product->companionID());
                          if (is_object($Subscription) && $Subscription->subInitialised()==0) {

                               $qty = (int)$OrderItem->itemQty();

                               $License  = $Licenses->create_subscriptionLisence($Product, $ProductVersion, $this->customerID(), $this->orderID(), $Subscription, $Companion, $qty);
                               $order_licenses[] = $License;

                               $number_of_subscriptions += $qty;

                               $Subscription->update(['subInitialised'=>'1']);

                           }

                  }
                  echo "product";

print_r($Product);
                   if ($Product->productCode() == 'R2SUBUPGRADE') {
                     echo "Subscription";echo $this->subscriptionID();
                              $Subscription = $Subscriptions->find((int)$this->subscriptionID());
                              $Companion = false;
                              if ($Product->companionID()) $Companion = $Products->find($Product->companionID());
                               if (is_object($Subscription) && $Subscription->subInitialised()==0) {
                               echo "upgrade";

                                    $qty = (int)$OrderItem->itemQty();

                                    //$License  = $Licenses->create_subscriptionLisence($Product, $ProductVersion, $this->customerID(), $this->orderID(), $Subscription, $Companion, $qty);
                                    //$order_licenses[] = $License;
                                    for($i=0; $i<$qty; $i++) {
                                           $Upgrade    = $Upgrades->create([
                                                               'upgradeDate'    => date('Y-m-d H:i:s'),
                                                               'customerID'     => $this->customerID(),
                                                               'orderID'        => $this->orderID(),
                                                               'productID'      => PROD_PERCH,
                                                               'versionMajor'   => '3',
                                                               'toProductID'    => PROD_R2SUBUPGRADE,
                                                               'toVersionMajor' => '4',
                                                           ]);
                                           $order_upgrades[] = $Upgrade;
                                           $number_of_upgrades++;
                                       }
                                    //$number_of_subscriptions += $qty;
                                    print_r( $order_upgrades);

                                    $Subscription->update(['subInitialised'=>'1']);

                                }

                       }


                    /* -------- PERCH MULTISITE LICENSE --------------------------------------- */
                    if ($Product->productCode() == 'PERCHMULTI50') {
                        $Subscription = $Subscriptions->find((int)$this->subscriptionID());
                        $Companion = false;
                        if ($Product->companionID()) $Companion = $Products->find($Product->companionID());

                        if (is_object($Subscription) && $Subscription->subInitialised()==0) {

                            $qty = (int)$OrderItem->itemQty();

                            $License          = $Licenses->create_multisite($Product, $ProductVersion, $this->customerID(), $this->orderID(), $Subscription, $Companion, $qty);
                            $order_licenses[] = $License;
                                
                            $number_of_subscriptions += $qty;

                            $Subscription->update(['subInitialised'=>'1']);

                        }
                    }

                }
        	    
        	}
        }else{
            Console::log('No order items');
        }
        
	    $this->send_confirmation_email($Customer, $order_licenses, $order_items, $number_of_services);
	    

	    // clear the basket
	    $Baskets = Factory::get('Baskets');
		$Basket  = $Baskets->get_for_customer($this->customerID());
	    if (is_object($Basket)) {
            $Basket->empty_contents();
            $Basket->delete();
        }

        // Delete used promo codes
        $PromoCodes = Factory::get('PromoCodes');
        $PromoCodes->deleted_used_single_promos();

        // Update customer record
        $Customer->update(['customerLastOrder'=>date('Y-m-d')]);
        $Customer->detag('customer:lapsed');
        $Customer->tag('customer:purchased');

        /*
        $Page = Page::fetch();
	
        GatewayLogger::log([
                'logGateway' => $this->orderType(),
				'orderID' => $this->id(),
				'logData' => json_encode($Page->console_messages),
				]);
		*/
    
        // Notify Slack
   	    if ($reg_dev) {
            Slack::notify('A registered developer buy! ('.$this->orderInvoiceNumber().': '.$Customer->customerFirstName().' '.$Customer->customerLastName().')', '#buys');
        }

        $notify_product_codes = array_unique($notify_product_codes);

        if ($number_of_buys) {
            if ($number_of_buys == 1) {
                $msg = 'A buy!';
            }else{
                $msg = $number_of_buys .' buys!';
            }
            $msg .= ' ['.implode(', ', $notify_product_codes).'] ';
            Slack::notify($msg.' ('.$this->orderInvoiceNumber().': '.$Customer->customerFirstName().' '.$Customer->customerLastName().')', '#buys');
        }

        if ($number_of_upgrades) {
            if ($number_of_upgrades == 1) {
                $msg = 'An upgrade!';
            }else{
                $msg = $number_of_upgrades .' upgrades!';
            }
            $msg .= ' ['.implode('and ', $notify_product_codes).'] ';
            Slack::notify($msg.' ('.$this->orderInvoiceNumber().': '.$Customer->customerFirstName().' '.$Customer->customerLastName().')', '#buys');
        }        

        if ($number_of_subscriptions) {
            if ($number_of_subscriptions == 1) {
                $msg = 'A subscription!';
            }else{
                $msg = $number_of_subscriptions .' subscriptions!';
            }
            $msg .= ' ['.implode('and ', $notify_product_codes).'] ';
            Slack::notify($msg.' ('.$this->orderInvoiceNumber().': '.$Customer->customerFirstName().' '.$Customer->customerLastName().')', '#buys');
        }
    }


    /**
     * Refund the order by returning the money and marking it as so in the db.
     * Note, does not cancel the services - there's a different method for that.
     * @param  [type] $amount    [description]
     * @param  [type] $vatAmount [description]
     * @param  [type] $reason    [description]
     * @return [type]            [description]
     */
    public function refund($amount, $vatAmount, $reason)
    {
    echo "refund";
        $this->update([
            'orderRefund'       => $amount,
            'orderVATrefund'    => $vatAmount,
            'orderRefundReason' => $reason,
        ]);

        $refunded = false;

        switch($this->orderType()) {
            case 'PAYPAL':
                $Gateway = Factory::get('PayPal');
                break;
            case 'STRIPE':
                $Gateway = Factory::get('StripeGateway');
                break;
            default:
                $Gateway = false;
                break;
        }

        if ($Gateway) {
            $refunded = $Gateway->process_refund($this);

            if ($refunded) {
                // Xero
                $Orders = Factory::get('Orders');

                $this->update([
                    'orderRefundedAtXero' => '-2',
                    'orderCreditNoteNumber' => $Orders->get_next_credit_note_number(),
                    'orderRefundDate'   => date('Y-m-d H:i:s'),
                ]);
                
            }
        }

        if (!$refunded) {
            // roll back
            $this->update([
                'orderRefund'         => '0',
                'orderVATrefund'      => '0',
                'orderRefundReason'   => 'Refund failed',
                'orderRefundedAtXero' => '0',
            ]);
        }

        return $refunded;
    }

    public function cancel_services()
    {
        $Licenses = Factory::get('Licenses');
        $licenses = $Licenses->get_by('orderID', $this->id(), 'licenseDate');
        if (Util::count($licenses)) {
            foreach($licenses as $License) {
                $License->update(['licenseActive'=>'0']);
            }
        }

        $Upgrades = Factory::get('Upgrades');
        $upgrades = $Upgrades->get_by('orderID', $this->id());
        if (Util::count($upgrades)) {
            foreach($upgrades as $Upgrade) {
                $Upgrade->update(['upgradeStatus'=>'REVOKED']);
            }
        }


    }

    public function is_subscription_renewal()
    {
        $subID = $this->subscriptionID();

        if ($subID) {
            $sql = 'SELECT COUNT(*) FROM '.$this->table.' 
                    WHERE orderStatus="PAID" 
                        AND customerID='.$this->db->pdb($this->customerID()).' 
                        AND subscriptionID='.$this->db->pdb($subID).'
                        AND orderID != '.$this->db->pdb($this->id());
            $count = $this->db->get_count($sql);

            if ($count > 0) {
                return true; // it's a renewal
            }
        }

        return false;
    }

    public function update_tax_evidence_count()
    {
        $sql = 'SELECT COUNT(*) AS items
                FROM tblOrderTaxEvidence
                WHERE orderID='.(int)$this->id().'
                GROUP BY countryID
                ORDER BY items DESC
                LIMIT 1';
        $count = $this->db->get_count($sql);

        if ($count==1) $count=0;

        $this->update([
                'orderTaxEvidenceItems'=>$count
            ]);
    }

}




