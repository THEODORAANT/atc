<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li class="active"><a href="/orders/order/<?php echo $Order->orderInvoiceNumber(); ?>">Overview</a></li>
        <li><a href="/orders/items/<?php echo $Order->orderInvoiceNumber(); ?>">Order items</a></li>
        <li><a href="/orders/refund/<?php echo $Order->orderInvoiceNumber(); ?>">Refund</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Order <?php echo $Order->orderInvoiceNumber();?></h1>

    <div class="row">
      <div class="col-xs-6">
        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">Order</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr><th>ID</th><td><?php echo $Order->orderID() ;?></td></tr>
              <tr><th>Date</th><td><?php echo $Order->orderDate() ;?></td></tr>
              <tr><th>Ref</th><td><?php echo $Order->orderRef() ;?></td></tr>
              <tr><th>Invoice no</th><td><?php echo $Order->orderInvoiceNumber() ;?></td></tr>
              
              <tr><th>Status</th><td><span <?php if( $Order->orderStatus()=="PENDING"){ ?> class="label label-warning"  <?php }else{ ?>  class="label label-success" <?php } ?> ><?php echo $Order->orderStatus() ;?></span></td></tr>
              <tr><th>Currency</th><td><?php echo $Order->orderCurrency() ;?></td></tr>
              <tr><th>Promo code</th><td><?php echo $Order->orderPromoCode() ;?></td></tr>
              <tr><th>Discount</th><td><?php echo $Order->orderPromoDiscount() ;?></td></tr>
              <tr><th>Value</th><td><?php echo $Order->orderValue() ;?></td></tr>
              <tr><th>Fees</th><td><?php 
                if ($Order->orderCurrency()=='GBP') echo $Order->orderFeesGBP() ; 
                if ($Order->orderCurrency()=='EUR') echo $Order->orderFeesEUR() ; 
                if ($Order->orderCurrency()=='USD') echo $Order->orderFeesUSD() ;?></td></tr>
              <tr><th>Items total</th><td><?php echo $Order->orderItemsTotal() ;?></td></tr>
              <tr><th>VAT</th><td><?php echo $Order->orderVAT() ;?></td></tr>
              <tr><th>VAT rate</th><td><?php echo $Order->orderVATrate() ;?>%</td></tr>
              <tr><th>VAT number</th><td><?php echo $Order->orderVATnumber() ;?></td></tr>
              <tr><th>Referral</th><td><?php echo $Order->orderReferral() ;?></td></tr>
              <tr><th>Currency rate</th><td><?php echo $Order->orderCurrencyRate() ;?></td></tr>

              <?php if ((int)$Order->orderRefund()>0) { ?>

              <tr><th>Refund amount</th><td><?php echo $Order->orderRefund() ;?></td></tr>
              <tr><th>Refunded VAT</th><td><?php echo $Order->orderVATrefund() ;?></td></tr>
              <tr><th>Reason for refund</th><td><?php echo $Order->orderRefundReason() ;?></td></tr>

              <?php }else{
                if($Order->orderInvoiceNumber()==null){
                ?>
               <tr><th>Refund</th><td><a href="/orders/refund/<?php echo $Order->orderID(); ?>">Issue refund&hellip;</a>
               <br/>
               <span style="color:red;font-size:11px;">** Please make sure on the Payment Gateway side the transaction exists</span>
               </td></tr>
              <?php  }else{
               ?>
              <tr><th>Refund</th><td><a href="/orders/refund/<?php echo $Order->orderInvoiceNumber(); ?>">Issue refund&hellip;</a></td></tr>
              <?php
                }
              } ?>


            </table>
          </div>
        </div>
      </div>


      <div class="col-xs-6">
        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">Customer</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr>
                <th>Name</th>
                <td><a href="/customers/customer/<?php echo $Customer->id(); ?>"><?php echo $Customer->customerFirstName() .' '.$Customer->customerLastName(); ?></a>
                </td>
              </tr>
              <tr>
                <th>Company</th>
                <td><?php echo $Customer->customerCompany(); ?></td>
              </tr>
            </table>
          </div>
        </div>

        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">Payment</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr><th>Gateway</th><td><?php echo $Order->orderType() ;?></td></tr>
             <tr><th>Stripe token</th><td><?php echo $Order->orderStripeToken() ;?></td></tr>
             <tr><th>Stripe charge ID</th><td><?php echo $Order->orderStripeChargeID() ;?></td></tr>
             <tr><th>Funds available</th><td><?php echo $Order->orderFundsAvailable() ;?></td></tr>
             <tr><th>PayPal payment</th><td><?php echo $Order->orderPayPalPaymentID() ;?></td></tr>
             <tr><th>PayPal sale</th><td><?php echo $Order->orderPayPalSaleID() ;?></td></tr>
             <tr><th>Verify key</th><td><?php echo $Order->orderVerifyKey() ;?></td></tr>
             <tr><th>Xero payment</th><td><?php echo $Order->orderXeroPaymentID() ;?></td></tr>
             <tr><th>Xero bank transaction</th><td><?php echo $Order->orderXeroBankTransactionID() ;?></td></tr>
             <tr><th>Sent to Xero</th><td><span class="glyphicon <?php echo $Order->orderSentToXero() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
             <tr><th>Reconciled at Xero</th><td><span class="glyphicon <?php echo $Order->orderReconciledAtXero() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
            </table>
          </div>
        </div>
      </div>
 
    <?php if (Util::count($evidence)) { ?>
      <div class="col-xs-6">
        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">Tax Evidence</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr>
                <th>Type</th>
                <th>Detail</th>
                <th>Country</th>
              </tr>
            <?php
                foreach($evidence as $Evidence) {
                    echo '<tr>';
                        echo '<td>'.$Evidence->evidenceType().'</td>';
                        echo '<td>'.$Evidence->evidenceDetail().'</td>';
                        $Country = $Countries->find($Evidence->countryID());
                        echo '<td>'.$Country->countryName().'</td>';
                    echo '</tr>';
                }
            ?>
            </table>
          </div>
        </div>
      </div>
    <?php } // if evidence ?>
    

    </div>


    </div>
  </div>
</div>
