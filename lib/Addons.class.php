<?php

class Addons extends Factory 
{
	protected $singularClassName = 'Addon';
    protected $table    = 'tblAddons';
    protected $pk   = 'addonID';

    protected $default_sort_column  = 'addonName';  


    public function get_latest()
    {
        $sql = 'SELECT addonName AS `name`, addonType AS `type`, addonVersion AS `version` ,IF(DATEDIFF(CURDATE(), addonUpdatedDate)<30, true, false)  AS addonNewVersion
               FROM '.$this->table.' 
                ORDER BY addonType, addonName';
        return $this->db->get_rows($sql);
    }

}
