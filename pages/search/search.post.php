<div class="container-fluid">
  <div class="row">

    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Results</h1>
<?php  
      if (Util::count($licenses)) {
          echo '<h2>Licenses</h2>';
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Key</th>';
              echo '<th>Domain 1</th>';
              echo '<th>Date</th>';
            echo '</tr>';

          foreach($licenses as $License) {

            echo '<tr>';
              echo '<td><a href="/licenses/license/'.$License->licenseSlug().'/">'.$License->licenseKey().'</a></td>';
              echo '<td>'.$License->licenseDomain1().'</td>';
              echo '<td>'.date('d F Y', strtotime($License->licenseDate())).'</td>';
            echo '</tr>';
          }

          echo '</table>';

      }

      if (Util::count($customers)) {
          echo '<h2>Customers</h2>';        
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Name</th>';
              echo '<th>Email</th>';
              echo '<th>Company</th>';
            echo '</tr>';


          foreach($customers as $Customer) {

            echo '<tr>';
              echo '<td><a href="/customers/customer/'.$Customer->id().'/">'.$Customer->customerFirstName().' '.$Customer->customerLastName().'</a></td>';
              echo '<td>'.$Customer->customerEmail().'</td>';
              echo '<td>'.$Customer->customerCompany().'</td>';
            echo '</tr>';
          }

          echo '</table>';

      }

      if (Util::count($orders)) {
          echo '<h2>Orders</h2>';
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Invoice #</th>';
              echo '<th>Date</th>';
              echo '<th>Customer</th>';
              echo '<th>Email</th>';
              echo '<th>Amount</th>';
              echo '<th>Gateway</th>';
            echo '</tr>';


          foreach($orders as $Order) {

            echo '<tr>';
              echo '<td><a href="/orders/order/'.$Order->orderInvoiceNumber().'/">'.$Order->orderInvoiceNumber().'</a></td>';
              echo '<td>'.date('d M y H:i', strtotime($Order->orderDate())).'</td>';
              echo '<td>'.$Order->customerFirstName().' '.$Order->customerLastName().'</td>';
              echo '<td>'.$Order->customerEmail().'</td>';
              echo '<td>'.$Order->orderValue().' '.$Order->orderCurrency().'</td>';
              echo '<td>'.$Order->orderType().'</td>';
            echo '</tr>';
          }

          echo '</table>';
         
      }
?>
    </div>
  </div>
</div>