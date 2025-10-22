<?php

class CountryReports extends Countries 
{
	public function get_top_countries_by_value()
	{
		$sql = "SELECT ROUND(SUM(o.orderValue / o.orderCurrencyRate)) AS valueGBP
				FROM tblOrders o
				WHERE o.orderStatus='PAID'";
		$total_order_value = $this->db->get_value($sql);

		$sql = "SELECT c.countryName, c.countryCode,
						FORMAT(SUM(o.orderValue / o.orderCurrencyRate),0) AS valueGBP,
						ROUND((SUM(o.orderValue / o.orderCurrencyRate)/$total_order_value)*100,1) AS percentage
				FROM tblCountries c, tblOrders o, tblCustomers cus
				WHERE o.customerID=cus.customerID AND cus.countryID=c.countryID
					AND o.orderStatus='PAID'
				GROUP BY c.countryID
				HAVING SUM(o.orderValue / o.orderCurrencyRate)>5000
				ORDER BY SUM(o.orderValue / o.orderCurrencyRate) DESC";

		$rows = $this->db->get_rows($sql);

		return $rows;
	}

	public function get_top_countries_by_order_value()
	{
		$sql = "SELECT COUNT(*) AS orders
				FROM tblOrders o
				WHERE o.orderStatus='PAID'";
		$total_orders = $this->db->get_value($sql);

		$sql = "SELECT c.countryName, c.countryCode,
						FORMAT(COUNT(*), 0) AS orders,
						FORMAT(SUM(o.orderValue / o.orderCurrencyRate)/COUNT(*),0) AS avValueGBP
				FROM tblCountries c, tblOrders o, tblCustomers cus
				WHERE o.customerID=cus.customerID AND cus.countryID=c.countryID
					AND o.orderStatus='PAID'
				GROUP BY c.countryID
				HAVING COUNT(*)>50
				ORDER BY avValueGBP DESC";

		$rows = $this->db->get_rows($sql);

		return $rows;
	}
}