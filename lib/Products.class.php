<?php

class Products extends Factory 
{
	protected $singularClassName = 'Product';
    protected $table    = 'tblProducts';
    protected $pk   = 'productID';

    protected $default_sort_column  = 'productCode';  


    public function get_by_item_code($code)
    {

    	// Is is just a code with no options?
    	if (strpos($code, '-')===false) {
    		return $this->get_one_by('productCode', $code);
    	}

    	// Find the options 
    	$parts = explode('-', $code);
		$productCode = $parts[0];
		$optionCode  = $parts[1];


    	$sql = 'SELECT p.productID, p.productCode, p.productVATRate, 
    					CONCAT(p.productTitle, " ", o.optionTitle) AS productTitle, 
    					CONCAT(p.productBasketDesc, " ", o.optionBasketDesc) AS productBasketDesc, 
    					p.productDiscountable, p.productLicensePrefix, p.productWelcomeEmail, p.productResetEmail, p.productEmailFrom, p.productPromoCodeEmail, p.productIsSubscription, 
    					o.optionID, o.optionCode, o.optionTitle, o.optionBasketDesc, o.productPriceGBP, o.productPriceEUR, o.productPriceUSD, o.optionInterval, o.optionIntervalCount, o.optionStatementDesc
    			FROM tblProducts p, tblProductOptions o
    			WHERE p.productID=o.productID AND p.productCode='.$this->db->pdb($productCode).' AND o.optionCode='.$this->db->pdb($optionCode).'
    			LIMIT 1';


    	return $this->get_instance($this->db->get_row($sql));


    }


}


