<?php

class Slack
{
	//private static $url   = 'https://grabaperch.slack.com/services/hooks/incoming-webhook?token=JWBTx3TjrchBhBL4fckLx99v';
	private static $url   ='https://hooks.slack.com/services/xx/xxx/xxxxx';

	public static function notify($message, $channel='#buys')
	{
		$data = [
			'channel' => $channel,
			'username' => 'atc',
			'text' => $message,
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::$url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'ATC');                
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 2);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		$result = curl_exec($ch);
		curl_close($ch);
	}
}
