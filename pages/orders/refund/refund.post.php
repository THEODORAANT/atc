<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li><a href="/orders/order/<?php echo $Order->orderInvoiceNumber(); ?>">Overview</a></li>
        <li><a href="/orders/items/<?php echo $Order->orderInvoiceNumber(); ?>">Order items</a></li>
        <li class="active"><a href="/orders/refund/<?php echo $Order->orderInvoiceNumber(); ?>">Refund</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

  		<h1 class="page-header">Refund <?php
  		if($Order->orderInvoiceNumber()== null){
  		echo "Order-NO INVOICE :".$Order->orderID();
  		}else{
  		echo $Order->orderInvoiceNumber();
  		}
  		?></h1>

        <?php echo Alert::get(); ?>

        <div class="row">
            <div class="col-xs-6">

                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Refund details (<?php echo $Order->orderCurrency(); ?>)</h3></div>
                    <div class="panel-body">


                        <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form">

                          <div class="form-group">
                            <?php echo $Form->label('orderRefund', 'Refund amount', 'control-label'); ?>
                            <?php echo $Form->text('orderRefund', $details['orderItemsTotal'], 'form-control'); ?>
                          </div>
                          
                          <div class="form-group">
                            <?php echo $Form->label('orderVATrefund', 'VAT refund amount', 'control-label'); ?>
                            <?php echo $Form->text('orderVATrefund', $details['orderVAT'], 'form-control'); ?>
                          </div>

                          <div class="form-group">
                            <?php echo $Form->label('orderRefundReason', 'Reason', 'control-label'); ?>
                            <?php echo $Form->textarea('orderRefundReason', $details['orderRefundReason'], 'form-control input-sm'); ?>
                          </div>

                          <div class="form-group checkbox">
                            <label>
                            <?php echo $Form->checkbox('revoke', 1, 1); ?>
                            Revoke licenses etc from this order
                            </label>
                          </div>

                          <?php echo $Form->submit('submit', 'Refund now', 'btn btn-danger'); ?>
                        </form>


                    </div>
                </div>
            </div>


            <div class="col-xs-6">
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Order</h3></div>
                <div class="panel-body">
                  <table class="table table-condensed">      
                    <tr><th>Gateway</th><td><?php echo $Order->orderType() ;?></td></tr>        
                    <tr><th>Status</th><td><span <?php if( $Order->orderStatus()=="PENDING"){ ?> class="label label-warning"  <?php }else{ ?>  class="label label-success" <?php } ?>><?php echo $Order->orderStatus() ;?></span></td></tr>
                    <tr><th>Currency</th><td><?php echo $Order->orderCurrency() ;?></td></tr>
                    <tr><th>Value</th><td><?php echo $Order->orderValue() ;?></td></tr>
                    <tr><th>Items total</th><td><?php echo $Order->orderItemsTotal() ;?></td></tr>
                    <tr><th>VAT</th><td><?php echo $Order->orderVAT() ;?></td></tr>
                    <tr><th>VAT rate</th><td><?php echo $Order->orderVATrate() ;?>%</td></tr>
                    <tr><th>VAT number</th><td><?php echo $Order->orderVATnumber() ;?></td></tr>
                    <tr><th>Currency rate</th><td><?php echo $Order->orderCurrencyRate() ;?></td></tr>                  
                  </table>
                </div>
              </div>
            </div>

        </div>






    </div>
  </div>
</div>

