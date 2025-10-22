<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<div class="page-header">
      <h1> <?php echo $heading; ?></h1>
      <?php include(__DIR__.'/../_subnav.php'); ?>
    </div>
<?php  
      if (Util::count($customers)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Name</th>';
              echo '<th>Email</th>';
              echo '<th>Company</th>';
              
              echo '<th>First buy</th>';
              echo '<th>Last buy</th>';
              echo '<th>Next buy</th>';
              echo '<th>Stockpile</th>';
              echo '<th>Value £</th>';
              //echo '<th>Av.£</th>';
            echo '</tr>';


          foreach($customers as $Customer) {

            echo '<tr>';
              echo '<td><a href="/customers/customer/'.$Customer->id().'/">'.$Customer->customerFirstName().' '.$Customer->customerLastName().'</a></td>';
              echo '<td>'.$Customer->customerEmail().'</td>';
              echo '<td>'.$Customer->customerCompany().'</td>';
              echo '<td>'.$Customer->customerFirstOrder().'</td>';


              echo '<td>'.$Customer->customerLastOrder().'</td>';

              $next_buy = strtotime($Customer->customerLastOrder())+(int)$Customer->customerOrderInterval();

              if ($next_buy < time() && $Customer->stockpile()=='0') {
                echo '<td style="color: red;">';
              }else{
                echo '<td>';
              }
              echo date('Y-m-d', $next_buy).'</td>';
              echo '<td>'.($Customer->stockpile()>0 ? $Customer->stockpile() : '').'</td>';
              echo '<td>'.number_format($Customer->value()).'</td>';
             // echo '<td>'.number_format($Customer->value() / $Customer->qty()).'</td>';
            echo '</tr>';
          }

          echo '</table>';

          if (isset($Paging)) {
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

      }
?>
    </div>
  </div>
</div>