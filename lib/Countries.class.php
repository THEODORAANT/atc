<?php

class Countries extends Factory 
{
	protected $singularClassName = 'Country';
    protected $table    = 'tblCountries';
    protected $pk   = 'countryID';

    protected $default_sort_column  = 'countryName';  

    public function get_us_states()
    {
    	$sql = 'SELECT * FROM tblUSStates ORDER BY stateName ASC';
    	return $this->db->get_rows($sql);
    }

    public function get_by_ip($ip=false)
    {
    	if (filter_var($ip, FILTER_VALIDATE_IP)) {
    		$countryCode = $this->_get_country_code_from_remote_api($ip);
    		if ($countryCode) {
    			$Country = $this->get_one_by('countryCode', $countryCode);
    			if ($Country) {
    				return $Country;
    			}
    		}
    	}
    	return false;
    }

    public function apply_tax_changes()
    {
        $sql = 'SELECT * FROM tblCountryTaxChanges WHERE changeApplied IS NULL AND changeDate<='.$this->db->pdb(date('Y-m-d H:i:s'));
        $rows = $this->db->get_rows($sql);

        if (Util::count($rows)) {
            foreach($rows as $row) {
                $Country = $this->find($row['countryID']);
                $Country->update([
                    'countryVATRate' => floatval($row['changeValue'])
                    ]);
                $this->db->update('tblCountryTaxChanges', ['changeApplied'=>date('Y-m-d H:i:s')], 'changeID', $row['changeID']);
            }
        }
    }

    private function _get_country_code_from_remote_api($ip)
    {
    	$countryCode = $this->_find_country_in_cache($ip);
    	if ($countryCode) return $countryCode;

    	$Conf = Conf::fetch();

    	$url = $Conf->ip_geolocator['url'].$ip;

    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $url);
    	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));     
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    	curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    	$result = curl_exec($ch);
    	curl_close($ch);

    	if ($result) {
    		$data = json_decode($result);
    		if (isset($data->country)) {
    			$this->_add_ip_to_cache($ip, $data->country);
    			return $data->country;
    		}
    	}
    	return false;
    }

    private function _add_ip_to_cache($ip, $countryCode)
    {
    	$this->db->delete('tblIpLookupCache', 'ip', $ip);

    	$this->db->insert('tblIpLookupCache', [
    		'ip' => $ip,
    		'country' => $countryCode,
    		]);
    }

    private function _find_country_in_cache($ip)
    {
    	return $this->db->get_value('SELECT country 
    									FROM tblIpLookupCache 
    									WHERE ip='.$this->db->pdb($ip).' 
    										AND timestamp>'.$this->db->pdb(date('Y-m-d H:i:s', strtotime('-24 HOURS'))));
    }


}