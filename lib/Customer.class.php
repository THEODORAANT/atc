<?php

use \DrewM\Drip\Drip;

class Customer extends Core_Base 
{
	protected $table  = 'tblCustomers';
    protected $pk     = 'customerID';


    public function delete()
    {
        if ($this->customerDripID()) {
            $this->delete_from_drip();    
        }
        
        if ($this->customerIsProspect()) {
            // delete prospects

            $this->db->delete('tblCustomerTags', 'customerID', $this->id());
            $this->db->delete('tblCustomerTagHistory', 'customerID', $this->id());
            $this->db->delete($this->table, $this->pk, $this->details[$this->pk]);

        }else{
            // don't delete customers, just blank out their personal data.

            $this->update([
                    'customerFirstName'               => 'Deleted',
                    'customerLastName'                => 'Customer '.$this->id(),
                    'customerEmail'                   => 'customer'.$this->id().'@grabaperch.com',
                    'customerURL'                     => '',
                    'customerActive'                  => 0,
                    'customerCompany'                 => '',
                    'customerStreetAdr1'              => '',
                    'customerStreetAdr2'              => '',
                    'customerLocality'                => '',
                    'customerRegion'                  => '',
                    'customerUsState'                 => '',
                    'customerPostalCode'              => '',
                    'customerVATnumber'               => '',
                    'customerLat'                     => 0,
                    'customerLng'                     => 0,
                    'customerMailChimpEUID'           => '',
                    'customerDripID'                  => '',
                    'customerReferralCode'            => '',
                    'customerForumEmailNotifications' => 0,
                ]);

            $this->db->update('tblCustomerTagHistory', ['sentToMailer'=>1], 'customerID', $this->id());

        }   
        
        return true;       
    }


    /**
     * Create a new session token to validate requests for this user's data across
     * The idea is that you can't get data about this user's account without the token.
     * The token is refreshed when the user logs in.
     * So you can only get e.g. license list for a user if you have the token, which means the user has logged in.
     * If the user logs in elsewhere, the session is invalidated. One at a time, please
     * @return string Token
     */
    public function generate_new_session_token()
    {
    	$new_token = md5(uniqid(rand(), true));
    	$this->update(array(
    		'customerSessionToken' => $new_token
    		));
    	return $new_token;
    }

    /**
     * Validate the given token against the stored session token. Nothing fancy.
     * @param  string $token Session token
     * @return bool        Validity of the token
     */
    public function check_session_token($token)
    {   //echo "customerSessionToken";
        //echo $this->customerSessionToken();
    	return $token == $this->customerSessionToken();
    }


    /**
     * Get a list of licenses the customer holds for the given productID
     * @param  integer $productID [description]
     * @return [type]             [description]
     */
    public function get_licenses($productID=1)
    {
    	$Licenses = Factory::get('Licenses');
    	return $Licenses->get_for_customer($this->id(), $productID);
    }


    /**
     * Get a list of licenses the customer holds for the given orderRef
     * @param  string $orderRef [description]
     * @return [type]             [description]
     */
    public function get_licenses_for_order($orderRef)
    {
        $Orders = Factory::get('Orders');
        $Order  = $Orders->get_by_ref_for_customer($this->id(), $orderRef);

        $Licenses = Factory::get('Licenses');
        return $Licenses->get_for_order($Order->id(), $this->id());
    }


    /**
     * Get a specific license based on productID and license slug
     * @param  integer $productID   [description]
     * @param  [type]  $licenseSlug [description]
     * @return [type]               [description]
     */
    public function get_license($productID, $licenseSlug)
    {
        $Licenses = Factory::get('Licenses');
        return $Licenses->get_for_customer($this->id(), $productID, $licenseSlug);
    }

    /**
     * Get orders for the customer.
     * @return [type] [description]
     */
    public function get_orders($sort='ASC')
    {
        $Orders = Factory::get('Orders');
        return $Orders->get_for_customer($this->id(), $sort);
    }

    /**
     * Get the dates of this customer's orders. Used for figuring out their order interval.
     * @return [type] [description]
     */
    public function get_order_dates($as_timestamps=true)
    {
        $orders = $this->get_orders();
        if (Util::count($orders)) {
            $out = [];
            foreach($orders as $Order) {
                if ($as_timestamps) {
                    $out[] = strtotime($Order->orderDate());
                }else{
                    $out[] = $Order->orderDate();    
                }
            }
            return $out;
        }
        return false;
    }

    /**
     * Get a specific order by reference.
     * @param  [type] $orderRef [description]
     * @return [type]           [description]
     */
    public function get_order($orderRef)
    {
        $Orders = Factory::get('Orders');
        //return $Orders->get_one_by('orderRef', $orderRef);
        return $Orders->get_by_ref_for_customer($this->id(), $orderRef);
    }

    /**
     * Get subscriptions for the customer.
     * @return [type] [description]
     */
    public function get_subscriptions()
    {
        $Subs = Factory::get('Subscriptions');
        return $Subs->get_for_customer($this->id());
    }

    /**
     * Get a single subscription
     */
    public function get_subscription($subID)
    {
        $Subs = Factory::get('Subscriptions');
        return $Subs->get_one_for_customer($this->id(), $subID);
    }


   public function has_active_subscription()
    {
        $Subs = Factory::get('Subscriptions');
        return $Subs->active_subscription_for_customer($this->id());
    }





    /**
    * Get promo codes that this customer has earned
    * @return array $CustomerPromoCodes
    */
    public function get_customer_promo_codes()
    {
        $CustomerPromoCodes = Factory::get('CustomerPromoCodes');
        return $CustomerPromoCodes->get_customer_promo_codes($this->id());
    }

    /**
     * Send a welcome email to new customers
     * @param  [type] $productID [description]
     * @return [type]            [description]
     */
    public function send_welcome_email($productID)
    {
        $Products = Factory::get('Products');
        $Product  = $Products->find($productID);

        $Email = Factory::get('Email', $Product->productWelcomeEmail());
        $Email->senderEmail($Product->productEmailFrom());
        $Email->recipientEmail($this->customerEmail());
        $Email->set_bulk($this->to_array());
       
        $Email->send();

    }




    /**
     * Send the email letting a customer know their password has been reset, and what that new password is.
     * @param  [type] $productID [description]
     * @param  [type] $clear_pwd [description]
     * @return [type]            [description]
     */
    public function send_password_reset_email($productID, $clear_pwd)
    {
        $Products = Factory::get('Products');
        $Product  = $Products->find($productID);

        $Email = Factory::get('Email', $Product->productResetEmail(), $use_twig=true);
        $Email->senderEmail($Product->productEmailFrom());
        $Email->recipientEmail($this->customerEmail());
        $Email->set_bulk($this->to_array());
        $Email->set('clear_pwd', $clear_pwd);
       
        $Email->send();
    }

    /**
     * Is the customer currently a valid registrered developer (not expired)?
     * @return boolean [description]
     */
    public function is_registered_developer()
    {
        $RegDevs = Factory::get('RegisteredDevelopers');
        $RegDev = $RegDevs->get_by_customer($this->id());
        return $RegDev;
    }

    public function is_staff()
    {
        $Users = Factory::get('Users');
        $User = $Users->get_one_by('userEmail', $this->customerEmail());
        return is_object($User);
    }

    /**
     * Does the customer pay VAT, based on their country and VAT number
     * @return [type] [description]
     */
    public function pays_VAT()
    {
        // If UK, always pays VAT
        if ((int)$this->countryID() == 161) return true;
           
        $Countries  = Factory::get('Countries');
        $Country    = $Countries->find($this->countryID());
        
        // If in EU
        if ($Country->countryInEU()) {

            // If customer has a valid VAT number, they don't pay VAT
            if ($this->customerVATnumber() != '' && $this->customerVATnumberValid()=='1') {
                return false;
            }else{
                // Otherwise they do. //no eu pays vat
                return false;
            }        
        }
        
        // Rest of world, don't pay VAT
        return false;
    }

    public function is_in_EU()
    {
        // If UK, cry. Damn Brexit.
        if ((int)$this->countryID() == 161) return false;
        
        $Countries  = Factory::get('Countries');
        $Country    = $Countries->find($this->countryID());
        
        // If in EU
        if ($Country->countryInEU()) {
            return true;
        }
        
        // Rest of world.
        return false;
    }
    
    /**
     * Get the VAT rate the customer pays based on their country.
     * This is not the same as whether they should be *charged* VAT. Use pays_VAT() to check that first.
     * IF we're charging them VAT, what rate should we charge it at?
     * @return [type] [description]
     */
    public function get_VAT_rate()
    {
        $Countries = Factory::get('Countries');
        $Country = $Countries->find($this->countryID());
        return (float)$Country->countryVATRate();
    }

    /**
     * Helper function to quickly get the country name
     * @return [type] [description]
     */
    public function country_name()
    {
        $Countries = Factory::get('Countries');
        $Country = $Countries->find($this->countryID());
        return $Country->countryName();
    }

    /**
     * Helper function to quickly get the country two-letter code (for payment gateway, usually)
     * @return [type] [description]
     */
    public function country_code()
    {
        $Countries = Factory::get('Countries');
        $Country = $Countries->find($this->countryID());
        return $Country->countryCode();
    }

    /**
    * Helper function to generate unique referral code
    * @return string referral code
    */
    public function generate_referral_code()
    {
        $str = Util::urlify($this->customerLastName());
        $str = str_replace('-', '', $str);
        $str = substr($str,0,5);
        $str = str_pad($str, 5, 'a', STR_PAD_RIGHT);

        $sql = 'SELECT count(*) FROM :table AS counter
                WHERE customerReferralCode LIKE :code 
                ';


        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('code', $str.'%');

        $count = $this->db->get_count($Query);

        $n = $count+1;

        $str = $str.$n;

        $this->update(array('customerReferralCode'=>$str));
    }


    /**
     * Create a randomised clear-text password.
     * @return [type] [description]
     */
    public function generate_password()
    {
        $Conf = Conf::fetch();
        
        $dictionary = $Conf->site . '/data/passwd.txt';
        
        if (file_exists($dictionary)){
            
            $words  = array();
            $fp     = fopen($dictionary, "r");
            
            while (!feof($fp)){
                $words[]    = trim(fgets($fp, 1024));
            }
            
            fclose($fp);
            
            $word1  = $words[mt_rand(0, count($words)-1)];
            $word2  = $words[mt_rand(0, count($words)-1)];
            
            $number = str_pad((string)rand(0, 100), 3, '0', STR_PAD_LEFT);


            return $word1.'-'.$word2.'-'.$number;
            
        }
            
        return false;
    }

    public function geocode()
    {
        // Don't bother if it's been manually verified
        if ($this->customerAdrManuallyVerified()==1) return true;

        $adr = [];
        if ($this->details['customerStreetAdr1']) $adr[] = $this->details['customerStreetAdr1'];
        if ($this->details['customerStreetAdr2']) $adr[] = $this->details['customerStreetAdr2'];
        if ($this->details['customerLocality'])   $adr[] = $this->details['customerLocality'];
        if ($this->details['customerRegion'])     $adr[] = $this->details['customerRegion'];
        if ($this->details['customerPostalCode']) $adr[] = $this->details['customerPostalCode'];
        if ($this->country_name())                $adr[] = $this->country_name();
                
        $adr = implode(', ', $adr);

        //Console::log($adr);

        $Geocoder = Factory::get('GoogleMapsGeocoder');
        $Geocoder->setAddress($adr);
        $result   = $Geocoder->geocode();

        //Console::log($result);

        if ($result && isset($result['status'])) {

            switch($result['status']) {

                case 'OK':

                    foreach($result['results'] as $adr) {

                        if (isset($adr['address_components'])) {
                            foreach($adr['address_components'] as $component) {
                                if (isset($component['types']) && in_array('country', $component['types'])) {
                                    if ($component['short_name'] == $this->country_code()) {
                                        $this->update([
                                            'customerLat' => $adr['geometry']['location']['lat'],
                                            'customerLng' => $adr['geometry']['location']['lng'],
                                            'customerNeedsGeocoding' => 0,
                                            'customerToReviewAddress' => 0,
                                        ]);
                                        //Console::log("Good geocode");
                                        return true;
                                    }
                                }
                            }
                        }
                        
                    }
                    
                    break;

                case 'OVER_QUERY_LIMIT':
                case 'REQUEST_DENIED':
                    $this->update([
                        'customerNeedsGeocoding' => 1,
                        ]);
                    return false;
                    break;

                case 'INVALID_REQUEST':
                case 'ZERO_RESULTS':
                    $this->update([
                        'customerToReviewAddress' => 1,
                        'customerNeedsGeocoding' => 1,
                        ]);
                    return false;
                    break;


            }

        } 

        $this->update([
            'customerToReviewAddress' => 1,
            'customerNeedsGeocoding' => 1,
        ]);
    }

    public function tag($tag)
    {
        $Tags = Factory::get('CustomerTags');
        $Tag  = $Tags->set_for_customer($this->id(), $tag);
    }

    public function detag($tag)
    {
        $Tags = Factory::get('CustomerTags');
        $Tags->detag_customer($this->id(), $tag);
    }



    public function update_tag($tag, $date=false)
    {
        $Tags = Factory::get('CustomerTags');

        if ($date) {
            $Tag  = $Tags->set_for_customer_historically($this->id(), $tag, $date);
        }else{
            $Tag  = $Tags->set_for_customer($this->id(), $tag);    
        }      
    }

    public function get_tag($type)
    {
        $Tags = Factory::get('CustomerTags');
        $Tag  = $Tags->get_current_for_customer($this->id(), $type);
        if ($Tag) {
            return $Tag->tag();
        } 

        return null;
    }

    public function get_tags($type)
    {
        $Tags = Factory::get('CustomerTags');
        $tags = $Tags->get_all_current_for_customer($this->id(), $type);
        if (Util::count($tags)) {
            return $tags;
        } 

        return null;
    }

    public function tag_for_default_mailing_lists()
    {
        $tags = ['list:newsletter', 'list:offers', 'list:tips'];
        foreach($tags as $tag) {
            $this->tag($tag);
        }

    }

    public function unsubscribe_from_lists()
    {
        $tags = ['list:newsletter', 'list:offers', 'list:tips'];
        $Tags = Factory::get('CustomerTags');
        foreach($tags as $tag) {
            $Tags->detag_customer($this->id(), $tag);
        }
    }

    public function get_license_purchase_count($to_date=false)
    {
        $sql = 'SELECT SUM(oi.itemQty) AS qty 
                FROM tblOrderItems oi, tblOrders o
                WHERE o.orderID=oi.orderID 
                        AND o.customerID=:customer
                        AND o.orderStatus=:status
                        AND o.orderRefund<o.orderItemsTotal
                        AND oi.itemCode IN (\'PERCH\', \'RUNWAY\', \'RUNWAYDEV\')';

        if ($to_date) {
            $sql .= ' AND o.orderDate < :to_date';
        }

        $Query = Factory::get('Query', $sql);
        $Query->set('customer', $this->id(), 'int');
        $Query->set('status', 'PAID');
        if ($to_date) $Query->set('to_date', $to_date);

        return $this->db->get_count($Query);
    }

    public function update_order_interval()
    {
        if ($this->customerLastOrder() > $this->customerFirstOrder()) {
            $dates = $this->get_order_dates($as_timestamps=true);
            if (Util::count($dates) && count($dates)>1) {
                $intervals = [];
                for($i=1; $i<count($dates); $i++) {
                    $intervals[] = $dates[$i]-$dates[$i-1];
                }
                if (count($intervals)) {

                    $average = round(array_sum($intervals)/count($intervals));

                   // echo PHP_EOL. print_r($intervals, true).PHP_EOL;
                    $this->update(['customerOrderInterval'=>$average]);    
                }
                
            }
        }else{
            $this->update(['customerOrderInterval'=>null]);   
        }
    }

    private function delete_from_drip()
    {
        // no longer using drip.
        return true;


        $Conf = Conf::fetch();
        $Drip = new Drip($Conf->drip['token'], $Conf->drip['accountID']);
        $Drip->delete('subscribers/'.$this->customerDripID());
    }

}
