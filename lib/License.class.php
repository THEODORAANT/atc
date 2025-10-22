<?php

class License extends Core_Base 
{
	protected $table  = 'tblLicenses';
    protected $pk     = 'licenseID';


    /**
     * Apply the given upgrade to the license
     * @param  [type] $Upgrade [description]
     * @return [type]          [description]
     */
    public function apply_upgrade($Upgrade)
    {
    	$Licenses = Factory::get('Licenses');
    	$Products = Factory::get('Products');

        $this->db->insert('tblUpgradedLicenses', [
                'licenseID'         => $this->details['licenseID'],
                'licenseKey'        => $this->details['licenseKey'],
                'licenseDomain1'    => $this->details['licenseDomain1'],
                'licenseDomain2'    => $this->details['licenseDomain2'],
                'licenseDomain3'    => $this->details['licenseDomain3'],
                'licenseDate'       => $this->details['licenseDate'],
                'licenseDesc'       => $this->details['licenseDesc'],
                'licenseIgnoreHost' => $this->details['licenseIgnoreHost'],
                'licenseActive'     => $this->details['licenseActive'],
                'customerID'        => $this->details['customerID'],
                'productID'         => $this->details['productID'],
                'versionID'         => $this->details['versionID'],
                'licenseSlug'       => $this->details['licenseSlug'],
                'orderID'           => $this->details['orderID'],
            ]);

    	$Product = $Products->find($Upgrade->productID());
    	$Version = $Product->get_latest_version();

    	$Upgrade->mark_spent($this->id());

    	$this->update([
    		'licenseKey' => $Product->generate_license_key($Version),
    		'versionID'  => $Version->id(),
            'productID'  => $Product->id(), 
    		]);


    }

    public function transfer_to($customerID)
    {
        $this->update(['customerID'=>$customerID]);
    }

    public function get_site_count()
    {
        $LicensedSites = Factory::get('LicensedSites');
        return $LicensedSites->get_count_for_license($this->id(), true);
    }

}




