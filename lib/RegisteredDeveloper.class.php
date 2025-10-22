<?php

class RegisteredDeveloper extends Core_Base 
{
	protected $table  = 'tblRegisteredDevelopers';
    protected $pk     = 'devID';


    public function extend($months=12)
    {   
        $data = array();
        
        // if currently a valid sub
        if (strtotime($this->devSubscriptionTo()) > time()) {
            $data['devSubscriptionTo'] = date('Y-m-d H:i:s', strtotime($this->devSubscriptionTo().' +'.$months.' MONTHS'));
        }else{
            $data['devSubscriptionFrom'] = date('Y-m-d H:i:s');
            $data['devSubscriptionTo'] = date('Y-m-d H:i:s', strtotime('+'.$months.' MONTHS'));
        }
        
        $data['devActive'] = '1';
        
        return $this->update($data);
    }
}
