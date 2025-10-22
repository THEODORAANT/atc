#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');
$customerID=134;


$currency="GBP";
$productID=16;
        $Customers  = Factory::get('Customers');
        $Customer   = $Customers->find($customerID);
 $Subscriptions  = Factory::get('Subscriptions');
  $Orders  = Factory::get('Orders');

           $Products  = Factory::get('Products');
                $Product   = $Customers->find($productID);
            /* $Plan = $Plans->find_matching_plan([
                                    'productID'         => $Product->id(),
                                    'planCurrency'      => $currency,
                                    'planInterval'      => $Product->optionInterval(),
                                    'planIntervalCount' => $Product->optionIntervalCount()
                                    //'planTaxRate'       => $basket['totals']['vat_rate'],
                                    //'planAmount'        => ((float)$item['item_price'] + (float)$item['item_vat']),
                                ], $Product);
                                echo "plan";
        print_r($Plan);
                            if ($Plan) {

                                    $Sub= $Subscriptions->create([
                                                'customerID'  => $Customer->id(),
                                                'planID'      =>42,
                                                'subStripeID' => "sub_1SDMyfCXZLrznbwDOc0NQgh2",
                                                'subCreated'  => date('Y-m-d H:i:s'),
                                                'subEnds'     => date('Y-m-d H:i:s', strtotime('1 month')),
                                                'subQty'      => 1,
                                                'subItem'     => "SUBRUNWAYNEW-1M",
                                                ]);*/
      //  }

  $data = [
            'orderDate'       => date('Y-m-d H:i:s'),
            'customerID'      => $customerID,
            'orderType'       => "STRIPE",
            'orderStatus'     => 'PAID',
            'orderCurrency'   => "GBP",
            'orderValue'      => '7.79',
            'orderFeesGBP' =>'0.35',
            'orderSentToXero' => '0',
            'orderItemsTotal' => '6.49',
            'orderVAT'        => '1.3',
            'orderVATrate'    => '20',
            'orderVATnumber'  => $Customer->customerVATnumber(),
             'orderRef' => 'P'.date('ym'),
             'orderInvoiceNumber' => $Orders->get_next_invoice_number(1),
            //'subscriptionID' => $Sub->subscriptionID(),
            'orderStripeChargeID'=>'pi_3SDNuzCXZLrznbwD038ymnLA',
            'orderVerifyKey'  => uniqid(),
        ];
        print_r( $data);

              /*  $Order = $Orders->create($data);
        echo "Order";
                if ($Order) {
        echo "Order update";
                    // update with order ref (needs ID)
                    $Order->update([
                        'orderRef' => 'P'.date('ym').$Order->id(),
                        ]);

                    // Copy items across from basket
                    $OrderItems = Factory::get('OrderItems');


                    $OrderItems->create([
                        'orderID'         => $Order->id(),
                        'itemCode'        => "SUBRUNWAYNEW-1M",
                        'itemQty'         => "1",
                        'itemUnitPrice'   => "6.49",
                        'itemVatRate'     => "20",
                        'itemUnitVat'     => "1.3",
                        'itemTotalPrice'  => "6.49",
                        'itemTotalVat'    => "1.3",
                        'itemTotalIncVat' => "7.79",
                        'itemDescription' => "Monthly Subscription",
                        ]);
                    }
*/


?>
