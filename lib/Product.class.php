<?php

class Product extends Core_Base 
{
	protected $table  = 'tblProducts';
    protected $pk     = 'productID';

    /**
     * Get the latest on-sale version of the product
     * @return [type] [description]
     */
    public function get_latest_version()
    {
        $Versions = Factory::get('ProductVersions');

        if ($this->parentID()) {
            $Version = $Versions->get_latest($this->parentID());

            // Load the prices from this item onto the version from the parent.
            $Version->squirrel('versionPriceGBP', $this->productPriceGBP());
            $Version->squirrel('versionPriceEUR', $this->productPriceEUR());
            $Version->squirrel('versionPriceUSD', $this->productPriceUSD());
            $Version->squirrel('productID', $this->productID());
        }else{
            $Version = $Versions->get_latest($this->id());    
        }
        

        return $Version;

    }

    /**
     * Get the description of the product for the basket, mainly.
     * This includes the current version number if available.
     * @return [type] [description]
     */
    public function get_description()
    {
        $Version = $this->get_latest_version();

        if ($Version) {
            return sprintf($this->productBasketDesc(), $Version->versionCode());
        }

        return $this->productBasketDesc();
    }


    /**
     * Get base price in given currency
     * @param  string $currency [description]
     * @param  int $qty [description]
     * @return [type]           [description]
     */
    public function get_price($currency='GBP', $qty=1)
    {
        $Version = $this->get_latest_version();

        $r = false;

        if ($Version && $Version->versionOnSale()) {
            switch($currency) {
                case 'GBP':
                    $r = $Version->versionPriceGBP();
                    break;
                case 'EUR':
                    $r = $Version->versionPriceEUR();
                    break;
                case 'USD':
                    $r = $Version->versionPriceUSD();
                    break;
            }
        }else {
            switch($currency) {
                case 'GBP':
                    $r = $this->productPriceGBP();
                    break;
                case 'EUR':
                    $r = $this->productPriceEUR();
                    break;
                case 'USD':
                    $r = $this->productPriceUSD();
                    break;
            }
        }

        if ($qty==1) return $r;

        // Price for multiples.
        
        if ($this->productCode()=='PERCH' || $this->productCode()=='RUNWAY') {
            
            // More than 10 Perch licenses? Return 90% of the cost, a 10% discount.
            if ($qty>=10) {
                return ($r*0.9)*$qty;
            }

            // Between 5 and 10 Perch licenses? Return 95% of the cost, a 5% discount.
            if ($qty>=5 && $qty<10) {
                return ($r*0.95)*$qty;
            }
        }

        return $r;
    }

    /**
     * Generate a new license key, for the given version or the current version if none is passed. Usually for the current version.
     * @param  boolean $Version [description]
     * @return [type]           [description]
     */
    public function generate_license_key($Version=false)
    {
        if (!$Version) $Version = $this->get_latest_version();

        $segments = array();
        $segments[] = $this->productLicensePrefix().$Version->versionMajor().date('ym');
        $segments[] = $this->random_letter(3).$this->random_number(3);
        $segments[] = $this->random_letter(3).$this->random_number(3);
        $segments[] = $this->random_letter(3).$this->random_number(3);

        if ($this->productLicenseSuffix()) {
            $segments[] = $this->productLicenseSuffix().date('0d');
        }else{
            $segments[] = $this->random_letter(3).date('0d');    
        }
       
        return implode('-', $segments);
    }
    
    public function generate_license_slug($Version=false, $customerID, $orderID)
    {
        if (!$Version) $Version = $this->get_latest_version();

        $segments = array();
        $segments[] = $this->productLicensePrefix().$Version->versionMajor();
        $segments[] = str_pad($customerID.'', 4, '0', STR_PAD_LEFT);
        $segments[] = date('ymdHis');
        
        $str = implode('-', $segments).'-';

        $sql = 'SELECT COUNT(*) FROM tblLicenses WHERE licenseSlug LIKE '.$this->db->pdb($str.'%');
        $count = $this->db->get_count($sql);
        $count++;

        return $str . str_pad($count.'', 2, '0', STR_PAD_LEFT);

    }

    /**
     * Random letter used by license key generator
     * @param  integer $qty [description]
     * @return [type]       [description]
     */
    public function random_letter($qty=1)
    {
        $chars = "ABCDEFGHJKLMNPQRSTUVWXYZ";
        $code = "";
        while (strlen($code) < $qty) {
            $code .= $chars[mt_rand(0,strlen($chars)-1)];
        }
        return $code;
    }
    
    /**
     * Random number used by the licence key generator
     * @param  integer $qty [description]
     * @return [type]       [description]
     */
    public function random_number($qty=1)
    {
        $code = "";
        while (strlen($code) < $qty) {
            $code .= rand(0,9);
        }
        return $code;
    }




}
