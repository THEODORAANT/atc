<?php

class Activations extends Factory 
{
	protected $singularClassName = 'Activation';
    protected $table    = 'dbActivation.tblActivationLog';
    protected $pk   = 'id';

    protected $default_sort_column  = 'logtime';  

    
    public function get_for_license($licenseKey, $Paging)
    {

        if (is_object($Paging)) {
            $select = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ';
        }else{
            $select = 'SELECT ';
        }

        $sql = $select.'* FROM :table
            WHERE licenseKey=:key
            ORDER BY logtime DESC';

        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $limit  = ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
            $sql    .= $limit;      
        }

    	$Query = Factory::get('Query', $sql);

        $Query->set('table', $this->table, 'sql');    
        
    	$Query->set('key', substr($licenseKey, 0, 34));
        
    	$rows = $this->db->get_rows($Query);

        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $sql    = "SELECT FOUND_ROWS() AS count";
            $total  = $this->db->get_value($sql);
            $Paging->set_total($total);
        }

    	return $this->get_instances($rows);
    }

    public function get_failures($Paging, $exclude_noise=true)
    {

        if (is_object($Paging)) {
            $select = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ';
        }else{
            $select = 'SELECT ';
        }

        $sql = $select.'* FROM :table ';    

        if ($exclude_noise) {
            $sql .= ' WHERE post NOT LIKE "%johnsmiths%" ';
        }

        $sql .= '    ORDER BY logtime DESC';


        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $limit  = ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
            $sql    .= $limit;      
        }

        $Query = Factory::get('Query', $sql);

        $Query->set('table', 'dbActivation.tblActivationFailure', 'sql');    
        
        
        $rows = $this->db->get_rows($Query);

        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $sql    = "SELECT FOUND_ROWS() AS count";
            $total  = $this->db->get_value($sql);
            $Paging->set_total($total);
        }
        
        return $this->get_instances($rows);
    }

}