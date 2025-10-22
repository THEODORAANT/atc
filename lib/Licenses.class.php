<?php

class Licenses extends Factory 
{
	protected $singularClassName = 'License';
    protected $table    = 'tblLicenses';
    protected $pk   = 'licenseID';

    protected $default_sort_column  = 'licenseDate';  
    protected $default_sort_direction  = 'DESC';  

    /**
     * Get licenses for the given customer and given product. If a slug is passed in, just get the single matching item
     * @param  [type] $customerID [description]
     * @param  [type] $productID  [description]
     * @param  [type] $licenseSlug  [description]
     * @return [type]             [description]
     */
    public function get_for_customer($customerID, $productID, $licenseSlug=false)
    {

        if ($licenseSlug) {
            #$sql = 'SELECT * FROM :table l, :versions v 
            #    WHERE l.versionID=v.versionID AND l.customerID=:customerID AND l.productID=:productID AND l.licenseActive=1 AND l.licenseSlug=:slug
            #    LIMIT 1';

            $sql = 'SELECT l.*, v.*, l2.licenseKey AS companionKey, l2.licenseSlug AS companionSlug
                    FROM :table l
                    JOIN :versions v ON l.versionID=v.versionID
                    LEFT JOIN :table l2 ON l2.parentID=l.licenseID
                    WHERE l.customerID=:customerID AND l.productID=:productID AND l.licenseActive=1 AND l.licenseSlug=:slug
                LIMIT 1';
        }else{
            #$sql = 'SELECT * FROM :table l, :versions v 
            #    WHERE l.versionID=v.versionID AND l.customerID=:customerID AND l.productID=:productID AND l.licenseActive=1 
            #    ORDER BY l.licenseMultisite=1 DESC, l.licenseDate DESC';

            $sql = 'SELECT l.*, v.*, l2.licenseSlug AS parentLicenseSlug, l2.licenseDesc AS parentLicenseDesc
                    FROM :table l
                        JOIN :versions v ON l.versionID=v.versionID
                        LEFT JOIN :table l2 ON l.parentID=l2.licenseID
                WHERE l.customerID=:customerID AND l.productID=:productID AND l.licenseActive=1 
                ORDER BY l.licenseMultisite=1 DESC, l.licenseDate DESC';
        }

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('versions', 'tblProductVersions', 'table');
    	$Query->set('customerID', $customerID, 'int');
        $Query->set('productID', $productID, 'int');
 	

        if ($licenseSlug) {
            $Query->set('slug', $licenseSlug);
            $row = $this->db->get_row($Query);
            return $this->get_instance($row);
        }

    	$rows = $this->db->get_rows($Query);
    	return $this->get_instances($rows);
    }

    public function get_for_order($orderID, $customerID)
    {
        $sql = 'SELECT l.*, v.*, l2.licenseSlug AS parentLicenseSlug, l2.licenseDesc AS parentLicenseDesc 
                    FROM :table l
                        JOIN :versions v ON l.versionID=v.versionID
                        LEFT JOIN :table l2 ON l.parentID=l2.licenseID
                WHERE l.customerID=:customerID AND l.licenseActive=1 AND l.orderID=:orderID 
                ORDER BY l.licenseMultisite=1 DESC, l.licenseDate DESC';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('versions', 'tblProductVersions', 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('orderID', $orderID, 'int');

        $rows = $this->db->get_rows($Query);
        return $this->get_instances($rows);
    }

    public function get_by_slug($slug, $customerID)
    {
        $sql = 'SELECT * FROM :table l
                WHERE l.customerID=:customerID AND l.licenseActive=1 AND l.licenseSlug=:slug
                LIMIT 1';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('slug', $slug);
        
        $row = $this->db->get_row($Query);
        return $this->get_instance($row);
    }

    public function create($Product, $ProductVersion, $customerID, $orderID)
    {       
        return parent::_create([
                'licenseKey'        => $Product->generate_license_key($ProductVersion),
                'licenseDate'       => date('Y-m-d H:i:s'),
                'licenseDesc'       => $ProductVersion->versionName() . ' - ' .date('d F Y'),
                'licenseIgnoreHost' => '0',
                'licenseActive'     => '1',
                'licenseDomain1'    => '',
                'licenseDomain2'    => '',
                'licenseDomain3'    => '',
                'customerID'        => $customerID,
                'productID'         => $Product->id(),
                'versionID'         => $ProductVersion->id(),
                'licenseSlug'       => $Product->generate_license_slug($ProductVersion, $customerID, $orderID),
                'orderID'           => $orderID,
            ]);
    }

    public function create_subscriptionLisence($Product, $ProductVersion, $customerID, $orderID, $Subscription, $Companion=false, $qty=1)
        {
            $productID = $Product->id();
            if ($Product->parentID()) {
                $productID = $Product->parentID();
            }

            $License = parent::_create([
                    'licenseKey'            => $Product->generate_license_key($ProductVersion),
                    'licenseDate'           => date('Y-m-d H:i:s'),
                    'licenseDesc'           => $ProductVersion->versionName() . 'Subscription - ' .date('d F Y'),
                    'licenseIgnoreHost'     => '0',
                    'licenseActive'         => '1',
                    'licenseDomain1'        => '',
                    'licenseDomain2'        => '',
                    'licenseDomain3'        => '',
                    'customerID'            => $customerID,
                    'productID'             => $productID,
                    'versionID'             => $ProductVersion->id(),
                    'licenseSlug'           => $Product->generate_license_slug($ProductVersion, $customerID, $orderID),
                    'orderID'               => $orderID,
                    'subscriptionID'        => $Subscription->id(),
                ]);
       return $License;
    }

    public function create_multisite($Product, $ProductVersion, $customerID, $orderID, $Subscription, $Companion=false, $qty=1)
    {
        $productID = $Product->id();
        if ($Product->parentID()) {
            $productID = $Product->parentID();
        }

        $License = parent::_create([
                'licenseKey'            => $Product->generate_license_key($ProductVersion),
                'licenseDate'           => date('Y-m-d H:i:s'),
                'licenseDesc'           => $ProductVersion->versionName() . ' Multi-site - ' .date('d F Y'),
                'licenseIgnoreHost'     => '0',
                'licenseActive'         => '1',
                'licenseDomain1'        => '',
                'licenseDomain2'        => '',
                'licenseDomain3'        => '',
                'customerID'            => $customerID,
                'productID'             => $productID,
                'versionID'             => $ProductVersion->id(),
                'licenseSlug'           => $Product->generate_license_slug($ProductVersion, $customerID, $orderID),
                'orderID'               => $orderID,
                'licenseMultisite'      => '1',
                'licenseMultisiteLimit' => ((int)$Product->productLicenseCount() * $qty),
                'subscriptionID'        => $Subscription->id(),
            ]);

        if ($Companion) {
            $CompanionVersion = $Companion->get_latest_version();

            parent::_create([
                'licenseKey'            => $Companion->generate_license_key($CompanionVersion),
                'licenseDate'           => date('Y-m-d H:i:s'),
                'licenseDesc'           => $CompanionVersion->versionName() . ' Multi-site - ' .date('d F Y'),
                'licenseIgnoreHost'     => '0',
                'licenseActive'         => '1',
                'licenseDomain1'        => '',
                'licenseDomain2'        => '',
                'licenseDomain3'        => '',
                'customerID'            => $customerID,
                'productID'             => $Companion->id(),
                'versionID'             => $CompanionVersion->id(),
                'licenseSlug'           => $Companion->generate_license_slug($CompanionVersion, $customerID, $orderID),
                'orderID'               => $orderID,
                'licenseMultisite'      => '1',
                'licenseMultisiteLimit' => '0',
                'parentID'              => $License->id(),
                'subscriptionID'        => $Subscription->id(),
            ]);
        }

        return $License;
    }

    public function search($query)
    {
        $sql = 'SELECT * 
                FROM :table
                WHERE licenseKey=:query
                    OR licenseDomain1 =:query
                    OR licenseDomain1 LIKE :lquery
                    OR licenseDomain1 LIKE :rquery
                    OR licenseDomain2 =:query
                    OR licenseDomain2 LIKE :lquery
                    OR licenseDomain2 LIKE :rquery
                    OR licenseDomain3 =:query
                    OR licenseDomain3 LIKE :lquery
                    OR licenseDomain3 LIKE :rquery
                    ';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('query', $query);
        $Query->set('lquery', '%'.$query);
        $Query->set('rquery', $query.'%');

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);       
    }



}
