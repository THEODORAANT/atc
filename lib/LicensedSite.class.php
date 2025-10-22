<?php

class LicensedSite extends Core_Base 
{
	protected $table  = 'tblLicensedSites';
    protected $pk     = 'siteID';

    public function set_product($productID)
    {
    	$sql = 'SELECT licenseID, parentID, productID FROM tblLicenses 
    			WHERE productID=:productID AND (licenseID=:licenseID OR parentID=:licenseID OR licenseID IN (
    					SELECT parentID FROM tblLicenses WHERE licenseID=:licenseID
    				))';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('productID', $productID, 'int');
    	$Query->set('licenseID', $this->licenseID(), 'int');

    	$rows = $this->db->get_rows($Query);

    	if (Util::count($rows)) {

    		foreach($rows as $row) {
    			if ($row['productID'] == $productID) {
					$this->update(['licenseID'=>$row['licenseID']]);
    				return true;
    			}
    		}

    	}

    	return false;
    }

}