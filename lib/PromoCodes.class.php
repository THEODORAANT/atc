<?php

class PromoCodes extends Factory 
{
	protected $singularClassName = 'PromoCode';
    protected $table    = 'tblPromoCodes';
    protected $pk   = 'promoID';

    protected $default_sort_column  = 'promoFrom';  

    /**
     * Get the promo by its code, but only if it's valid
     * @param  [type] $code [description]
     * @return [type]       [description]
     */
    public function get_valid($code)
    {
    	$sql = 'SELECT * FROM :table
    			WHERE promoCode=:code 
    				AND promoFrom < :now AND promoTo >= :now';
    	$Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
    	$Query->set('code', $code);
    	$Query->set('now', date('Y-m-d H:i:s'));
    	return $this->get_instance($this->db->get_row($Query));
    }

    /**
     * Generate a new, single use promo code. Takes a prefix for tracking purposes e.g. 'UB' might be Unfinished Business
     * @param  [type]  $prefix      Short alpha prefix. 2 or 3 letters is best
     * @param  [type]  $discount    Percentage discount for mortals
     * @param  [type]  $devdiscount Percentage discount for regdevs
     * @param  string  $expires     strtotime offset for expiry date, e.g. +1 YEAR, +30 DAYS etc
     * @param  integer $productID   ID from products table
     * @return [type]               New PromoCode object
     */
    public function generate_single_use($prefix, $discount, $devdiscount, $expires='+1 YEAR', $productID=1)
    {
        return $this->create([
            'productID'              => $productID,
            'promoCode'              => $this->get_new_code($prefix),
            'promoFrom'              => date('Y-m-d H:i:s'),
            'promoTo'                => date('Y-m-d H:i:s', strtotime($expires)),
            'promoDiscount'          => (int) $discount,
            'promoDeveloperDiscount' => (int) $devdiscount,
            'promoSingleUse'         => 1,
        ]);
    }

    public function deleted_used_single_promos()
    {
        $sql     = 'DELETE FROM '.$this->table .' 
                    WHERE promoSingleUse=1 AND promoCode IN (
                        SELECT orderPromoCode
                        FROM tblOrders
                        WHERE orderStatus=\'PAID\'
                    )';
        $this->db->execute($sql);
    }


    public function get_new_code($prefix) 
    {
        $code = $this->_generate_code($prefix);

        while ($this->db->get_count('SELECT COUNT(*) FROM '.$this->table.' WHERE promoCode='.$this->db->pdb($code))) {
            $code = $this->_generate_code($prefix);
        }

        return $code;
    }

    private function _generate_code($prefix) 
    {
        $code    = $prefix;
        $code   .= $this->_generate_random_string(2, 'letters');
        $code   .= $this->_generate_random_string(3, 'numbers');
        $code   .= $this->_generate_random_string(2, 'letters');
    
        $code    = strtoupper($code);
        return $code;
    }
        
    private function _generate_random_string($length=6, $type='both') 
    {
        switch($type) {
            case 'letters' :
                $chars = "abcdefghjklmnpqrstuvwxyz";
                break;
                
            case 'numbers' :
                $chars = "23456789";
                break;
                
            default:
                $chars = "abcdefghjklmnpqrstuvwxyz23456789";
                break;
        }
        
        $code = "";
        while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0,strlen($chars)-1)];
        }
        return $code;
    }


}