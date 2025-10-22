<?php

class LocalLicenses extends Factory 
{
	protected $singularClassName = 'LocalLicense';
    protected $table    = 'tblLocalLicenses';
    protected $pk   = 'licenseID';

    protected $default_sort_column  = 'licenseDate';  
    protected $default_sort_direction  = 'DESC';  


    public function get_unactivated($look_back = '1 MONTH')
    {
    	$sql = 'SELECT * FROM :table
    			WHERE licenseDate > :d AND licenseActivated=0';

        $Query = Factory::get('Query', $sql);
        $Query->set('d', date('Y-m-d H:i:s', strtotime('-'.$look_back)));
        $Query->set('table', $this->table, 'table');

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows); 
    }

}