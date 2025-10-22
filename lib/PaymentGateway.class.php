<?php

interface PaymentGateway
{
	public function register_transaction(Order $Order, callable $cb_failure);

	public function get_updated_order_values(array $response);

	public function set_result_url($url);

	public function process_refund(Order $Order);
}