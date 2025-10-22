<?php

class Upgrades extends Factory 
{
	protected $singularClassName = 'Upgrade';
    protected $table    = 'tblUpgrades';
    protected $pk   = 'upgradeID';

    protected $default_sort_column  = 'upgradeDate';  

    /**
     * Get the upgrades for the given customer and given product.
     * @param  [type] $customerID [description]
     * @param  [type] $productID  [description]
     * @param  [type] $status     [description]
     * @return [type]             [description]
     */
    public function get_for_customer($customerID, $productID, $status='UNSPENT', $versionMajor=1)
    {
        $sql = 'SELECT * FROM :table u, :products p
            WHERE u.toProductID=p.productID AND u.customerID=:customerID AND u.productID=:productID AND u.upgradeStatus=:status AND (u.versionMajor=:versionMajor OR u.versionMajor=2)
            ORDER BY upgradeDate DESC';

    	$Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
    	$Query->set('products', 'tblProducts', 'table');
    	$Query->set('customerID', $customerID, 'int');
        $Query->set('productID', $productID, 'int');
        $Query->set('versionMajor', $versionMajor, 'int');
        $Query->set('status', $status);
 	
    	$rows = $this->db->get_rows($Query);
    	return $this->get_instances($rows);
    }

    
    public function get_count_for_customer($customerID)
    {
        $sql = 'SELECT COUNT(*) FROM :table u
            WHERE u.customerID=:customerID AND u.upgradeStatus=:status';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('status', 'UNSPENT');
    
        return $this->db->get_count($Query);
    }

    /**
     * Get an upgrade that is always available, e.g. Perch to Runway Dev
     * @param  [type] $customerID [description]
     * @param  [type] $productID  [description]
     * @return [type]             [description]
     */
    public function get_evergreen_upgrade($productID, $versionMajor, $to_productID)
    {
        // P2 to RUNWAYDEV
        if ($productID==PROD_PERCH && $versionMajor>1 && $to_productID==PROD_RUNWAYDEV) {

            $sql = 'SELECT *, 0 AS upgradeID
                    FROM :products p
                    WHERE p.productID=:productID';

            $Query = Factory::get('Query', $sql);
            $Query->set('products', 'tblProducts', 'table');
            $Query->set('productID', $to_productID, 'int');

            $row = $this->db->get_row($Query);
            return $this->get_instance($row);
        }

        return false;
    }
}