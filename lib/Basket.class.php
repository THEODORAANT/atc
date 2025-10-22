<?php

class Basket extends Core_Base 
{
	protected $table  = 'tblBaskets';
    protected $pk     = 'basketID';

    public $basket_discounts = [];

    /**
     * Add an item to the basket
     * @param [type] $code [description]
     * @param [type] $qty  [description]
     * @param [type] $replace  Should the qty replace any existing? (else it uses addition)
     */
    public function add($code, $qty, $replace=false)
    {

        // Is this a subscription or a product?
        if ($this->item_is_subscription($code)) {
            // empty all other items. Only one subscription, no mixing.
            $this->empty_contents(false);
        }else{
            $this->remove_subscriptions();
        }

    	// Does this item already exist in the customer's basket?
    	// If so, adjust the qty
    	
    	if ($this->_has_item($code)) {
            if ($replace) {
                $this->update_qty($code, $qty);
            }else{
                $this->_adjust_qty_by($code, $qty);
            }
    	}else{
    		$BasketItems = Factory::get('BasketItems');
    		$BasketItems->create([
    				'basketID'=>$this->id(),
    				'customerID'=>$this->customerID(),
    				'itemCode'=>$code,
    				'itemQty'=>(int)$qty,
    			]);
    		return true;
    	}
    }

    /**
     * Update the qty of an item within the basket
     * @param  [type] $code [description]
     * @param  [type] $qty  [description]
     * @return [type]       [description]
     */
    public function update_qty($code, $qty)
    {
        if ($this->_has_item($code)) {
            $BasketItems = Factory::get('BasketItems');
            $Item = $BasketItems->get_item($this->id(), $code);
       
            if ($Item) {
                if ((int)$qty) {
                    $Item->update([
                        'itemQty'=>(int)$qty,
                    ]);    
                }else{
                    $Item->delete();
                }               
            }

        }else{
            $this->add($code, $qty);
        }

    }

    public function get_raw_item_count()
    {
        $BasketItems = Factory::get('BasketItems');
        $Items       = $BasketItems->get_by('basketID', $this->id());
        return Util::count($Items);
    }

    /**
     * Get the contents of the basket, all calculated and ready for display on the target website
     * @param  string $currency [description]
     * @return [type]           [description]
     */
    public function get_contents($currency='GBP', $type='all')
    {
        $BasketItems = Factory::get('BasketItems');
        $Products    = Factory::get('Products');
        $Customers   = Factory::get('Customers');
        $Currencies  = Factory::get('Currencies');
        $Countries   = Factory::get('Countries');
        
        $Currency    = $Currencies->find($currency);

    	$Customer    = $Customers->find($this->customerID());

    	$Items = $BasketItems->get_by('basketID', $this->id());

        $OurCountry   = $Countries->get_one_by('countryCode', 'GB');
        

        $basket_vat_rate = null;
    	
    	// store running calculations as we go
    	$calc = [
            'items_total' => 0,
            'vat_total'   => 0,
            'grand_total' => 0,
    	];

    	// final output structure
    	$out  = [
					'items'     => [],
					'discounts' => [],
					'totals'    => [
									'items_total'=>0,
									'vat'=>0,
									'grand_total'=>0,
    								],
                    'notices'   => [],
    			];

    	// Add line items
    	if (Util::count($Items)) {

            // Does the customer pay VAT? If so, at what rate?
            $customer_vat_rate = 0;
            $our_vat_rate      = 0;
            if ($Customer->pays_VAT()) {
                $customer_vat_rate = (float) $Customer->get_VAT_rate();
                $our_vat_rate      = (float) $OurCountry->countryVATRate();
            }

            // Is the customer a registered developer?
            $Regdev = $Customer->is_registered_developer();

            // Is there a promo code in use?
            $Promo = false;
            if ($this->basketPromoCode()) {
                $PromoCodes = Factory::get('PromoCodes');
                $Promo      = $PromoCodes->get_valid($this->basketPromoCode());

                if ($Promo) {
                    $promo_percentage = ($Regdev ? (int)$Promo->promoDeveloperDiscount() : (int)$Promo->promoDiscount());
                }else{
                    // Promo code is set on basket, but doesn't appear to be a valid one, so remove it.
                    $this->update(['basketPromoCode'=>null]);
                }
            }
            
    		// Run through the line items
    		foreach($Items as $Item) {


                // Find the product for pricing information
                //$Product = $Products->get_by_item_code($Item->itemCode());
                $Product = $Products->get_by_item_code($Item->itemCode());


                // Check which type of items we're returning.
                // Usually 'all' but can be 'one-offs' and 'subscriptions' when processing a split order
                if ($type!='all') {
                    if ($type=='subscriptions') {
                        // Only subscriptions
                        if (!$Product->productIsSubscription()) {
                            continue;
                        }
                    }else{
                        // Only non-subscriptions, i.e. one-offs
                        if ($Product->productIsSubscription()) {
                            continue;
                        }
                    }
                }


                // Work out VAT - we don't support mixed VAT in one basket, so check that items aren't of different rates
                if ($Product->productVATRate() == 'THEIRS') {
                    $item_vat_rate = $customer_vat_rate;
                }else{
                    $item_vat_rate = $our_vat_rate;
                }

                if ($basket_vat_rate === null) {
                    // not set yet, so set it now.
                    $basket_vat_rate = $item_vat_rate;
                }else{
                    if ($item_vat_rate != $basket_vat_rate) {
                        // skip this item from the basket.
                        $out['notices'][] = 'mixed_vat';
                        $this->update_qty($Item->itemCode(), 0);
                        continue;
                    }
                }
                // VAT done, I think.


                $item_discounts = [];

    			$tmp = [];
                $tmp['code']            = $Item->itemCode();
                $tmp['qty']             = $Item->itemQty();
                $tmp['currency']        = $Currency->currencyCode();
                $tmp['currency_symbol'] = $Currency->currencySymbol();
                $tmp['desc']            = $Product->get_description();
                $tmp['item_rrp']        = $Product->get_price($currency);
                $tmp['item_price']      = $tmp['item_rrp'];

    			
                /* ---- figure out item discounts ---- */


                // Multibuy 
                // - not ideal, but Product::get_price() also does this 5% and 10% calculation, so if changing check it there too.
                if ($Item->itemCode()=='PERCH' || $Item->itemCode()=='RUNWAY') {
                    if ($Item->itemQty()>=10) {
                        $item_discounts[] = [
                            'percentage' => 10,
                            'description'=> 'Multibuy discount',
                            'value' => ($tmp['item_rrp']/100) * 10,
                        ];
                    }

                    if ($Item->itemQty()>=5 && $Item->itemQty()<10) {
                        $item_discounts[] = [
                            'percentage' => 5,
                            'description'=> 'Multibuy discount',
                            'value' => ($tmp['item_rrp']/100) * 5,
                        ];
                    }
                }

                if ($Regdev && $Product->productDiscountable()) {
                    $val = ($tmp['item_rrp']/100) * (int)$Regdev->devDiscount();

                    if ($val>0) {
                        $item_discounts[] = [
                            'percentage' => (int)$Regdev->devDiscount(),
                            'description'=> 'Registered Developer discount',
                            'value' => $val,
                        ]; 
                    }
                }


                if ($Product->productDiscountable() && $Promo && ($Promo->productID() == $Product->id() || (int)$Promo->productID()===0)) {
                    
                    $item_discounts[] = [
                        'percentage' => $promo_percentage,
                        'description'=> 'Discount code '.$Promo->promoCode(),
                        'value' => ($tmp['item_rrp']/100) * $promo_percentage,
                    ];  

                }


                if (Util::count($this->basket_discounts)) {

                    foreach($this->basket_discounts as $basket_discount) {
                        $item_discounts[] = [
                            'percentage' => $basket_discount['percentage'],
                            'description'=> 'Discount',
                            'value' => ($tmp['item_rrp']/100) * $basket_discount['percentage'],
                        ]; 
                    }

                }


                /*
                Now sort through the discounts to find the best value
                 */
                if (Util::count($item_discounts)) {
                    $item_discounts = Util::array_sort($item_discounts, 'value');
                    $best_discount  = array_pop($item_discounts);

                    $tmp['item_price']          = (float)$tmp['item_rrp'] - (float)$best_discount['value'];
                    $tmp['discount_percentage'] = $best_discount['percentage'];
                    $tmp['discount_desc']       = $best_discount['description'];
                    $tmp['discount_value']      = $best_discount['value'];

                }

                /* ---- / figure out item discounts ---- */

                // Tax
                $tmp['item_vat']      = ($tmp['item_price']/100) * $basket_vat_rate;
                $tmp['row_vat_total'] = (float)$tmp['item_vat'] * (int)$tmp['qty'];

    			// Totals
                $tmp['row_items_total']    = (float)$tmp['item_price'] * (int)$tmp['qty'];
    			$tmp['row_payable_total']  = $tmp['row_items_total'] + $tmp['row_vat_total'];

    			// update running totals
                $calc['items_total'] += $tmp['row_items_total'];
                $calc['vat_total']   += $tmp['row_vat_total'];
                $calc['grand_total'] += $tmp['row_payable_total'];

    			$out['items'][] = $tmp;
    		}
    	}


    	// Totals
    	
        // items without discount removed
    	$out['totals']['items_total'] = round((float)$calc['items_total'], 2);

        // VAT?
        if ($Customer->pays_VAT()) {
            $out['totals']['vat'] = round((float) $calc['vat_total'], 2);
            $out['totals']['vat_rate'] = $Customer->get_VAT_rate();
        }

        // Grand total
        $out['totals']['grand_total'] = round((float)$calc['grand_total'], 2);


        $out['totals']['currency'] = $Currency->currencyCode();
        $out['totals']['currency_symbol'] = $Currency->currencySymbol();

        $out['totals']['vat_rate'] = $basket_vat_rate;



        // and we're done.
    	return $out;
    }

    /**
     * Does this basket contain any items that are recurring subscriptions?
     * @return boolean [description]
     */
    public function has_subscriptions()
    {
        $BasketItems = Factory::get('BasketItems');
        $Products    = Factory::get('Products');
        $Items = $BasketItems->get_by('basketID', $this->id());

        if (Util::count($Items)) {
            foreach($Items as $Item) {
                $Product = $Products->get_by_item_code($Item->itemCode());
                if ($Product->productIsSubscription()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Is this item a subscription?
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function item_is_subscription($code)
    {
        $BasketItems = Factory::get('BasketItems');
        $Products    = Factory::get('Products');
        
        $Product = $Products->get_by_item_code($code);
        if ($Product->productIsSubscription()) {
            return true;
        }
        return false;
    }

    /**
     * Remove any subscription products form the basket.
     * @return [type] [description]
     */
    public function remove_subscriptions()
    {
        $BasketItems = Factory::get('BasketItems');
        $Products    = Factory::get('Products');
        
        $Items = $BasketItems->get_by('basketID', $this->id());

        if (Util::count($Items)) {
            foreach($Items as $Item) {
                $Product = $Products->get_by_item_code($Item->itemCode());
                if ($Product->productIsSubscription()) {
                    $this->update_qty($Item->itemCode(), 0);
                }
            }
        }
    }

    /**
     * Get a breakdown of the different types of items in the basket - subscriptions and one-offs
     * @return [type] [description]
     */
    public function get_item_type_breakdown()
    {
        $out = [
                'all'           => 0,
                'subscriptions' => 0,
                'one-offs'      => 0,
                ];

        $BasketItems = Factory::get('BasketItems');
        $Products    = Factory::get('Products');
        $Items = $BasketItems->get_by('basketID', $this->id());

        if (Util::count($Items)) {
            foreach($Items as $Item) {
                $Product = $Products->get_by_item_code($Item->itemCode());

                if ($Product->productIsSubscription()) {
                    $out['subscriptions']++;
                }else{
                    $out['one-offs']++;
                }

                $out['all']++;
            }
        }

        return $out;
    }

    /**
     * Empty the basket - remove all items
     * @return [type] [description]
     */
    public function empty_contents($remove_promos=true)
    {
        $BasketItems = Factory::get('BasketItems');
        $BasketItems->delete_for_basket($this->id());

        if ($remove_promos) $this->update(['basketPromoCode'=>null]);
    }

    /**
     * Apply a promo code to the basket. If the code is valid, it's later used for basket calculations.
     * @param  PromoCode $Promo [description]
     * @return [type]           [description]
     */
    public function apply_promo_code(PromoCode $Promo)
    {
        $this->update(['basketPromoCode'=>$Promo->promoCode()]);

        return true;
    }


    public function set_client_ip($client_ip=false)
    {
        if ($client_ip) {

            $data = ['basketClientIP'=>$client_ip];

            $Countries = Factory::get('Countries');
            $Country   = $Countries->get_by_ip($client_ip);
            if ($Country) {
                $data['countryID'] = $Country->id();
            }

            $this->update($data);
        }
    }


    /**
     * Does the given item code appear in the basket?
     * @param  [type]  $code [description]
     * @return boolean       [description]
     */
    private function _has_item($code)
    {
    	$BasketItems = Factory::get('BasketItems');
    	$Item = $BasketItems->get_item($this->id(), $code);
    	return is_object($Item);
    }


    /**
     * Update the qty of an item in the basket by a positive or negative amount
     * @param  [type] $code [description]
     * @param  [type] $qty  [description]
     * @return [type]       [description]
     */
    private function _adjust_qty_by($code, $qty)
    {
    	$BasketItems = Factory::get('BasketItems');
    	$Item = $BasketItems->get_item($this->id(), $code);
    	if ($Item) {
    		$new_qty = (int)$Item->itemQty() + (int)$qty;
    		if ($new_qty <= 0) {
    			$Item->delete();
    			return true;
    		}

    		$Item->update([
    				'itemQty'=>$new_qty
    			]);
    		return true;
    	}
    }
}
