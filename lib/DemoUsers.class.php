<?php

class DemoUsers extends Factory
{
	protected $singularClassName = 'DemoUser';
    protected $table    = 'tblDemoUsers';
    protected $pk   = 'userID';

    protected $default_sort_column  = 'userLastName';  
    protected $created_date_column  = 'userLastName';



    /**
     * Get the currently active demos for the given product and demo node
     * @param  [type] $product [description]
     * @param  [type] $server  [description]
     * @return [type]          [description]
     */
    public function get_current($product, $node)
    {
    	$Conf = Conf::fetch();
    	$dt = new DateTime('now', $Conf->displayTimeZone);

    	$sql = 'SELECT * 
    			FROM :table d, :usertable u
    			WHERE d.userID=u.userID
    				AND demoProduct=:product
    				AND (demoNode=:node OR demoNode=:any)
    				AND demoStatus IN (:status)
    				AND demoValidFrom <= :now
    				AND demoValidTo >= :now
    				AND demoHost != :nothing';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('usertable', 'tblDemoUsers', 'table');
    	$Query->set('product', $product);
    	$Query->set('node', $node);
    	$Query->set('any', '*');
    	$Query->set('status', "'PENDING', 'LIVE'", 'sql');
    	$Query->set('now', $dt->format('Y-m-d H:i:s'));
    	$Query->set('nothing', '');

    	$rows = $this->db->get_rows($Query);

    	return $this->get_instances($rows);

    }


}