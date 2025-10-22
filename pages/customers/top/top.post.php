<div class="container-fluid">
  <div class="row">
    <?php include(__DIR__.'/../sidebar.php'); ?>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Top customers</h1>
<?php  
      if (Util::count($customers)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Name</th>';
              echo '<th>Email</th>';
              echo '<th>Company</th>';
              echo '<th>Licenses</th>';
            echo '</tr>';


          foreach($customers as $Customer) {

            echo '<tr>';
              echo '<td><a href="/customers/customer/'.$Customer->id().'/">'.$Customer->customerFirstName().' '.$Customer->customerLastName().'</a></td>';
              echo '<td>'.$Customer->customerEmail().'</td>';
              echo '<td>'.$Customer->customerCompany().'</td>';
              echo '<td>'.$Customer->qty().'</td>';
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