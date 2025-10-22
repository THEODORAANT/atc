<?php

class SubscriptionPlan extends Core_Base 
{
	protected $table  = 'tblSubscriptionPlans';
    protected $pk     = 'planID';

    public function get_predicted_end_date()
    {
    	return date('Y-m-d H:i:s', strtotime('+'.$this->planIntervalCount().' '.strtoupper($this->planInterval()).'S'));
    }

}
