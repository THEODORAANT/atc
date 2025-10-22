<?php

class Demos extends Factory
{
	protected $singularClassName = 'Demo';
    protected $table    = 'tblDemos';
    protected $pk   = 'demoID';

    protected $default_sort_column  = 'demoValidFrom';  
    protected $created_date_column  = 'demoCreated';



    /**
     * Get the currently active demos for the given product and demo server
     * @param  [type] $product [description]
     * @param  [type] $server  [description]
     * @return [type]          [description]
     */
    public function get_current($product, $server)
    {
    	$Conf = Conf::fetch();
    	$dt = new DateTime('now', $Conf->displayTimeZone);

    	$sql = 'SELECT demoID, demoHost, demoUsername, demoPassword, demoNode, demoSite, demoVersion, userFirstName, userLastName, userEmail
    			FROM :table d, :usertable u
    			WHERE d.userID=u.userID
    				AND demoProduct=:product
    				AND (demoNode=:server OR demoNode=:any)
    				AND demoStatus IN (:status)
    				AND demoValidFrom <= :now
    				AND demoValidTo >= :now
    				AND demoHost != :nothing';

    	$Query = Factory::get('Query', $sql);
    	$Query->set('table', $this->table, 'table');
    	$Query->set('usertable', 'tblDemoUsers', 'table');
    	$Query->set('product', $product);
    	$Query->set('server', $server);
    	$Query->set('any', '*');
    	$Query->set('status', "'PENDING', 'LIVE'", 'sql');
    	$Query->set('now', $dt->format('Y-m-d H:i:s'));
    	$Query->set('nothing', '');

    	$rows = $this->db->get_rows($Query);

    	return $this->get_instances($rows);
    }

    public function get_pending_count_for_user($userID,$product='perch')
    {

        $sql = 'SELECT count(*) 
                FROM :table d
                WHERE demoProduct=:product
                AND demoStatus = :status
                AND userID = :userID';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('usertable', 'tblDemoUsers', 'table');
        $Query->set('product', $product);
        $Query->set('status', 'PENDING');
        $Query->set('userID', $userID);

        return $this->db->get_count($Query);
    }

    public function get_live_for_user($userID,$product='perch')
    {
        $sql = 'SELECT demoID, demoHost, demoUsername, demoPassword, demoNode, demoSite, demoVersion, userFirstName, userLastName, userEmail
                FROM :table d, :usertable u
                WHERE d.userID=u.userID
                    AND demoProduct=:product
                    AND demoStatus = :status
                    AND u.userID = :userID';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('usertable', 'tblDemoUsers', 'table');
        $Query->set('product', $product);
        $Query->set('status', 'LIVE');
        $Query->set('userID', $userID);

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);
    }


    public function get_ended_needing_cleanup($product, $server, $count=false)
    {
        $Conf = Conf::fetch();
        $dt = new DateTime('now', $Conf->displayTimeZone);

        $sql = 'SELECT demoHost, demoNode, demoSite
                FROM :table
                WHERE demoProduct=:product
                    AND demoNode=:server
                    AND demoStatus=:status
                    AND demoValidTo<:now
                    AND demoHost!=:nothing';

        if ($count) $sql .= ' LIMIT :count';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('product', $product);
        $Query->set('server', $server);
        $Query->set('status', 'LIVE');
        $Query->set('now', $dt->format('Y-m-d H:i:s'));
        $Query->set('nothing', '');
        if ($count) $Query->set('count', $count, 'int');

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);
    }


    public function get_current_older_than($product='perch', $hours=24)
    {
        $Conf = Conf::fetch();

        $sql = 'SELECT *
                FROM :table
                WHERE demoProduct=:product
                    AND demoStatus = :status
                    AND demoValidFrom <= :now
                    AND demoValidTo >= :now
                    AND demoHost != :nothing
                    AND demoCreated <= :ago';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('product', $product);
        $Query->set('any', '*');
        $Query->set('status', 'LIVE');
        $Query->set('now', Util::time_now());
        $Query->set('nothing', '');
        $Query->set('ago', date('Y-m-d H:i:s', strtotime(Util::time_now().' -'.$hours.' HOURS')));

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);
    }



    public function generate_password()
    {
        $Conf = Conf::fetch();
        
        $dictionary = $Conf->site . '/data/passwd.txt';
        
        if (file_exists($dictionary)){
            
            $words  = array();
            $fp     = fopen($dictionary, "r");
            
            while (!feof($fp)){
                $words[]    = trim(fgets($fp, 1024));
            }
            
            fclose($fp);
            
            $word1  = $words[mt_rand(0, count($words)-1)];
            $word2  = $words[mt_rand(0, count($words)-1)];
            
            
            return $word1.'-'.$word2;
            
        }
            
        return false;
    }

    public function get_headline_stats()
    {
        $out = array();
        
        // Demos today
        $sql = 'SELECT COUNT(*)
                FROM '.$this->table.'
                WHERE demoCreated BETWEEN '.$this->db->pdb(date('Y-m-d 00:00:00')).' AND '.$this->db->pdb(date('Y-m-d 23:59:59')).'
                LIMIT 1';
        $out['today'] = (int) $this->db->get_value($sql);
        

        // Demo Users today
        $sql = 'SELECT COUNT(DISTINCT userID)
                FROM '.$this->table.'
                WHERE demoCreated BETWEEN '.$this->db->pdb(date('Y-m-d 00:00:00')).' AND '.$this->db->pdb(date('Y-m-d 23:59:59')).'
                LIMIT 1';
        $out['users_today'] = (int) $this->db->get_value($sql);
        
        // Demos this week
        $sql = 'SELECT COUNT(*)
                FROM '.$this->table.'
                WHERE demoCreated BETWEEN '.$this->db->pdb(date('Y-m-d 00:00:00', strtotime('last Monday'))).' AND '.$this->db->pdb(date('Y-m-d 23:59:59')).'
                LIMIT 1';
        $out['week'] = (int) $this->db->get_value($sql);
        
        
        // Demos this month
        $sql = 'SELECT COUNT(*)
                FROM '.$this->table.'
                WHERE demoCreated BETWEEN '.$this->db->pdb(date('Y-m-01 00:00:00')).' AND '.$this->db->pdb(date('Y-m-d 23:59:59')).'
                LIMIT 1';
        $out['month'] = (int) $this->db->get_value($sql);
                
        
        // Average per day
        $sql = 'SELECT AVG(qty) FROM (
                    SELECT date_format(demoCreated, \'%Y-%m-%d\') as `date`, COUNT(*) as qty
                    FROM '.$this->table.'
                    GROUP BY `date`
                ) as mytable
                LIMIT 1';
        $out['average'] = number_format((float) $this->db->get_value($sql), 1);

        return $out;
    }

    public function get_referrals()
    {
        $out = array();
        
        // Recent referrsers
        $sql = 'SELECT refText AS ref
                        FROM tblDemoReferrals
                        WHERE refText != \'\'
                        ORDER BY refCreated DESC
                        LIMIT 25';
        $rows = $this->db->get_rows($sql);
        return $rows; 

        $refs = array();
        if (Util::count($rows)) {
            foreach($rows as $row) {
                $refs[] = $row['ref'];
            }
        }

        return $refs;
    }

    public function get_pending_for_admin()
    {
        $Conf = Conf::fetch();
        $dt = new DateTime('now', $Conf->displayTimeZone);

        $sql = 'SELECT demoID, demoStatus, demoHost, demoUsername, demoPassword, demoNode, demoSite, demoVersion, userFirstName, userLastName, userEmail
                FROM :table d, :usertable u
                WHERE d.userID=u.userID
                    AND demoStatus IN (:status)
                    AND demoValidFrom <= :now
                    AND demoValidTo >= :now
                    AND demoHost != :nothing
                ORDER BY demoCreated DESC';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('usertable', 'tblDemoUsers', 'table');
        $Query->set('any', '*');
        $Query->set('status', "'PENDING'", 'sql');
        $Query->set('now', $dt->format('Y-m-d H:i:s'));
        $Query->set('nothing', '');

        $rows = $this->db->get_rows($Query);

        return $this->get_instances($rows);
    }


}


