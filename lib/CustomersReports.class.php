<?php

class CustomersReports extends Customers
{

	public function get_license_tag_breakdown()
	{
		$sql = 'SELECT REPLACE(tag, \'licenses:\', \'\')  AS Tag, count(*) AS Qty
				FROM tblCustomerTags
				WHERE tag LIKE \'licenses:%\'
				GROUP BY tag
				ORDER BY tag=\'licenses:new\' DESC, tag=\'licenses:casual\' DESC, tag=\'licenses:committed\' DESC, tag=\'licenses:super\' DESC';
		return $this->db->get_rows($sql);
	}

	public function get_segment_buy_intervals()
	{
		$sql = 'SELECT REPLACE(t.tag, \'licenses:\', \'\') AS Tag,
					ROUND(AVG(customerOrderInterval)/60/60/24) AS Days
				FROM tblCustomers c, tblCustomerTags t
				WHERE t.customerID=c.customerID 
					AND customerOrderInterval IS NOT NULL AND customerOrderInterval>86400
					AND t.tag LIKE \'licenses:%\'
				GROUP BY t.tag
				ORDER BY tag=\'licenses:new\' DESC, tag=\'licenses:casual\' DESC, tag=\'licenses:committed\' DESC, tag=\'licenses:super\' DESC';

		return $this->db->get_rows($sql);
	}

	public function get_report_by_tag($tag)
	{
		// stockpile
		$stockpile_sql = 'SELECT COUNT(*) FROM tblLicenses WHERE customerID=c.customerID AND licenseDomain1="" AND licenseDomain2="" AND licenseDomain3=""';

		// value
		$value_sql = 'SELECT SUM(orderItemsTotal / orderCurrencyRate) FROM tblOrders WHERE customerID=c.customerID AND orderStatus='.$this->db->pdb('PAID');


		$sql = 'SELECT c.*,
				('.$stockpile_sql.') AS stockpile, 
				('.$value_sql.') as value
				FROM tblCustomers c, tblCustomerTags t
				WHERE c.customerID=t.customerID
				AND t.tag=:tag
				ORDER BY value DESC';

		$Query = Factory::get('Query', $sql);
		$Query->set('table', $this->table, 'table');
		$Query->set('tag', $tag);

		$rows = $this->db->get_rows($Query);
		return $this->get_instances($rows); 
	}

	public function get_tag_activity($Paging=false, $customerID=null)
	{
		if (is_object($Paging)) {
		    $sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT ';
		}else{
		    $sql = 'SELECT ';
		}

		$sql .= ' * FROM tblCustomers c, tblCustomerTagHistory t
				WHERE c.customerID=t.customerID  ';

		if ($customerID!=null) {
			$sql .= ' AND c.customerID='.(int)$customerID;
		}else{
			$sql .= ' AND t.from_tag !=""';
		}

		$sql .= ' ORDER BY t.timestamp DESC ';

        if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $limit  = ' LIMIT ' . $Paging->lower_bound() . ', ' . $Paging->per_page();
            $sql    .= $limit;      
        }

        $rows	= $this->db->get_rows($sql);

		if (is_object($Paging) && $Paging->enabled() && $Paging->type() == 'db'){
            $sql	= "SELECT FOUND_ROWS() AS count";
		    $total	= $this->db->get_value($sql);
		    $Paging->set_total($total);
		}

		return $this->get_instances($rows);
	}

}