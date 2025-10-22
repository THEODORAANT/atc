<?php

class OAuthStash extends Factory
{
	protected $singularClassName = 'OAuthStash';
    protected $table    = 'oauth_stash';
    protected $pk   = 'token';


	public function init($request, $url)
	{
		$token = $this->get_token();
		while ($this->db->get_count('SELECT COUNT(*) FROM '.$this->table.' WHERE token='.$this->db->pdb($token))) {
			$token = $this->get_token();
		}

		$this->create([
			'token' => $token,
			'request' => serialize($request),
			'url'	=> $url.$token,
			'expires' => date('Y-m-d H:i:s', strtotime('+4 HOURS')),
			]);

		return $token;
	}

	public function get_stashed($token)
	{
		$sql = 'SELECT * FROM '.$this->table.' WHERE token='.$this->db->pdb($token);
		$row = $this->db->get_row($sql);
		if (Util::count($row)) {
			$row['request'] = unserialize($row['request']);
			return $row;
		}
		return false;
	}

	public function set_customer($token, $customerID)
	{
		$sql  = 'UPDATE '.$this->table.' SET customerID='.$this->db->pdb($customerID).' WHERE token='.$this->db->pdb($token).' LIMIT 1';
		$this->db->execute($sql);

		$sql = 'SELECT url FROM '.$this->table.' WHERE token='.$this->db->pdb($token).' LIMIT 1';
		return $this->db->get_value($sql);
	}

	public function crypto_rand_secure($min, $max) 
	{
	        $range = $max - $min;
	        if ($range < 0) return $min; // not so random...
	        $log = log($range, 2);
	        $bytes = (int) ($log / 8) + 1; // length in bytes
	        $bits = (int) $log + 1; // length in bits
	        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
	        do {
	            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
	            $rnd = $rnd & $filter; // discard irrelevant bits
	        } while ($rnd >= $range);
	        return $min + $rnd;
	}

	public function get_token($length=12)
	{
	    $token = "";
	    $codeAlphabet = "abcdefghijklmnopqrstuvwxyz";
	    $codeAlphabet.= "0123456789";
	    for($i=0;$i<$length;$i++){
	        $token .= $codeAlphabet[$this->crypto_rand_secure(0,strlen($codeAlphabet))];
	    }
	    return $token;
	}
}