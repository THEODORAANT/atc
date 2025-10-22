<?php

class RegisteredDevelopers extends Factory 
{
	protected $singularClassName = 'RegisteredDeveloper';
    protected $table    = 'tblRegisteredDevelopers';
    protected $pk   = 'devID';

    protected $default_sort_column  = 'devID';  

    public function get_by_customer($customerID)
    {
    	$sql = 'SELECT * FROM :table
    			WHERE customerID=:customer
    				AND devSubscriptionFrom < :now AND devSubscriptionTo >= :now AND devActive=1 
    			LIMIT 1';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('customer', $customerID, 'int');
    	$Query->set('now', date('Y-m-d H:i:s'));
    	return $this->get_instance($this->db->get_row($Query));
    }

    /**
     * Get the list of regdevs for display in the developer listing on the website.
     * @return [type] [description]
     */
    public function get_listing()
    {
        $sql = 'SELECT * FROM :table
                WHERE devSubscriptionFrom < :now AND devSubscriptionTo >= :now AND devActive=1 AND devListingEnabled=1 AND devURL IS NOT NULL
                ORDER BY RAND()';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('now', date('Y-m-d H:i:s'));
        return $this->get_instances($this->db->get_rows($Query));
    }

    public function get_active()
    {
        $sql = "SELECT r.devID, c.customerID, c.customerFirstName, c.customerLastName, r.devTitle, country.countryName, r.devSubscriptionFrom, r.devSubscriptionTo, r.devListingEnabled
                FROM tblRegisteredDevelopers r, tblCustomers c, tblCountries country
                WHERE r.customerID=c.customerID AND c.countryID=country.countryID AND devActive=1 AND devSubscriptionTo>:now
                        AND c.customerEmail NOT LIKE '%edgeofmyseat.com'
                ORDER BY devSubscriptionTo ASC";

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('now', date('Y-m-d H:i:s'));
        return $this->get_instances($this->db->get_rows($Query));
    }

    public function get_lapsed()
    {
        $sql = "SELECT r.devID, c.customerID, c.customerFirstName, c.customerLastName, r.devTitle, country.countryName, r.devSubscriptionFrom, r.devSubscriptionTo, r.devListingEnabled
                FROM tblRegisteredDevelopers r, tblCustomers c, tblCountries country
                WHERE r.customerID=c.customerID AND c.countryID=country.countryID AND devSubscriptionTo<:now
                ORDER BY devSubscriptionTo ASC";

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('now', date('Y-m-d H:i:s'));
        return $this->get_instances($this->db->get_rows($Query));
    }
}