<?php

class LocalLicense extends Core_Base 
{
	protected $table  = 'tblLocalLicenses';
    protected $pk     = 'licenseID';

    public function attempt_to_match_with_customer()
    {
    	$Customers = Factory::get('Customers');
    	$Customer = $Customers->find_by_email($this->licenseEmail());

    	if ($Customer) {
    		if ($Customer->id() == $this->customerID()) {
    			return $Customer;
    		}

    		$this->update([
    			'customerID' => $Customer->id(),
    			]);

    		return $Customer;
    	}

    	return false;
    }

    public function count_activations()
    {
    	$sql = 'SELECT COUNT(*) FROM dbActivation.tblActivationLog
    			WHERE licenseKey = :key';

        $Query = Factory::get('Query', $sql);
        $Query->set('key', $this->licenseKey());

        return (int) $this->db->get_count($Query);
    }
}