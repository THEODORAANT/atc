<?php

class OrdersReports extends Orders
{

	public function get_top_sales_days_by_unit()
	{
		$sql = "SELECT DATE(tblOrders.orderDate), 
					SUM(tblOrderItems.itemQty) as qty
				FROM tblOrderItems INNER JOIN tblOrders ON tblOrderItems.orderID = tblOrders.orderID
				WHERE orderStatus='PAID'
				GROUP BY DATE(tblOrders.orderDate)
				ORDER BY qty DESC
				LIMIT 10";

		return $this->db->get_rows($sql);
	}

	public function get_export_figures()
	{
		$sql = "SELECT 'UK' AS region, 
				FORMAT(SUM(tblOrders.orderValue/tblOrders.orderCurrencyRate),0) as total
				FROM tblCustomers INNER JOIN tblCountries ON tblCustomers.countryID = tblCountries.countryID
					 INNER JOIN tblOrders ON tblOrders.customerID = tblCustomers.customerID
				WHERE tblCountries.countryCode='GB' AND tblOrders.orderStatus='PAID'

				UNION ALL

				SELECT 'EU' AS region, 
				FORMAT(SUM(tblOrders.orderValue/tblOrders.orderCurrencyRate),0) as total
				FROM tblCustomers INNER JOIN tblCountries ON tblCustomers.countryID = tblCountries.countryID
					 INNER JOIN tblOrders ON tblOrders.customerID = tblCustomers.customerID
				WHERE tblCountries.countryInEU=1 AND tblCountries.countryCode!='GB' AND tblOrders.orderStatus='PAID'

				UNION ALL

				SELECT 'Rest of world' AS region, 
				FORMAT(SUM(tblOrders.orderValue/tblOrders.orderCurrencyRate),0) as total
				FROM tblCustomers INNER JOIN tblCountries ON tblCustomers.countryID = tblCountries.countryID
					 INNER JOIN tblOrders ON tblOrders.customerID = tblCustomers.customerID
				WHERE tblCountries.countryInEU=0 AND tblCountries.countryCode!='GB' AND tblOrders.orderStatus='PAID'
				";
		return $this->db->get_rows($sql);
	}

	public function get_currency_breakdown()
	{
		$sql = "SELECT COUNT(orderCurrency) AS cx
				FROM tblOrders o
				WHERE orderDate>'2012-07-16 00:00:00' AND o.orderStatus='PAID' AND orderCurrency IS NOT NULL";
		$total_orders = $this->db->get_value($sql);



		$sql = "SELECT tblOrders.orderCurrency, 
						FORMAT(COUNT(orderCurrency),0) as qty, 
						ROUND((COUNT(orderCurrency)/$total_orders)*100,1) AS percentage
				FROM tblOrders
				WHERE orderDate>'2012-07-16 00:00:00' AND orderStatus='PAID' AND orderCurrency IS NOT NULL
				GROUP BY orderCurrency
				ORDER BY percentage DESC";
		return $this->db->get_rows($sql);
	}

	public function get_monthly_sales_for_product($product_code='PERCH')
	{
		$sql = "SELECT DATE_FORMAT(DATE(o.orderDate), '%b %Y') AS displayDate, SUM(oi.itemQty) AS nocode, (
                    SELECT SUM(oi2.itemQty)
                    FROM tblOrders o2, tblOrderItems oi2
                    WHERE o2.orderID=oi2.orderID AND  o2.orderStatus='PAID' AND o2.orderRefund<o2.orderItemsTotal
                        AND ((oi2.itemCode='$product_code' AND o2.orderPromoCode != ''))
                        AND YEAR(o2.orderDate)=YEAR(o.orderDate) AND MONTH(o2.orderDate)=MONTH(o.orderDate)
                ) AS code, 
				(
                    SELECT FORMAT(SUM(o2.orderValue/o2.orderCurrencyRate),0)
                    FROM tblOrders o2, tblOrderItems oi2
                    WHERE o2.orderID=oi2.orderID AND  o2.orderStatus='PAID' AND o2.orderRefund<o2.orderItemsTotal
                        AND ((oi2.itemCode='$product_code'))
                        AND YEAR(o2.orderDate)=YEAR(o.orderDate) AND MONTH(o2.orderDate)=MONTH(o.orderDate)
                    GROUP BY YEAR(orderDate), MONTH(orderDate)
                ) as valueGBP
                FROM tblOrders o, tblOrderItems oi
                WHERE o.orderID=oi.orderID AND oi.itemCode='$product_code' AND o.orderStatus='PAID' AND o.orderRefund<o.orderItemsTotal AND o.orderValue>0 AND o.orderPromoCode=''
                GROUP BY YEAR(orderDate), MONTH(orderDate)
                ORDER BY orderDate DESC";

        return $this->db->get_rows($sql);
	}

	public function get_monthly_sales_for_products(array $product_codes)
	{
		$codes = $this->db->implode_for_sql_in($product_codes);

		$sql = "SELECT DATE_FORMAT(DATE(o.orderDate), '%b %Y') AS displayDate, SUM(oi.itemQty) AS nocode, (
                    SELECT SUM(oi2.itemQty)
                    FROM tblOrders o2, tblOrderItems oi2
                    WHERE o2.orderID=oi2.orderID AND  o2.orderStatus='PAID' AND o2.orderRefund<o2.orderItemsTotal
                        AND ((oi2.itemCode IN ($codes) AND o2.orderPromoCode != ''))
                        AND YEAR(o2.orderDate)=YEAR(o.orderDate) AND MONTH(o2.orderDate)=MONTH(o.orderDate)
                ) AS code, 
				(
                    SELECT FORMAT(SUM(o2.orderValue/o2.orderCurrencyRate),0)
                    FROM tblOrders o2, tblOrderItems oi2
                    WHERE o2.orderID=oi2.orderID AND  o2.orderStatus='PAID' AND o2.orderRefund<o2.orderItemsTotal
                        AND ((oi2.itemCode IN ($codes)))
                        AND YEAR(o2.orderDate)=YEAR(o.orderDate) AND MONTH(o2.orderDate)=MONTH(o.orderDate)
                    GROUP BY YEAR(orderDate), MONTH(orderDate)
                ) as valueGBP
                FROM tblOrders o, tblOrderItems oi
                WHERE o.orderID=oi.orderID AND oi.itemCode IN ($codes) AND o.orderStatus='PAID' AND o.orderRefund<o.orderItemsTotal AND o.orderValue>0 AND o.orderPromoCode=''
                GROUP BY YEAR(orderDate), MONTH(orderDate)
                ORDER BY orderDate DESC";

        return $this->db->get_rows($sql);
	}

	public function get_payment_gateway_breakdown()
	{
		$sql = "SELECT COUNT(*) AS qty
				FROM tblOrders
				WHERE orderStatus='PAID' AND orderDate>'2014-04-01'";
		$total_orders = $this->db->get_value($sql);

		$sql = "SELECT orderType, COUNT(*) AS qty, ROUND((COUNT(*)/$total_orders)*100,1) AS percentage
				FROM tblOrders
				WHERE orderStatus='PAID' AND orderDate>'2014-04-01'
				GROUP BY orderType
				ORDER BY qty DESC";
		return $this->db->get_rows($sql);
	}


}