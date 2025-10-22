<?php

class OrderTaxEvidenceItems extends Factory 
{
	protected $singularClassName = 'OrderTaxEvidenceItem';
	protected $table             = 'tblOrderTaxEvidence';
	protected $pk                = 'evidenceID';

    protected $default_sort_column  = 'orderID';  


    public function log($orderID, $type='ADDRESS', $detail, $source, $countryID)
    {
    	$this->create([
			'orderID'        => (int)$orderID,
			'evidenceType'   => $type,
			'evidenceDetail' => $detail,
			'evidenceSource' => $source,
			'countryID'      => (int)$countryID,
    		]);
    }

}