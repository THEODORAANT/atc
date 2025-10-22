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
    
    $Xero      = Factory::get('Xero', $Conf->xero_api_key, $Conf->xero_secret, $Conf->xero_key_dir.'/publickey.cer', $Conf->xero_key_dir.'/privatekey.pem');  
    
    $orders    = $Orders->get_refunds_for_xero();
    
    //echo "20\n";
    
    if (Util::count($orders)) {
        foreach($orders as $Order) {
            

            $Customer   = $Customers->find($Order->customerID());
            
            $total =  (float)$Order->orderRefund() + (float)$Order->orderVATrefund();
            
            // Invoice
            $new_credit_note = array(
                    "Reference"        => $Order->orderInvoiceNumber(),
                    "Type"             =>"ACCRECCREDIT",
                    "Contact"          => array("ContactNumber" => $Customer->id()),
                    "Date"             => date('Y-m-d', strtotime($Order->orderRefundDate())),
                    "Status"           => "DRAFT",
                    "LineAmountTypes"  => "Exclusive",
                    "CreditNoteNumber" => $Order->orderCreditNoteNumber(),
                    "FullPaidOnDate"   => date('Y-m-d'),
                    "SentToContact"    => '0',
                    "HasAttachments"    => '0',
                    "BrandingThemeID"  => '1b06bb28-1440-436c-b4bc-a43a4d38550c', // Perch Sales template
                    "CurrencyCode"     => $Order->orderCurrency(),
                    "SubTotal"         => number_format((float) $Order->orderRefund(), 2, '.', ''),
                    "TotalTax"         => number_format((float) $Order->orderVATrefund(), 2, '.', ''),
                    "Total"            => number_format((float) $total, 2, '.', ''),
                    "LineItems"        => array()
            );
            
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
            

            $new_credit_note['LineItems'][]["LineItem"] = array(
				"Description" => 'Goods refunded - ' . $Order->orderType() . ' - @ ' . $Order->orderCurrencyRate(),
				"Quantity"    => 1,
				"UnitAmount"  => number_format((float) $Order->orderRefund(), 2, '.', ''),
				"TaxType"     => $taxType,
				"TaxAmount"   => number_format((float) $Order->orderVATrefund(), 2, '.', ''),
				"LineAmount"  => number_format((float) $Order->orderRefund(), 2, '.', ''),
				"AccountCode" => $accountCode
			);
            
            $result = $Xero->CreditNotes(array($new_credit_note));

            echo "170\n";
            
            if (is_array($result) && isset($result['Status']) && $result['Status'] == 'OK') {
                
                $data  = array();
                $data['orderRefundedAtXero'] = '1';
                $Order->update($data);


                Util::send_email('grabaperch@gmail.com', 'hello@grabaperch.com', 'Perch Xero Connector', 'Xero output', print_r($Order->to_array(), true)."\n\n".print_r($result, true), '');


            }else{
                $data  = array();
                $data['orderSentToXero'] = '-1';
                $Order->update($data);
                Util::send_email('drew@edgeofmyseat.com', 'hello@grabaperch.com', 'Perch Xero Connector', 'Xero failure', print_r($Order->to_array(), true)."\n\n".print_r($new_credit_note, true)."\n\n".print_r($result, true), '');
            }
            
            echo "74\n";
        }
        
        
        
    }

