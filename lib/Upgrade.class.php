<?php

class Upgrade extends Core_Base 
{
	protected $table  = 'tblUpgrades';
    protected $pk     = 'upgradeID';


    /**
     * Mark the upgrade as spent, so that it can't be used again
     * @param  [type] $licenseID [description]
     * @return [type]            [description]
     */
    public function mark_spent($licenseID)
    {
        // Sometimes we have mock Upgrade objects for evergreen upgrades with no db row.
        if ($this->id()==0) return true;


    	$this->update([
			'upgradeStatus'      => 'SPENT',
			'licenseID'          => $licenseID,
			'upgradeAppliedDate' => date('Y-m-d H:i:s'),
    		]);
    }

}
