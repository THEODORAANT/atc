<?php

class Baskets extends Factory 
{
	protected $singularClassName = 'Basket';
    protected $table    = 'tblBaskets';
    protected $pk   = 'basketID';

    protected $default_sort_column  = 'basketID';  

    public function get_for_customer($customerID, $internal=false)
    {
    	$sql = 'SELECT * FROM :table 
    			WHERE customerID=:customer AND basketIsInternal=:internal
    			ORDER BY basketDate DESC
    			LIMIT 1';
    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
        $Query->set('customer', $customerID, 'int');
    	$Query->set('internal', ($internal ? '1' : '0'), 'int');

    	$row = $this->db->get_row($Query);

    	if (Util::count($row)) {
    		return $this->get_instance($row);
    	}

    	// no basket, so create one.
    	
    	return $this->create([
            'customerID'       => $customerID,
            'basketDate'       => date('Y-m-d H:i:s'),
            'basketIsInternal' => ($internal ? '1' : '0'),
    		]);
    	
    }
}