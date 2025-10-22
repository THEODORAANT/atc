<?php

class ReferralCode extends Core_Base 
{
	protected $table  = 'tblReferralCodes';
    protected $pk     = 'codeID';

    public function to_array()
    {
    	$out = parent::to_array();
   	    $out['url'] = '?utm_source='.$this->codeSource().'&utm_medium='.$this->codeMedium().'&utm_campaign='.$this->codeCampaign();
	        	
  		return $out;
  	}

}