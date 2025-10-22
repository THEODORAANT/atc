<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li><a href="/licenses/license/<?php echo $License->licenseSlug(); ?>">Overview</a></li>
        <li><a href="/licenses/transfer/<?php echo $License->licenseSlug(); ?>">Transfer</a></li>
        <li class="active"><a href="/licenses/activations/<?php echo $License->licenseSlug(); ?>">Activations</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Activations</h1>
<?php  
      if (Util::count($activations)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Date</th>';
              echo '<th>Domain</th>';
              echo '<th>Perch</th>';
              echo '<th>PHP</th>';
            echo '</tr>';


          foreach($activations as $Activation) {

            echo '<tr>';
              echo '<td>'.date('D, d M Y, H:i', strtotime($Activation->logtime())).'</td>';
              echo '<td>'.$Activation->domain().'</td>';
              echo '<td>'.$Activation->perch_version().'</td>';
              echo '<td>'.$Activation->php_version().'</td>';
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