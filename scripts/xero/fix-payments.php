<?php
    /* This is v4 for ATC */

    require('../env.php');
    
    $invoices = ['Perch14675'];

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
    $Countries = Factory::get('Countries');
    
    $Xero      = Factory::get('Xero', $Conf->xero_api_key, $Conf->xero_secret, $Conf->xero_key_dir.'/publickey.cer', $Conf->xero_key_dir.'/privatekey.pem');  
    
    //echo "20\n";
    
    if (Util::count($invoices)) {
        foreach($invoices as $invoice) {

            $Order = $Orders->get_one_by('orderInvoiceNumber', $invoice);

            if (!$Order) {
                echo "not found: $invoice\n";
                continue;
            }
            
            $result = $Xero->Invoices($invoice);

            sleep(1);

            echo "170\n";
            
            if (is_array($result) && isset($result['Status']) && $result['Status'] == 'OK') {

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
                    'Date'         => date('Y-m-d', strtotime($Order->orderDate())),
                    'Amount'       => number_format((float) $Order->orderValue(), 2, '.', ''),
                    'Reference'    => $Order->orderType(),
                );


                if ($Order->orderType()=='STRIPE') {
                    $payment['IsReconciled'] = true;
                }


                // if we have the currency rate (e.g. from Stripe) set it, otherwise let Xero set it.
                if ($Order->orderCurrencyRate()>0) {
                    $payment['CurrencyRate'] = number_format((float) $Order->orderCurrencyRate(), 6, '.', '');
                }else{
                    echo "No currency rate: $invoice\n";
                    continue;
                }
                
                echo "207\n";

                $payment_result = $Xero->Payments(array($payment));

                sleep(1);

                if (is_array($payment_result) && isset($payment_result['Status']) && $payment_result['Status'] == 'OK') {

                    if (isset($payment_result['Payments']['Payment']['PaymentID'])) {

                        $data  = array();
                        $data['orderXeroPaymentID'] = (string) $payment_result['Payments']['Payment']['PaymentID'];
                        $Order->update($data);

                    }

                }

                // Do we know the order fees? If so, log a Bank Transaction with Xero
                $transaction_result = [];
               

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

