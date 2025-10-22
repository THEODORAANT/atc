<?php

class CustomerPromoCodes extends Factory 
{
	protected $singularClassName = 'CustomerPromoCode';
    protected $table  = 'tblCustomerPromoCodes';
    protected $pk     = 'id';

    protected $default_sort_column  = 'promoCreated';  

    /**
    * get all promocodes this customer has earned.
    */
    public function get_customer_promo_codes($customerID)
    {
        $sql = 'SELECT * FROM :table
                WHERE customerID = :customerID 
                ORDER BY promoCreated DESC';


        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('customerID', $customerID);

        return $this->get_instances($this->db->get_rows($Query));
    }

    /**
     * Check to see if codes have already been generated for the given originatingOrderID
     * @param  [type] $orderID [description]
     * @return [type]          [description]
     */
    public function order_has_been_issued_codes($orderID)
    {
        $sql = 'SELECT COUNT(*) FROM :table
                WHERE originatingOrderID=:orderID';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('orderID', $orderID);

        return $this->db->get_count($Query);
    }
}