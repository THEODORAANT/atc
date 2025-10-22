<?php

class Customers extends Factory 
{
	protected $singularClassName = 'Customer';
    protected $table    = 'tblCustomers';
    protected $pk   = 'customerID';

    protected $default_sort_column  = 'customerLastName';  


    /**
     * Find an active customer by their email address.
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public function find_by_email($email)
    {
    	$sql = 'SELECT * 
    			FROM :table
    			WHERE customerEmail=:email
    				AND customerActive=1';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('email', $email);

    	$row = $this->db->get_row($Query);

    	return $this->get_instance($row);
    }

    /**
     * Find an active customer by their referral code.
     * @param  [type] $email [description]
     * @return [type]        [description]
     */
    public function find_by_referrer($code)
    {
        $sql = 'SELECT * 
                FROM :table
                WHERE customerReferralCode=:code
                    AND customerActive=1';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('code', $code);

        $row = $this->db->get_row($Query);

        return $this->get_instance($row);
    }

    public function search($query)
    {
        $sql = 'SELECT * 
                FROM :table
                WHERE customerEmail=:query
                    OR customerEmail LIKE :lquery
                    OR customerEmail LIKE :rquery
                    OR customerCompany LIKE :lquery
                    OR customerCompany LIKE :rquery
                    OR customerCompany = :query
                    OR customerLastName = :query
                    OR customerLastName LIKE :lquery
                    OR customerLastName LIKE :rquery
                    AND customerActive=1';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('query', $query);
        $Query->set('lquery', '%'.$query);
        $Query->set('rquery', $query.'%');

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);       
    }

    public function get_top($Paging)
    {   
        $select = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ';
        
        $sql    = $select . ' c.*, COUNT(licenseID) AS qty  
                    FROM ' . $this->table . ' c, tblLicenses l
                    WHERE l.customerID=c.customerID
                    GROUP BY c.customerID
                    ORDER BY qty DESC';
        
        $sql .= ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
                 

        $rows   = $this->db->get_rows($sql);

        
        $sql    = "SELECT FOUND_ROWS() AS count";
        $total  = $this->db->get_value($sql);
        $Paging->set_total($total);
    

        return $this->get_instances($rows);
    }

    public function get_with_product($productID, $Paging)
    {   
        $select = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ';
        
        $sql    = $select . ' c.*, COUNT(licenseID) AS qty
                    FROM ' . $this->table . ' c, tblLicenses l
                    WHERE l.customerID=c.customerID
                        AND productID='.$this->db->pdb((int)$productID).'
                    GROUP BY c.customerID
                    ORDER BY qty DESC';
        
        $sql .= ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
                 

        $rows   = $this->db->get_rows($sql);

        
        $sql    = "SELECT FOUND_ROWS() AS count";
        $total  = $this->db->get_value($sql);
        $Paging->set_total($total);
    

        return $this->get_instances($rows);
    }

    public function get_top_100()
    {   
        
        // last order
        $last_order_sql = 'SELECT orderDate FROM tblOrders WHERE customerID=c.customerID AND orderStatus='.$this->db->pdb('PAID').'
                            ORDER BY orderDate DESC LIMIT 1';

        // regdev
        $regdev_sql = 'SELECT COUNT(*) FROM tblRegisteredDevelopers 
                        WHERE customerID=c.customerID AND devActive=1 
                            AND devSubscriptionFrom<='.$this->db->pdb(date('Y-m-d H:i:s')).' AND devSubscriptionTo>='.$this->db->pdb(date('Y-m-d H:i:s'));

        // stockpile
        $stockpile_sql = 'SELECT COUNT(*) FROM tblLicenses WHERE customerID=c.customerID AND licenseDomain1="" AND licenseDomain2="" AND licenseDomain3=""';

        // value
        $value_sql = 'SELECT SUM(orderItemsTotal / orderCurrencyRate) FROM tblOrders WHERE customerID=c.customerID AND orderStatus='.$this->db->pdb('PAID');


        $sql    = 'SELECT c.*, COUNT(licenseID) AS qty, 
                        ('.$stockpile_sql.') AS stockpile, 
                        ('.$regdev_sql.') as regdev, 
                        ('.$last_order_sql.') as last_order, 
                        ('.$value_sql.') as value  
                    FROM ' . $this->table . ' c, tblLicenses l
                    WHERE l.customerID=c.customerID AND c.customerID>8
                    GROUP BY c.customerID
                    ORDER BY qty DESC';
        
        $sql .= ' LIMIT 100';
                 

        $rows   = $this->db->get_rows($sql);
   

        return $this->get_instances($rows);
    }

    public function get_for_tagging($count=100, $hours_since_last_tagged=24)
    {
        $date = date('Y-m-d H:i:s', strtotime('-'.$hours_since_last_tagged.' HOURS'));        

        $sql = 'SELECT * 
                FROM :table
                WHERE (customerTagsUpdated<:d OR customerTagsUpdated IS NULL) 
                    AND customerActive=1
                LIMIT :limit';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('limit', $count, 'int');
        $Query->set('d', $date);

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);  
    }

    public function get_historically($date)
    {    

        $sql = 'SELECT * 
                FROM :table
                WHERE customerFirstOrder<=:d 
                    AND customerActive=1';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('d', $date);

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);  
    }

    public function get_all_not_prospects($Paging=false)
    {
        if (Util::count($this->cache)) {
            $out = array();
            foreach($this->cache as $Item){
                $out[] = $Item;
            }
            return $out;
        }
        
        if (is_object($Paging)) {
            $select = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ';
        }else{
            $select = 'SELECT ';
        }
        
        $sql    = $select . ' * 
                    FROM ' . $this->table . '
                    WHERE customerIsProspect=0 '.$this->standard_restrictions();

        if ($this->default_sort_column) {
            $sql .= ' ORDER BY ' . $this->default_sort_column .' '.$this->default_sort_direction;
        }

         
        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $limit  = ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
            $sql    .= $limit;      
        }
               

        $rows   = $this->db->get_rows($sql);

        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $sql    = "SELECT FOUND_ROWS() AS count";
            $total  = $this->db->get_value($sql);
            $Paging->set_total($total);
        }

        return $this->get_instances($rows);
    }


    public function tag_lapsed()
    {
        $sql = 'SELECT * FROM '.$this->table.'
                WHERE customerIsProspect=0 
                    AND customerActive=1
                    AND customerLastOrder IS NOT NULL 
                    AND customerLastOrder<'.$this->db->pdb(date('Y-m-d H:i:s', strtotime('-1 YEAR'))).'
                    AND customerLastOrder!=customerFirstOrder';
        $customers = $this->get_instances($this->db->get_rows($sql));

        if (Util::count($customers)) {
            foreach($customers as $Customer) {
                $Customer->tag('customer:lapsed');
            }
        }
    }

    public function get_for_map_report()
    {
        $sql = 'SELECT customerID, customerLat, customerLng, customerFirstName, customerLastName
                FROM '.$this->table.' WHERE customerActive=1 AND customerLat IS NOT NULL AND customerFirstOrder IS NOT NULL';
        return $this->get_instances($this->db->get_rows($sql));
    }

    public function create_prospect($data)
    {
        if (!isset($data['customerEmail'])) return false; 

        // Does this customer already exist? (by email)
        $Existing = $this->find_by_email($data['customerEmail']);

        if ($Existing) return $Existing;

        $data['customerIsProspect'] = 1;

        return parent::create($data);

    }


}