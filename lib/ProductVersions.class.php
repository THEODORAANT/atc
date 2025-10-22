<?php

class ProductVersions extends Factory 
{
	protected $singularClassName = 'ProductVersion';
    protected $table    = 'tblProductVersions';
    protected $pk   = 'versionID';

    protected $default_sort_column  = 'productDate';  


    /**
     * Get the latest version for the given product
     * @param  integer $productID [description]
     * @return [type]             [description]
     */
    public function get_latest($productID=1)
    {
    	$sql = 'SELECT * FROM :table
                WHERE productID=:productID AND versionOnSale=1
                ORDER BY versionDate DESC
                LIMIT 1';
        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('productID', $productID, 'int');
 	
    	$row = $this->db->get_row($Query);
    	return $this->get_instance($row);
    }

    /**
     * Get the very latest version, even if it's not on sale yet.
     * Used for creating beta program licenses.
     * @param  integer $productID [description]
     * @return [type]             [description]
     */
    public function get_latest_for_beta($productID=1)
    {
        $sql = 'SELECT * FROM :table
                WHERE productID=:productID
                ORDER BY versionDate DESC
                LIMIT 1';
        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('productID', $productID, 'int');
    
        $row = $this->db->get_row($Query);
        return $this->get_instance($row);
    }

    public function get_versions_for_demo_options()
    {
        $sql = 'SELECT DISTINCT versionCode, versionDate FROM :table
                ORDER BY versionDate DESC';
        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
    
        $rows = $this->db->get_rows($Query);
        return $this->get_instances($rows);
    }
}