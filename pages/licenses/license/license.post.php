<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li class="active"><a href="/licenses/license/<?php echo $License->licenseSlug(); ?>">Overview</a></li>
        <li><a href="/licenses/transfer/<?php echo $License->licenseSlug(); ?>">Transfer</a></li>
        <li><a href="/licenses/activations/<?php echo $License->licenseSlug(); ?>">Activations</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header"><?php echo $License->licenseDomain1(); ?></h1>

    <div class="row">
      
      <div class="col-xs-6">
        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">License</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr><th>ID</th><td><?php echo $License->licenseID(); ?></td></tr>
              <tr><th>Key</th><td><?php echo $License->licenseKey(); ?></td></tr>
              <tr><th>Domain 1</th><td><a href="http://<?php echo $License->licenseDomain1(); ?>"><?php echo $License->licenseDomain1(); ?></a></td></tr>
              <tr><th>Domain 2</th><td><a href="http://<?php echo $License->licenseDomain2(); ?>"><?php echo $License->licenseDomain2(); ?></a></td></tr>
              <tr><th>Domain 3</th><td><a href="http://<?php echo $License->licenseDomain3(); ?>"><?php echo $License->licenseDomain3(); ?></a></td></tr>
              <tr><th>Date</th><td><?php echo date('d F Y', strtotime($License->licenseDate())); ?></td></tr>
              <tr><th>Description</th><td><?php echo $License->licenseDesc(); ?></td></tr>
              <tr><th>Ignore host</th><td><span class="glyphicon <?php echo $License->licenseIgnoreHost() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
              <tr><th>Active</th><td><span class="glyphicon <?php echo $License->licenseActive() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
              <tr><th>Slug</th><td><?php echo $License->licenseSlug(); ?></td></tr>
              <tr><th>Ownership</th><td><a href="/licenses/transfer/<?php echo $License->licenseSlug(); ?>">Transfer license&hellip;</a></td></tr>

            </table>
          </div>
        </div>
      </div>

      <div class="col-xs-6">
        <div class="panel panel-default">
          <div class="panel-heading"><h3 class="panel-title">Customer</h3></div>
          <div class="panel-body">
            <table class="table table-condensed">
              <tr><th>Name</th><td><a href="/customers/customer/<?php echo $Customer->id(); ?>"><?php echo $Customer->customerFirstName(); ?> <?php echo $Customer->customerLastName(); ?></a></td></tr>
              <tr><th>Email</th><td><?php echo $Customer->customerEmail(); ?></td></tr>
              <tr><th>Company</th><td><?php echo $Customer->customerCompany(); ?></td></tr>
              <tr><th>Country</th><td><?php echo $Customer->country_name(); ?></td></tr>
            </table>
          </div>
        </div>
      </div>
      
      <div class="col-xs-6">

        <?php if (is_object($Order)) { ?>

          <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Order</h3></div>
            <div class="panel-body">
              <?php  
                    
                        echo '<table class="table table-condensed">';

                          echo '<tr>';
                            echo '<th>Invoice #</th>';
                            echo '<th>Date</th>';
                            echo '<th>Amount</th>';
                          echo '</tr>';


                    
                          echo '<tr>';
                            echo '<td><a href="/orders/order/'.$Order->orderInvoiceNumber().'/">'.$Order->orderInvoiceNumber().'</a></td>';
                            echo '<td>'.date('d M y H:i', strtotime($Order->orderDate())).'</td>';
                            echo '<td>'.$Order->orderValue().' '.$Order->orderCurrency().'</td>';
                          echo '</tr>';
                    

                        echo '</table>';
                    
              ?>
            </div>
          </div>

        <?php  } // Order ?>


      </div>
    


    </div>
  </div>
</div>