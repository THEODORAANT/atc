<?php

class CustomerTags extends Factory 
{
	protected $singularClassName = 'CustomerTag';
    protected $table    = 'tblCustomerTags';
    protected $pk   = 'id';

    protected $default_sort_column  = 'id';  

    protected $updateable_tag_prefixes = ['licenses'];

    public function get_current_for_customer($customerID, $tag_type) 
    {
    	$sql = 'SELECT * FROM :table
    			WHERE customerID=:customerID
    				AND tag LIKE :tag';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('customerID', $customerID, 'int');
    	$Query->set('tag', $tag_type.':%');

    	$row = $this->db->get_row($Query);

    	return $this->get_instance($row);  
    }

    public function get_all_current_for_customer($customerID, $tag_type)
    {
        $sql = 'SELECT * FROM :table
                WHERE customerID=:customerID
                    AND tag LIKE :tag';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('tag', $tag_type.':%');

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);
    }

    public function get_tag_at_date_for_customer($customerID, $tag_type, $date) 
    {
        $sql = 'SELECT *, to_tag AS tag FROM tblCustomerTagHistory
                WHERE customerID=:customerID
                    AND to_tag LIKE :tag
                    AND timestamp<=:date
                ORDER BY timestamp DESC
                LIMIT 1';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('tag', $tag_type.':%');
        $Query->set('date', $date);

        $row = $this->db->get_row($Query);

        return $this->get_instance($row);  
    }

    public function set_for_customer($customerID, $tag)
    {
    	$parts = explode(':', $tag);
    	$tag_type = $parts[0];

        $from_tag = false;

        // Is this tag updatable?
        // (e.g. licenses:committed replaces licenses:new, but demo:loggedin doesn't replace demo:signup)
        
        if (in_array($tag_type, $this->updateable_tag_prefixes)) {
            // find the existing tag
            $From_tag = $this->get_current_for_customer($customerID, $tag_type);
    
            if ($From_tag) $from_tag = $From_tag->tag();

            // delete the existing tag
            $this->_delete_tag($from_tag, $customerID);
        }

    	// set the new tag
    	$this->_set_tag($customerID, $tag);
    	 
    	// log the change
    	$this->_log_tag_change($from_tag, $tag, $customerID);
    }

    public function set_for_customer_historically($customerID, $tag, $date)
    {
        $parts = explode(':', $tag);
        $tag_type = $parts[0];

        // find the existing tag
        $From_tag = $this->get_tag_at_date_for_customer($customerID, $tag_type, $date);
        $from_tag = false;
        if ($From_tag) $from_tag = $From_tag->tag();
         
        // log the change
        $this->_log_tag_change($from_tag, $tag, $customerID, $date);
    }

    public function detag_customer($customerID, $tag)
    {
        $this->_delete_tag($tag, $customerID);

        // log the change
        $this->_log_tag_change($tag, '', $customerID);
    }

    private function _delete_tag($from_tag, $customerID)
    {
    	$sql = 'DELETE FROM :table 
    			WHERE customerID=:customerID
    				AND tag=:tag
    			LIMIT 1';
    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('customerID', $customerID, 'int');
    	$Query->set('tag', $from_tag);	

    	$this->db->execute($Query);

        
    }

    private function _set_tag($customerID, $tag)
    {
    	$this->create([
			'tag'        => $tag,
			'customerID' => $customerID
    		]);
    }

    private function _log_tag_change($from_tag, $to_tag, $customerID, $date=false)
    {
        if ($from_tag == $to_tag) return;

    	$data = [
			'customerID' => (int)$customerID,
			'from_tag'   => $from_tag,
			'to_tag'     => $to_tag
    	];

        if ($date) {
            $data['timestamp'] = $date;
        }

    	$this->db->insert('tblCustomerTagHistory', $data);
    

    }

}