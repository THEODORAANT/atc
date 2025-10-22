<?php

class LicensedSites extends Factory
{
	protected $singularClassName = 'LicensedSite';
    protected $table    		 = 'tblLicensedSites';
    protected $pk   			 = 'siteID';

    protected $default_sort_column  = 'licenseDomain1';  

    public function get_for_license($slug, $Customer)
    {
    	$sql = 'SELECT l.productID, l.licenseSlug, s.*
    			FROM :licenses l
    				LEFT JOIN :table s ON s.licenseID=l.licenseID		
    			WHERE l.licenseSlug=:slug AND l.customerID=:customerID
    				AND l.licenseActive=1
                    AND siteID IS NOT NULL

    			UNION

    			SELECT l2.productID, l.licenseSlug, s.*
    			FROM :licenses l
    				LEFT JOIN :licenses l2 ON l2.parentID=l.licenseID
    				LEFT JOIN :table s ON s.licenseID=l2.licenseID
    			WHERE l.licenseSlug=:slug AND l.customerID=:customerID
    				AND l.licenseActive=1
    				AND siteID IS NOT NULL

    			';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('licenses', 'tblLicenses', 'table');
    	$Query->set('slug', $slug);
    	$Query->set('customerID', $Customer->id(), 'int');

    	$rows = $this->db->get_rows($Query);

    	return $this->get_instances($rows);
    }

    public function find_for_license($siteID, $slug, $Customer)
    {
    	$sql = 'SELECT l.productID, l.licenseSlug, s.* 
    			FROM :licenses l
    				LEFT JOIN :table s ON s.licenseID=l.licenseID
    			WHERE l.licenseSlug=:slug AND l.customerID=:customerID
    				AND l.licenseActive=1 AND s.siteID=:siteID

    			UNION

    			SELECT l2.productID, l.licenseSlug, s.* 
    			FROM :licenses l
    				LEFT JOIN :licenses l2 ON l2.parentID=l.licenseID
    				LEFT JOIN :table s ON s.licenseID=l2.licenseID
    			WHERE l.licenseSlug=:slug AND l.customerID=:customerID
    				AND l.licenseActive=1 AND s.siteID=:siteID

    			LIMIT 1
    			';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('licenses', 'tblLicenses', 'table');
    	$Query->set('slug', $slug);
    	$Query->set('customerID', $Customer->id(), 'int');
    	$Query->set('siteID', $siteID, 'int');

    	$row = $this->db->get_row($Query);

    	return $this->get_instance($row);
    }

    public function get_count_for_license($licenseID, $include_children=true)
    {
    	$sql = 'SELECT COUNT(*) FROM :table 
    				WHERE licenseID=:licenseID';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('licenseID', $licenseID, 'int');

    	$count = $this->db->get_count($Query);

    	if ($include_children) {
    		$sql = 'SELECT COUNT(*) FROM :table s, :licenses l
    				WHERE s.licenseID=l.licenseID AND l.parentID=:licenseID';

    		$Query = Factory::get('Query', $sql);
    		$Query->set('table', $this->table, 'table');
    		$Query->set('licenses', 'tblLicenses', 'table');
    		$Query->set('licenseID', $licenseID, 'int');

    		$count += $this->db->get_count($Query);

    	}

    	return $count;
    }
}