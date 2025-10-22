<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li><a href="/orders/order/<?php echo $Order->orderInvoiceNumber(); ?>">Overview</a></li>
        <li class="active"><a href="/orders/items/<?php echo $Order->orderInvoiceNumber(); ?>">Order items</a></li>
        <li><a href="/orders/refund/<?php echo $Order->orderInvoiceNumber(); ?>">Refund</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Items in order <?php echo $Order->orderInvoiceNumber();?></h1>
    <?php  
          if (Util::count($items)) {
              echo '<table class="table table-condensed table-hover">';

                echo '<tr>';
                echo '<th>Code</th>';
                echo '<th>Qty</th>';
                echo '<th>Unit price</th>';
                echo '<th>VAT rate</th>';
                echo '<th>Unit VAT</th>';
                echo '<th>Total Price</th>';
                echo '<th>Total VAT</th>';
                echo '<th>Total Inc VAT</th>';
                echo '</tr>';


              foreach($items as $Item) {

                echo '<tr>';
                echo '<td>'. $Item->itemCode(). '</td>';
                echo '<td>'. $Item->itemQty(). '</td>';
                echo '<td>'. $Item->itemUnitPrice(). '</td>';
                echo '<td>'. $Item->itemVatRate(). '</td>';
                echo '<td>'. $Item->itemUnitVat(). '</td>';
                echo '<td>'. $Item->itemTotalPrice(). '</td>';
                echo '<td>'. $Item->itemTotalVat(). '</td>';
                echo '<td>'. $Item->itemTotalIncVat(). '</td>';
                echo '</tr>';
              }

              echo '</table>';


          }
    ?>
    <p class="text-muted">Currency: <?php echo $Order->orderCurrency(); ?></p>    


    </div>
  </div>
</div>