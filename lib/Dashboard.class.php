<?php

class Dashboard extends Factory
{
	public function get_licenses_for_date($itemCode='PERCH', $date1=false, $date2=false)
	{
	    if ($date1===false) {
	        $date1 = date('Y-m-d');
	        $date2 = date('Y-m-d');
	    }
	    
	    if ($date2===false) {
	        $date2 = $date1;
	    }
        
        $sql = 'SELECT SUM(oi.itemQty)
                FROM tblOrders o, tblOrderItems oi
                WHERE o.orderID=oi.orderID 
                    AND oi.itemCode='.$this->db->pdb($itemCode).' 
                    AND o.orderStatus=\'PAID\' 
                    AND o.orderRefund<o.orderItemsTotal AND o.orderValue>0 
                    AND orderDate BETWEEN '.$this->db->pdb($date1.' 00:00:00').' AND '.$this->db->pdb($date2.' 23:59:59').'
                LIMIT 1';
                
        return (int) $this->db->get_value($sql);
	    	    
	    return 0;
	}

	public function get_local_licenses_for_date($itemCode='PERCH', $date1=false, $date2=false)
	{
	    if ($date1===false) {
	        $date1 = date('Y-m-d');
	        $date2 = date('Y-m-d');
	    }
	    
	    if ($date2===false) {
	        $date2 = $date1;
	    }
        
        $sql = 'SELECT COUNT(DISTINCT licenseEmail)
                FROM tblLocalLicenses
                WHERE licenseKey LIKE '.$this->db->pdb(substr($itemCode, 0, 1).'%').' 
                    AND licenseDate BETWEEN '.$this->db->pdb($date1.' 00:00:00').' AND '.$this->db->pdb($date2.' 23:59:59').'
                LIMIT 1';
                
        return (int) $this->db->get_value($sql);
	    	    
	    return 0;
	}

	public function get_downloads_for_date($productID=PROD_PERCH, $date1=false, $date2=false)
	{
	    if ($date1===false) {
	        $date1 = date('Y-m-d');
	        $date2 = date('Y-m-d');
	    }
	    
	    if ($date2===false) {
	        $date2 = $date1;
	    }
        
        $sql = 'SELECT COUNT(*)
                FROM tblDownloads
                WHERE productID='.$this->db->pdb((int)$productID).'
                    AND downloadDateTime BETWEEN '.$this->db->pdb($date1.' 00:00:00').' AND '.$this->db->pdb($date2.' 23:59:59').'
                LIMIT 1';
                
        return (int) $this->db->get_value($sql);
	    	    
	    return 0;
	}

	public function get_average_licenses_per_day_this_month($itemCode='PERCH')
	{
	    $sql = "SELECT AVG(qty) as av_qty FROM (

	            SELECT DATE(o.orderDate) as orderDate, SUM(oi.itemQty) AS qty
	            FROM tblOrders o, tblOrderItems oi
	            WHERE o.orderID=oi.orderID AND oi.itemCode=".$this->db->pdb($itemCode)." AND o.orderStatus='PAID' AND o.orderRefund<o.orderItemsTotal AND o.orderValue>0 AND orderDate BETWEEN '".date('Y-m-01')." 00:00:00' AND '".date('Y-m-d')." 23:59:59'

	            GROUP BY DATE(orderDate)
	            ORDER BY orderDate ASC) AS mytable";
	            
	    return number_format((float) $this->db->get_value($sql), 1);
	}
	public function get_average_licenses_per_day($itemCode='PERCH')
	{
	    $sql = "SELECT AVG(qty) as av_qty FROM (

	            SELECT DATE(o.orderDate) as orderDate, SUM(oi.itemQty) AS qty
	            FROM tblOrders o, tblOrderItems oi
	            WHERE o.orderID=oi.orderID AND oi.itemCode=".$this->db->pdb($itemCode)." AND o.orderStatus='PAID' AND o.orderRefund<o.orderItemsTotal AND o.orderValue>0 
	            GROUP BY DATE(orderDate)
	            ORDER BY orderDate ASC) AS mytable";
	            
	    return number_format((float) $this->db->get_value($sql), 1);
	}

	public function get_customer_count()
	{
	    // total customers
	    $sql = 'SELECT COUNT(DISTINCT customerID) AS qty
	            FROM tblOrders
	            WHERE orderStatus="PAID" AND orderRefund<orderItemsTotal';
	    $all = $this->db->get_count($sql);
	  
	    return $all;
	}

	public function get_percent_repeat()
	{
	    // total customers
	    $sql = 'SELECT COUNT(DISTINCT customerID) AS qty
	            FROM tblOrders
	            WHERE orderStatus="PAID" AND orderRefund<orderItemsTotal';
	    $all = $this->db->get_count($sql);
	    
	    // repeat customers
	    $sql = 'SELECT COUNT(*) AS ppl
	            FROM (
	                SELECT COUNT(*) AS qty, customerID
	                FROM tblOrders
	                WHERE orderStatus="PAID" AND orderRefund<orderItemsTotal 
	                GROUP BY customerID
	                HAVING qty>1
	            ) AS mytable';
	    $repeat = $this->db->get_count($sql);
	    
	    return number_format(($repeat/$all)*100,0);
	}

	public function get_percent_repeat_this_month()
	{
	    // total customers
	    $sql = 'SELECT DISTINCT customerID
	            FROM tblOrders
	            WHERE orderStatus="PAID" AND orderRefund<orderItemsTotal
	                AND orderDate BETWEEN '.$this->db->pdb(date('Y-m-01 00:00:00')).' AND '.$this->db->pdb(date('Y-m-d 23:59:59'));
	    $all = $this->db->get_rows($sql);
	    $all_count = Util::count($all);
	    $all_ids = '0';
	    
	    if ($all_count) {
	        $out = array();
	        foreach($all as $row) {
	            $out[] = $row['customerID'];
	        }
	        $all_ids = Util::implode_for_sql_in($out, $this->db);
	    }
	            
	    
	    // repeat customers
	    $sql = 'SELECT COUNT(*) AS ppl
	            FROM (
	                SELECT COUNT(*) AS qty, customerID
	                FROM tblOrders
	                WHERE orderStatus="PAID" AND orderRefund<orderItemsTotal 
	                    AND customerID IN ('.$all_ids.')
	                GROUP BY customerID
	                HAVING qty>1
	            ) AS mytable';
	    //Util::debug($sql);
	    $repeat = $this->db->get_count($sql);
	    
	    if ($all_count ==0) return 0;
	    
	    return number_format(($repeat/$all_count)*100,0);
	}

	public function get_license_stockpile()
	{
		$sql = 'SELECT COUNT(*) AS qty
		        FROM tblLicenses l, tblProductVersions pv
		        WHERE l.versionID=pv.versionID AND pv.versionMajor=2 AND pv.productID=1 
		        	AND licenseDomain1="" AND licenseDomain2="" AND licenseDomain3="" 
		        	AND customerID>8 AND licenseActive=1 AND subscriptionID IS NULL';
		return $this->db->get_count($sql);
	}

	public function get_license_total_perch_2()
	{
		$sql = 'SELECT COUNT(*) AS qty
		        FROM tblLicenses l, tblProductVersions pv
		        WHERE l.versionID=pv.versionID AND pv.versionMajor=2 AND pv.productID=1 
		        AND customerID>8';
		return $this->db->get_count($sql);
	}

	public function get_license_total_runway_crossgrade()
	{
		$sql = 'SELECT COUNT(*) AS qty
		        FROM tblLicenses l
		        WHERE l.productID='.PROD_RUNWAYDEV.' AND licenseSlug NOT LIKE '.$this->db->pdb('R2%').' AND licenseActive=1 
		        AND customerID>8';
		return $this->db->get_count($sql);
	}

	public function get_customer_tag_count($tag)
	{
		$sql = 'SELECT COUNT(*) FROM tblCustomerTags WHERE tag='.$this->db->pdb($tag);
		return $this->db->get_count($sql);
	}

	public function get_revenue_for_date($date1, $date2)
	{
	    if ($date1===false) {
	        $date1 = date('Y-m-d');
	        $date2 = date('Y-m-d');
	    }
	    
	    if ($date2===false) {
	        $date2 = $date1;
	    }
        $sql = 'SELECT ROUND(SUM((orderItemsTotal-orderRefund)/orderCurrencyRate))
                FROM tblOrders 
                WHERE orderStatus=\'PAID\' 
                    AND orderRefund<orderItemsTotal AND orderValue>0 
                    AND orderDate BETWEEN '.$this->db->pdb($date1.' 00:00:00').' AND '.$this->db->pdb($date2.' 23:59:59').'
                LIMIT 1';
                
        return (int) $this->db->get_value($sql);
	    	    
	    return 0;
	}

	public function get_subscription_payments_for_date($date1=false, $date2=false)
	{
	    if ($date1===false) {
	        $date1 = date('Y-m-d');
	        $date2 = date('Y-m-d');
	    }
	    
	    if ($date2===false) {
	        $date2 = $date1;
	    }
        
        $sql = 'SELECT SUM(oi.itemQty)
                FROM tblOrders o, tblOrderItems oi
                WHERE o.orderID=oi.orderID 
              
                    AND o.orderStatus=\'PAID\' 
                    AND o.orderRefund<o.orderItemsTotal AND o.orderValue>0 
                    AND o.subscriptionID IS NOT NULL 
                    AND orderDate BETWEEN '.$this->db->pdb($date1.' 00:00:00').' AND '.$this->db->pdb($date2.' 23:59:59').'
                LIMIT 1';
                
        return (int) $this->db->get_value($sql);
	    	    
	    return 0;
	}

	public function get_new_subscriptions_for_date($date1=false, $date2=false)
	{
	    if ($date1===false) {
	        $date1 = date('Y-m-d');
	        $date2 = date('Y-m-d');
	    }
	    
	    if ($date2===false) {
	        $date2 = $date1;
	    }
        
        $sql = 'SELECT COUNT(*)
                FROM tblSubscriptions
                WHERE subCreated BETWEEN '.$this->db->pdb($date1.' 00:00:00').' AND '.$this->db->pdb($date2.' 23:59:59').'
                LIMIT 1';
                
        return (int) $this->db->get_value($sql);
	    	    
	    return 0;
	}

}
