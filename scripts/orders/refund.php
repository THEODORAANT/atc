#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');
 echo "Order";
	$Orders = Factory::get('Orders');
    $Order  = $Orders->find(34417);
    echo "Order"; print_r($Order);
$amount='8.48999977';
$vatAmount='';
$reason="Requested by customer";
      // mark it paid
      print_r([
                                                 'orderRefund'       => $amount,
                                                 'orderVATrefund'    => $vatAmount,
                                                 'orderRefundReason' => $reason,
                                                  'orderRefundedAtXero' => '-2',
                                                                     'orderCreditNoteNumber' => $Orders->get_next_credit_note_number(),
                                                                     'orderRefundDate'   => date('Y-m-d H:i:s'),
                                             ]);
       $Order->update([
                                   'orderRefund'       => $amount,
                                   'orderVATrefund'    => $vatAmount,
                                   'orderRefundReason' => $reason,
                                    'orderRefundedAtXero' => '-2',
                                                       'orderCreditNoteNumber' => $Orders->get_next_credit_note_number(),
                                                       'orderRefundDate'   => date('Y-m-d H:i:s'),
                               ]);


?>
