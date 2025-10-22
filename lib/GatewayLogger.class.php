<?php

class GatewayLogger
{
	public static function log($data)
	{
		$DB = Factory::get('DB');
		$DB->insert('tblGatewayLog', $data);
	}
}
