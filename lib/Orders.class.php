<?php

class Orders extends Factory 
{
	protected $singularClassName = 'Order';
    protected $table    = 'tblOrders';
    protected $pk   = 'orderID';

    protected $default_sort_column  = 'orderDate';  



    /**
     * Get orders for the given customer.
     * @param  [type] $customerID [description]
     * @return [type]             [description]
     */
    public function get_for_customer($customerID, $sort='ASC')
    {

        $sql = 'SELECT * FROM :table
                WHERE customerID=:customerID AND orderStatus=:status
                ORDER BY orderDate :sort';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('status', 'PAID');
        $Query->set('sort', $sort, 'sql');

        $rows = $this->db->get_rows($Query);
        return $this->get_instances($rows);
    }

    /**
     * Get orders for the given customer.
     * @param  [type] $customerID [description]
     * @return [type]             [description]
     */
    public function get_by_ref_for_customer($customerID, $orderRef)
    {

        $sql = 'SELECT * FROM :table
                WHERE customerID=:customerID AND orderStatus=:status AND orderRef=:ref
                LIMIT 1';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('customerID', $customerID, 'int');
        $Query->set('status', 'PAID');
        $Query->set('ref', $orderRef);

    	$row = $this->db->get_row($Query);
    	return $this->get_instance($row);
    }



       /**
     * Create a new order, in the PENDING state. This is the first point where a basket turns into an order before payment.
     * @param  [type] $customerID [description]
     * @param  [type] $currency   [description]
     * @return [type]             [description]
     */
    public function create_pending($Basket, $customerID, $currency, $redirectURL, $provider)
    {
        $Conf = Conf::fetch();
        
        $Customers  = Factory::get('Customers');
        $Customer   = $Customers->find($customerID);

        $basket     = $Basket->get_contents($currency);
      
        
        $PromoCodes = Factory::get('PromoCodes');
        $PromoCode  = $PromoCodes->get_valid($Basket->basketPromoCode());
echo "create_pending";
        $data = [
            'orderDate'       => date('Y-m-d H:i:s'),
            'customerID'      => $customerID,
            'orderType'       => strtoupper($provider),
            'orderStatus'     => 'PENDING',
            'orderCurrency'   => strtoupper($currency),
            'orderValue'      => $basket['totals']['grand_total'],
            'orderSentToXero' => '0',
            'orderItemsTotal' => $basket['totals']['items_total'],
            'orderVAT'        => $basket['totals']['vat'],
            'orderVATrate'    => $basket['totals']['vat_rate'],
            'orderVATnumber'  => $Customer->customerVATnumber(),
            'orderRedirectURL'=> $redirectURL,
            'orderVerifyKey'  => uniqid(),
        ];
        print_r( $data);

        if ($PromoCode) {
            $data['orderPromoCode'] = $PromoCode->promoCode();

            if ($Customer->is_registered_developer()) {
                $data['orderPromoDiscount'] = $PromoCode->promoDeveloperDiscount();
            }else{
                $data['orderPromoDiscount'] = $PromoCode->promoDiscount();
            }
        
        }

        $Order = $this->create($data);
echo "Order";
        if ($Order) {
echo "Order update";
            // update with order ref (needs ID)
            $Order->update([
                'orderRef' => 'P'.date('ym').$Order->id(),
                ]);

            // Copy items across from basket
            $OrderItems = Factory::get('OrderItems');
            if (Util::count($basket['items'])) {
                foreach($basket['items'] as $item) {

                    $OrderItems->create([
                        'orderID'         => $Order->id(),
                        'itemCode'        => $item['code'],
                        'itemQty'         => $item['qty'],
                        'itemUnitPrice'   => $item['item_price'],
                        'itemVatRate'     => $basket['totals']['vat_rate'],
                        'itemUnitVat'     => $item['item_vat'],
                        'itemTotalPrice'  => $item['row_items_total'],
                        'itemTotalVat'    => $item['row_vat_total'],
                        'itemTotalIncVat' => $item['row_payable_total'],
                        'itemDescription' => $item['desc'],
                        ]);

                }
            }

            // Log Tax Evidence
            $TaxEvidenceItems = Factory::get('OrderTaxEvidenceItems'); 
                
            // Evidence: IP address
            if ($Basket->basketClientIP() && $Basket->countryID()) {
                $TaxEvidenceItems->log($Order->id(), 'IP_ADDRESS', $Basket->basketClientIP(), $Conf->ip_geolocator['name'], $Basket->countryID());  
            }

            // Evidence: Billing address
            $TaxEvidenceItems->log($Order->id(), 'ADDRESS', $Customer->country_code(), 'Account', $Customer->countryID()); 
            

            return $Order;
        }

        return false;
    }


    public function get_next_invoice_number($productID=1)
    {
        $sql = 'SELECT nextNumber FROM tblOrderInvoiceNumbers WHERE productID='.$productID.' LIMIT 1';
        $number = (int) $this->db->get_value($sql);
        
        $sql = 'UPDATE tblOrderInvoiceNumbers SET nextNumber=nextNumber+1 WHERE productID='.$productID.'';
        $this->db->execute($sql);

        $Products = Factory::get('Products');
        $Product = $Products->find($productID);
        
        return ucfirst(strtolower($Product->productCode())).$number;
    }

    public function get_next_credit_note_number($productID=1)
    {
        $sql = 'SELECT nextNumber FROM tblOrderCreditNoteNumbers WHERE productID='.$productID.' LIMIT 1';
        $number = (int) $this->db->get_value($sql);
        
        $sql = 'UPDATE tblOrderCreditNoteNumbers SET nextNumber=nextNumber+1 WHERE productID='.$productID.'';
        $this->db->execute($sql);

        $Products = Factory::get('Products');
        $Product = $Products->find($productID);
        
        return ucfirst(strtolower($Product->productCode())).'CN'.$number;
    }

    /**
     * Get orders that are paid and have not yet been sent to Xero
     *
     * @return void
     * @author Drew McLellan
     */
    public function get_orders_for_xero($limit=1)
    {   
        $sql = 'SELECT * FROM :table
                WHERE orderStatus=:status AND orderSentToXero=0
                ORDER BY orderDate ASC
                LIMIT :limit';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('status', 'PAID');
        $Query->set('limit', $limit, 'int');

        $rows = $this->db->get_rows($Query);
        return $this->get_instances($rows);
    }


        /**
         * Get orders that are paid and have not yet been sent to Xero
         *
         * @return void
         * @author Drew McLellan
         */
        public function get_orders_for_stripe()
        {
            $sql = 'SELECT * FROM :table
                    WHERE orderStatus=:status AND orderType=:paymentType
                    AND DATE(orderDate)>=:daterange
                    ORDER BY orderDate ASC
                   ';

            $Query = Factory::get('Query', $sql);
            $Query->set('table', $this->table, 'table');
            $Query->set('status', 'PENDING');
            $Query->set('paymentType', 'STRIPE');
            $Query->set('daterange', '2021-12-07');
            $rows = $this->db->get_rows($Query);

            return $this->get_instances($rows);
        }

    /**
     * Get orders that have been refunded and need to be credited at xero
     * @param  integer $limit [description]
     * @return [type]         [description]
     */
    public function get_refunds_for_xero($limit=1)
    {   
        $sql = 'SELECT * FROM :table
                WHERE orderStatus=:status AND orderRefundedAtXero=-2 AND orderRefund>0 AND orderRefundDate IS NOT NULL
                ORDER BY orderDate ASC
                LIMIT :limit';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('status', 'PAID');
        $Query->set('limit', $limit, 'int');

        $rows = $this->db->get_rows($Query);
        return $this->get_instances($rows);
    }

    /**
     * Get orders within the last 24 hours where the customer was referred by another customer.
     * @return [type] [description]
     */
    public function get_recent_with_referral($time='24 HOURS')
    {
        $sql = 'SELECT o.*, c.customerReferredBy
                FROM tblOrders o, tblCustomers c
                WHERE o.customerID=c.customerID
                    AND o.orderStatus=:status
                    AND o.orderRefund=0
                    AND o.orderDate >:date
                    AND c.customerReferredBy > 1';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('status', 'PAID');
        $Query->set('date', date('Y-m-d H:i:s', strtotime('-'.$time)));

        $rows = $this->db->get_rows($Query);
        return $this->get_instances($rows);
    }

    public function get_all_paid($Paging=false)
    {
        if (is_object($Paging)) {
            $select = 'SELECT SQL_CALC_FOUND_ROWS ';
        }else{
            $select = 'SELECT ';
        }
        
        $sql    = $select . ' * 
                    FROM ' . $this->table . ' o, tblCustomers c
                    WHERE o.customerID=c.customerID AND o.orderStatus=\'PAID\' '.$this->standard_restrictions();

        if ($this->default_sort_column) {
            $sql .= ' ORDER BY o.orderDate DESC';
        }

         
        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $limit  = ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
            $sql    .= $limit;      
        }
              

        $rows   = $this->db->get_rows($sql);

        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $sql    = "SELECT FOUND_ROWS() AS count";
            $total  = $this->db->get_value($sql);
            $Paging->set_total($total);
        }

        return $this->get_instances($rows);
    }
        

    public function search($query)
    {
        $sql = 'SELECT * 
                FROM :table
                WHERE orderInvoiceNumber=:query
                    OR orderRef=:query
                    OR orderPayPalSaleID=:query';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('query', $query);
        $Query->set('lquery', '%'.$query);
        $Query->set('rquery', $query.'%');

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);       
    }

    public function count_matching_tax_evidence()
    {
        $sql = 'SELECT * FROM '.$this->table.'
                WHERE orderTaxEvidenceItems IS NULL AND orderStatus="PAID" AND orderID IN (
                    SELECT orderID FROM tblOrderTaxEvidence
                    )';
        $orders = $this->get_instances($this->db->get_rows($sql));

        if (Util::count($orders)) {
            foreach($orders as $Order) {
                $Order->update_tax_evidence_count();
            }
        }
    }

    public function get_with_missing_evidence($Paging=false)
    {
        if (is_object($Paging)) {
            $select = 'SELECT SQL_CALC_FOUND_ROWS ';
        }else{
            $select = 'SELECT ';
        }
        
        $sql    = $select . ' * 
                    FROM ' . $this->table . ' o, tblCustomers c
                    WHERE o.customerID=c.customerID AND o.orderStatus=\'PAID\' AND o.orderTaxEvidenceItems=0 '.$this->standard_restrictions();

        if ($this->default_sort_column) {
            $sql .= ' ORDER BY o.orderDate DESC';
        }

         
        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $limit  = ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
            $sql    .= $limit;      
        }
              

        $rows   = $this->db->get_rows($sql);

        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $sql    = "SELECT FOUND_ROWS() AS count";
            $total  = $this->db->get_value($sql);
            $Paging->set_total($total);
        }

        return $this->get_instances($rows);
    }

}
