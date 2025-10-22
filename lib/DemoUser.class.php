<?php

class DemoUser extends Core_Base 
{
	protected $table  = 'tblDemoUsers';
    protected $pk     = 'userID';


    public function log_referral($referral)
    {
    	$this->db->insert('tblDemoReferrals', array(
			'userID'     => $this->id(),
			'refText'    => $referral,
			'refCreated' => Util::time_now(),
    		));
    }

    public function can_send_email($email_key)
    {
        switch($email_key) {

            case 'demo_thanks':

                if (strtotime($this->userLastThanksForTrying()) < strtotime('-1 MONTH')) {
                    return true;
                }

                break;

        }


        return false;
    }

}