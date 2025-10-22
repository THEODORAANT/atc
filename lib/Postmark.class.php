<?php

class Postmark {

	private $api_key;
	private $attachment_count = 0;
	private $data = array();

	public $error_code;

	function __construct()
	{
		$Conf = Conf::fetch();
		$this->api_key = $Conf->postmark_key;
		$this->data['From'] = '';
		$this->data['ReplyTo'] = '';
	}

	function send()
	{
		$headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'X-Postmark-Server-Token: '.$this->api_key
		);
		
		$ch = curl_init('https://api.postmarkapp.com/email');
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
		$return     = curl_exec($ch);
		$curl_error = curl_error($ch);
		$http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		curl_close($ch);
		
		// do some checking to make sure it sent
		if($http_code !== 200) {
			$this->error_code = $http_code;
			return false;
		}else{
			return true;
		}
	}

	function to($to)
	{
		$this->data['To'] = $to;
		return $this;
	}

	function from($from)
	{
		$this->data['From'] = $from;
	}

	function reply_to($reply='')
	{
		$this->data['ReplyTo'] = $reply;
	}
	
	function cc($cc)
	{
		$this->data["Cc"] = $cc;
		return $this;
	}
	
	function bcc($bcc)
	{
		$this->data["Bcc"] = $bcc;
		return $this;
	}
		
	function subject($subject)
	{
		$this->data['Subject'] = $subject;
		return $this;
	}

	function html_message($html)
	{
		$this->data['HtmlBody'] = $html;
		return $this;
	}

	function plain_message($msg)
	{
		$this->data['TextBody'] = $msg;
		return $this;
	}

	function tag($tag)
	{
		$this->data['Tag'] = $tag;
		return $this;
	}
	
	function attachment($name, $content, $content_type, $content_id=false)
	{
		$this->data['Attachments'][$this->attachment_count]['Name']			= $name;
		$this->data['Attachments'][$this->attachment_count]['ContentType']	= $content_type;

		if ($content_id) {
			$this->data['Attachments'][$this->attachment_count]['ContentID']= $content_id;
			$this->data['HtmlBody'] = str_replace($name, $content_id, $this->data['HtmlBody']);	
		}
		
		// Check if our content is already base64 encoded or not
		if( ! base64_decode($content, true))
			$this->data['Attachments'][$this->attachment_count]['Content']	= base64_encode($content);
		else
			$this->data['Attachments'][$this->attachment_count]['Content']	= $content;
		
		// Up our attachment counter
		$this->attachment_count++;
		
		return $this;
	}

}