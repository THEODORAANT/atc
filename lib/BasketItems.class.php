<?php

class BasketItems extends Factory 
{
	protected $singularClassName = 'BasketItem';
    protected $table    = 'tblBasketItems';
    protected $pk   = 'itemID';

    protected $default_sort_column  = 'itemID';  

    public function get_item($basketID, $code)
    {
    	$sql = 'SELECT * FROM :table 
    			WHERE basketID=:basket
    				AND itemCode=:code 
    			LIMIT 1';
    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('basket', $basketID, 'int');
    	$Query->set('code', $code);

    	$row = $this->db->get_row($Query);
		return $this->get_instance($row);
    	
    }

    public function delete_for_basket($basketID)
    {
        $sql = 'DELETE FROM :table WHERE basketID=:basket';
        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('basket', $basketID, 'int');

        $this->db->execute($Query);
    }
}