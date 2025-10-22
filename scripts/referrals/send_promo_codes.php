#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');
    
    $referral_customer_prefix          = 'RC';
    $referral_customer_discount        = 10;
    $referral_customer_regdev_discount = 25;
    
    $referral_referrer_prefix          = 'RR';
    $referral_referrer_discount        = 10;
    $referral_referrer_regdev_discount = 25;


    
    $DB = Factory::get('DB');
    
    $Customers          = Factory::get('Customers');
    $Orders             = Factory::get('Orders');
    $CustomerPromoCodes = Factory::get('CustomerPromoCodes');
    $PromoCodes         = Factory::get('PromoCodes');

    // Find completed orders within the last 24 hours that have a referral code
    $orders = $Orders->get_recent_with_referral();

    if (Util::count($orders)) {
        foreach($orders as $Order) {

            
            // Check this order hasn't already been issued with codes
            if (!$CustomerPromoCodes->order_has_been_issued_codes($Order->id())) {

                // Purchasing Customer
                $Customer = $Customers->find($Order->customerID());
                $PromoCode = $PromoCodes->generate_single_use($referral_customer_prefix, $referral_customer_discount, $referral_customer_regdev_discount, '+1 YEAR', '0');

                $CustomerPromoCode = $CustomerPromoCodes->create([
                    'promoCode'          => $PromoCode->promoCode(),
                    'customerID'         => $Customer->id(),
                    'promoCreated'       => date('Y-m-d H:i:s'),
                    'originatingOrderID' => $Order->id(),
                    'promoType'          => 'CUSTOMER',
                    ]);

                if ($CustomerPromoCode) {
                    $CustomerPromoCode->send_email();
                }


                // Referrer
                $ReferringCustomer = $Customers->find($Customer->customerReferredBy());
                if ($ReferringCustomer) {

                    $PromoCode = $PromoCodes->generate_single_use($referral_referrer_prefix, $referral_referrer_discount, $referral_referrer_regdev_discount, '+1 YEAR', '0');

                    $CustomerPromoCode = $CustomerPromoCodes->create([
                        'promoCode'          => $PromoCode->promoCode(),
                        'customerID'         => $ReferringCustomer->id(),
                        'promoCreated'       => date('Y-m-d H:i:s'),
                        'originatingOrderID' => $Order->id(),
                        'promoType'          => 'REFERRER',
                        ]);

                    if ($CustomerPromoCode) {
                        $CustomerPromoCode->send_email();
                    }

                }
            }
        }
    }

    echo Console::output();