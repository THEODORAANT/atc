<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li class="active"><a href="/orders/">Overview</a></li>
<!--         <li><a href="#">Reports</a></li>
        <li><a href="#">Analytics</a></li>
        <li><a href="#">Export</a></li> -->
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Orders</h1>
<?php  
      if (Util::count($orders)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Invoice #</th>';
              echo '<th>Date</th>';
              echo '<th>Customer</th>';
              echo '<th>Email</th>';
              echo '<th>Amount</th>';
              echo '<th>Gateway</th>';
              echo '<th>VAT evidence</th>';
            echo '</tr>';


          foreach($orders as $Order) {
            
            if ($Order->orderTaxEvidenceItems()==0 && $Order->orderTaxEvidenceItems()!=null) {
              echo '<tr class="bg-danger">';
            }else{
              echo '<tr>';               
            }
              echo '<td><a href="/orders/order/'.$Order->orderInvoiceNumber().'/">'.$Order->orderInvoiceNumber().'</a></td>';
              echo '<td>'.date('d M y H:i', strtotime($Order->orderDate())).'</td>';
              echo '<td>'.$Order->customerFirstName().' '.$Order->customerLastName().'</td>';
              echo '<td>'.$Order->customerEmail().'</td>';
              echo '<td>'.$Order->orderValue().' '.$Order->orderCurrency().'</td>';
              echo '<td>'.$Order->orderType().'</td>';
              if ($Order->orderTaxEvidenceItems()==0 && $Order->orderTaxEvidenceItems()!=null) {
                echo '<td><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span></td>';
              }else{
                echo '<td>'.$Order->orderTaxEvidenceItems().'</td>';  
              }
              
            echo '</tr>';
          }

          echo '</table>';

          $Paging->set_base_url($Page->URL->path);
          $paging_links = $Paging->get_page_links(true);
          if (Util::count($paging_links)) {
            echo '<ul class="pagination">';
              foreach($paging_links as $link) {
                if (isset($link['selected'])) {
                  echo '<li class="active"><a href="'.$link['url'].'">'.$link['page_number'].'</a></li>';
                }elseif (isset($link['spacer'])) {
                  echo '<li class="disabled"><a href="#">'.$link['page_number'].'</a></li>';
                }else{
                  echo '<li><a href="'.$link['url'].'">'.$link['page_number'].'</a></li>';
                }
                
              }
            echo '</ul>';
          }

      }
?>
    </div>
  </div>
</div>