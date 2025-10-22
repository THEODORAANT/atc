<?php

class SagePay implements PaymentGateway
{

	public $name = 'SAGEPAY';

	private $sagepay_live_register_url = 'https://live.sagepay.com/gateway/service/vspserver-register.vsp';
	private $sagepay_test_register_url = 'https://test.sagepay.com/gateway/service/vspserver-register.vsp';


	public function register_transaction(Order $Order, callable $cb_failure)
	{
		$Conf = Conf::fetch();

    	$OrderItems = Factory::get('OrderItems');

    	$Customers = Factory::get('Customers');
    	$Customer  = $Customers->find($Order->customerID());

		$returnURL = $this->get_callback_url();

		$data = [
			'Amount'             => number_format($Order->orderValue(), 2),
			'Description'        => $OrderItems->get_description($Order->id()),
			'NotificationURL'    => $returnURL,
			'VendorTxCode'       => $Order->orderRef(),
			'CustomerEMail'      => $Customer->customerEmail(),
			'Currency'           => $Order->orderCurrency(),
			'BillingFirstnames'  => $this->filter($Customer->customerFirstName(), 20, '/[^\p{L}\s\-\.\'\&\/]/u'),
			'BillingSurname'     => $this->filter($Customer->customerLastName(), 20, '/[^\p{L}\s\-\.\'\&\/]/u'),
			'BillingAddress1'    => $this->filter($Customer->customerStreetAdr1(), 100, '/[^\p{L}0-9\s\-\.\'\&\/\+\:\,\(\)]/u'),
			'BillingAddress2'    => $this->filter($Customer->customerStreetAdr2(), 100, '/[^\p{L}0-9\s\-\.\'\&\/\+\:\,\(\)]/u'),
			'BillingCity'        => $this->filter($Customer->customerLocality(), 40, '/[^\p{L}0-9\s\-\.\'\&\/\+\:\,\(\)]/u'),
			'BillingPostCode'    => $this->filter($Customer->customerPostalCode(), 10, '/[^A-Za-z0-9\-\s]/u'),
			'BillingCountry'     => $Customer->country_code(),
			'DeliveryFirstnames' => $this->filter($Customer->customerFirstName(), 20, '/[^\p{L}\s\-\.\'\&\/]/u'),
			'DeliverySurname'    => $this->filter($Customer->customerLastName(), 20, '/[^\p{L}\s\-\.\'\&\/]/u'),
			'DeliveryAddress1'   => $this->filter($Customer->customerStreetAdr1(), 100, '/[^\p{L}0-9\s\-\.\'\&\/\+\:\,\(\)]/u'),
			'DeliveryAddress2'   => $this->filter($Customer->customerStreetAdr2(), 100, '/[^\p{L}0-9\s\-\.\'\&\/\+\:\,\(\)]/u'),
			'DeliveryCity'       => $this->filter($Customer->customerLocality(), 40, '/[^\p{L}0-9\s\-\.\'\&\/\+\:\,\(\)]/u'),
			'DeliveryPostcode'   => $this->filter($Customer->customerPostalCode(), 10, '/[^A-Za-z0-9\-\s]/u'),
			'DeliveryCountry'    => $Customer->country_code(),
			'BasketXML'			 => $OrderItems->get_items_xml($Order->id()),
			];

		if ($Customer->country_code()=='US') {
	      $data['BillingState']      = $Customer->customerUsState();
	      $data['ShippingState']     = $Customer->customerUsState();
	    }

	    $orderID = $Order->id();

		$defaults = array(
			'VPSProtocol' 	=> '3.00',
			'TxType'		=> 'PAYMENT',
			'Vendor'		=> $Conf->payment_gateway['vendor'],
		);

		$data = array_merge($defaults, $data);

		$non_blank_fields = array('BillingSurname', 'BillingFirstnames', 'BillingAddress1', 'BillingAddress2', 'BillingCity', 'BillingPostCode', 'BillingCountry', 'DeliverySurname', 'DeliveryFirstnames', 'DeliveryAddress1', 'DeliveryCity', 'DeliveryPostCode', 'DeliveryCountry');

		foreach($non_blank_fields as $non_blank_field) {
			if (!isset($data[$non_blank_field])) {
				$data[$non_blank_field] = '-';
			}
		}

		$url = $this->sagepay_live_register_url;
		if ($Conf->payment_gateway['test_mode']) {
			$url = $this->sagepay_test_register_url;
		}

		$result = $this->_post($url, $data);

		if (is_array($result)) {

			switch($result['Status']) {

				case 'OK' :
				case 'OK REPEATED':
					return $result;
					break;

				default:
					$cb_failure($result);
					return false;

					break;
			}
		}

		return false;
	}

	public function format_response($data)
	{
		$out = array();

		foreach($data as $key=>$val) {
			$out[] = $key.'='.$val;
		}

		return implode("\r\n", $out);
	}

	public function filter($var, $max, $pattern)
	{
		$s = mb_substr($var, 0, $max, 'UTF-8');
		$s = preg_replace($pattern, '', $s);

		return $s;
	}

	public function get_updated_order_values(array $response)
	{
		return [
				'orderSagePayVPSTxId' 	  => $response['VPSTxId'],
				'orderSagePaySecurityKey' => $response['SecurityKey'],
				];
	}

	private function _post($url, $data)
	{
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result) {
        	return $this->_parse_respose_to_array($result);
        }
        return $result;
	}

	private function _parse_respose_to_array($resp)
	{
		$lines = explode("\r\n", $resp);
		if (is_array($lines) && Util::count($lines)) {
			$out = array();
			foreach($lines as $line) {
				$parts = explode('=', $line, 2);
				if (Util::count($parts)) {
					$out[trim($parts[0])] = trim($parts[1]);
				}
			}

			return $out;
		}

		return false;
	}

	private function _prepare_data()
	{

	}

	public function process_refund(Order $Order)
	{
		
	}

}

