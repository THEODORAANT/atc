<?php
    /* This is v4 for ATC */

    require('../env.php');
    
    $accountCode = '250';
    $feesAccountCode = '405';

    $xero_accounts = array(
        'CARD'=>array(
                'GBP'=> '1e232ee7-6510-4e30-ab02-d1c1010c3c49',
                'EUR'=> '28b41c39-fd59-42ef-bbeb-fd17e294fa8b', 
                'USD'=> 'a527be38-acb9-4f38-80b6-73ec99b3b07a'
            ),

        'PAYPAL'=>array(
                'GBP'=> '2a306d3c-20e3-488b-8ac3-1078f194a8a8',
                'EUR'=> 'cf3a43a7-bc6b-4c44-9242-b41f40add25b',
                'USD'=> '5a9489ff-f8eb-44c8-b32a-104b7c8ab0be'
            ), 

        'STRIPE'=>array( // Just one account for stripe, always GBP
                'GBP'=> '4B4127B0-1925-4708-916A-A712F24E2D35',
                'EUR'=> '4B4127B0-1925-4708-916A-A712F24E2D35',
                'USD'=> '4B4127B0-1925-4708-916A-A712F24E2D35'
            ),
    );

    $xero_contacts = array(
        'STRIPE' => 'B7526654-055B-4FFD-8FC9-DB8022B87DE5',
        'PAYPAL' => '75893CAC-6DD8-40ED-B92D-B3AFAF708116',
    );

    
    $DB = DB::fetch();
    
    //echo "10\n";
    
    $Orders    = Factory::get('Orders');
    $Customers = Factory::get('Customers');
    $Countries = Factory::get('Countries');
    $Products  = Factory::get('Products');
    
    $Xero      = Factory::get('Xero', $Conf->xero_api_key, $Conf->xero_secret, $Conf->xero_key_dir.'/publickey.cer', $Conf->xero_key_dir.'/privatekey.pem');  
    
    $orders    = $Orders->get_orders_for_xero();
    
    //echo "20\n";
    
    if (Util::count($orders)) {
        foreach($orders as $Order) {
            $Customer   = $Customers->find($Order->customerID());
                    
            if (trim($Customer->customerCompany())!='') {
                $name = $Customer->customerCompany();
            }else{
                $name =  $Customer->customerFirstName().' '.$Customer->customerLastName();
            }
                      
            // Contact
            $contact = array(
        	    "ContactNumber"    => $Customer->id(),
        		"Name"             => $name,
        		"FirstName"        => $Customer->customerFirstName(),
        		"LastName"         => $Customer->customerLastName(),
        		"EmailAddress"     => $Customer->customerEmail(),
        		"TaxNumber"        => $Customer->customerVATnumber(),
        		"Addresses" => array(
        			"Address" => array(

                        array(
                            "AddressType"  => "POBOX",
                            "AddressLine1" => $Customer->customerStreetAdr1(),
                            "AddressLine2" => $Customer->customerStreetAdr2(),
                            "City"         => $Customer->customerLocality(),
                            "Region"       => $Customer->customerRegion(),
                            "PostalCode"   => $Customer->customerPostalCode(),
                            "Country"      => $Customer->country_name()
                        ),
                        array(
                            "AddressType"  => "STREET",
                            "AddressLine1" => $Customer->customerStreetAdr1(),
                            "AddressLine2" => $Customer->customerStreetAdr2(),
                            "City"         => $Customer->customerLocality(),
                            "Region"       => $Customer->customerRegion(),
                            "PostalCode"   => $Customer->customerPostalCode(),
                            "Country"      => $Customer->country_name()
                        )
        			)
        		)
            );
            
            if ($Customer->customerXeroContactID()!='') {
                unset($contact['ContactNumber']);
                $contact['ContactID'] = $Customer->customerXeroContactID();
            }
                
            $contacts_result = $Xero->Contacts(array($contact));
                   
            
            echo "30\n";
            
            // Invoice
            $new_invoice = array(
            		"Type"            =>"ACCREC",
            		"Contact"         => array("ContactNumber" => $Customer->id()),
            		"Date"            => date('Y-m-d', strtotime($Order->orderDate())),
            		"DueDate"         => date('Y-m-d', strtotime($Order->orderDate())),
            		"Status"          => "AUTHORISED",
            		"LineAmountTypes" => "Exclusive",
            		"InvoiceNumber"   => $Order->orderInvoiceNumber(),
            		"Reference"       => $Order->orderAuthCode(),
                    "BrandingThemeID" => '1b06bb28-1440-436c-b4bc-a43a4d38550c', // Perch Sales template
            		"CurrencyCode"    => $Order->orderCurrency(),
            		"SubTotal"        => number_format((float) $Order->orderItemsTotal(), 2, '.', ''),
            		"TotalTax"        => number_format((float) $Order->orderVAT(), 2, '.', ''),
            		"Total"           => number_format((float) $Order->orderValue(), 2, '.', ''),
            		"LineItems"       => array()
            );

            if ($Order->orderCurrencyRate()>0) {
                $new_invoice['CurrencyRate'] = number_format((float) $Order->orderCurrencyRate(), 6, '.', '');
            }

            switch($Order->orderType()) {
                case 'PAYPAL':
                    $new_invoice['Reference'] = $Order->orderPayPalPaymentID();
                    break;

                case 'STRIPE':
                    $new_invoice['Reference'] = $Order->orderStripeChargeID();
                    break;
            }
            
            echo "36\n";

            $Country = $Countries->find($Customer->countryID());
            
            // find tax type
            if ((int)$Order->orderVAT() == 0) {
                               
                if ($Country->countryInEU()) {
                    $taxType = 'ECZROUTPUT';
                }else{
                    $taxType = 'ZERORATEDOUTPUT';
                }
                
            }else{

                if ($Country->countryInEU() && $Country->countryXeroTaxType()) {
                    $taxType = $Country->countryXeroTaxType(); // country-specific tax rate code
                }else{
                    $taxType = 'OUTPUT2'; // new 20% rate    
                }

                
            }
            
            echo "53\n";
            
            $items = $Order->get_items();
            
            if (Util::count($items)) {
                foreach($items as $Item) {

                    $Product = $Products->get_by_item_code($Item->itemCode());

                    $new_invoice['LineItems'][]["LineItem"] = array(
        				"Description" => $Item->itemDescription(),
        				"Quantity"    => number_format((int) $Item->itemQty(), 4, '.', ''),
        				"UnitAmount"  => number_format((float) $Item->itemUnitPrice(), 2, '.', ''),
        				"TaxType"     => $taxType,
        				"TaxAmount"   => number_format((float) $Item->itemUnitVat() * (int) $Item->itemQty(), 2, '.', ''),
        				"LineAmount"  => number_format((float) $Item->itemUnitPrice() * (int) $Item->itemQty(), 2, '.', ''),
        				"AccountCode" => $Product->productXeroAccountCode()
        			);
                }
            }

            
            
            $result = $Xero->Invoices(array($new_invoice));

            echo "170\n";
            
            if (is_array($result) && isset($result['Status']) && $result['Status'] == 'OK') {
                $data  = array();
                $data['orderSentToXero'] = '1';
                $Order->update($data);

                echo "177\n";

                // Create the payment
                
                // which account?
                switch($Order->orderType()) {
                    case 'PAYPAL':
                        $accountID = $xero_accounts['PAYPAL'][$Order->orderCurrency()];
                        break;
                    case 'STRIPE':
                        $accountID = $xero_accounts['STRIPE'][$Order->orderCurrency()];
                        break;
                    default:
                        $accountID = $xero_accounts['CARD'][$Order->orderCurrency()];
                        break;
                }

                $payment = array(
                    'Invoice'      => array('InvoiceNumber'=>$Order->orderInvoiceNumber()),
                    'Account'      => array('AccountID'=>$accountID),
                    'Date'         => date('Y-m-d'),
                    'Amount'       => number_format((float) $Order->orderValue(), 2, '.', ''),
                    'Reference'    => $Order->orderType(),
                );


                if ($Order->orderType()=='STRIPE') {
                    $payment['IsReconciled'] = true;
                }


                // if we have the currency rate (e.g. from Stripe) set it, otherwise let Xero set it.
                if ($Order->orderCurrencyRate()>0) {
                    $payment['CurrencyRate'] = number_format((float) $Order->orderCurrencyRate(), 6, '.', '');
                }
                
                echo "207\n";

                $payment_result = $Xero->Payments(array($payment));

                if (is_array($payment_result) && isset($payment_result['Status']) && $payment_result['Status'] == 'OK') {

                    if ($Order->orderCurrencyRate()==0) {
                        if (isset($payment_result['Payments']['Payment']['CurrencyRate'])) {
                            $data  = array();
                            $data['orderCurrencyRate'] = (float) $payment_result['Payments']['Payment']['CurrencyRate'];
                            $Order->update($data);
                        }
                    }

                    if (isset($payment_result['Payments']['Payment']['PaymentID'])) {

                        $data  = array();
                        $data['orderXeroPaymentID'] = (string) $payment_result['Payments']['Payment']['PaymentID'];
                        $Order->update($data);

                    }

                }

                // Do we know the order fees? If so, log a Bank Transaction with Xero
                if ($Order->orderFeesGBP()>0 || $Order->orderFeesEUR()>0 || $Order->orderFeesUSD()>0) {

                    switch ($Order->orderType()) {
                        case 'PAYPAL':
                            $currency = $Order->orderCurrency();
                            switch($Order->orderCurrency()) {
                                case 'GBP':
                                    $fees = $Order->orderFeesGBP();
                                    break;
                                case 'EUR':
                                    $fees = $Order->orderFeesEUR();
                                    break;
                                case 'USD':
                                    $fees = $Order->orderFeesUSD();
                                    break;
                            }
                            break;
                        case 'STRIPE':
                            $fees = $Order->orderFeesGBP();
                            $currency = 'GBP';
                            break;
                    }

                    $transaction = array(
                        'Type'        => 'SPEND',
                        'Contact'     => ['ContactID'=>$xero_contacts[$Order->orderType()]],
                        'BankAccount' => ['AccountID'=>$accountID],
                        'Date'        => date('Y-m-d'),
                        'Reference'   => $Order->orderInvoiceNumber(),
                    );

                    $transaction['LineItems'][]["LineItem"] = array(
                        "Description" => 'Payment processing fees',
                        "Quantity"    => 1,
                        "UnitAmount"  => number_format((float) $fees, 2, '.', ''),
                        "AccountCode" => $feesAccountCode,
                    );


                    if ($Order->orderType()=='STRIPE') {
                        $transaction['IsReconciled'] = true;
                    }


                    $transaction_result = $Xero->BankTransactions(array($transaction));

                    if (is_array($transaction_result) && isset($transaction_result['Status']) && $transaction_result['Status'] == 'OK') {
                        if (isset($transaction_result['BankTransactions']['BankTransaction']['BankTransactionID'])) {
                            $data  = array();
                            $data['orderXeroBankTransactionID'] = (string) $transaction_result['BankTransactions']['BankTransaction']['BankTransactionID'];
                            $Order->update($data);
                        }
                    }

                }else{
                    $transaction_result = [];
                }


                Util::send_email('grabaperch@gmail.com', 'hello@grabaperch.com', 'Perch Xero Connector', 'Xero output', print_r($Order->to_array(), true)."\n\n".print_r($payment, true)."\n\n".print_r($payment_result, true)."\n\n".print_r($transaction_result, true), '');


            }else{
                $data  = array();
                $data['orderSentToXero'] = '-1';
                $Order->update($data);
                Util::send_email('drew@edgeofmyseat.com', 'hello@grabaperch.com', 'Perch Xero Connector', 'Xero failure', print_r($Order->to_array(), true)."\n\n".print_r($new_invoice, true)."\n\n".print_r($result, true), '');
            }
            
            echo "74\n";
        }
        
        
        
    }

