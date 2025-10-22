<div class="container-fluid">
  <div class="row">
    <?php include(__DIR__.'/sidebar.php'); ?>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Licenses (<?php echo $Paging->total(); ?>)</h1>
<?php  
      if (Util::count($licenses)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Key</th>';
              echo '<th>Domain 1</th>';
              echo '<th>Domain 2</th>';
              echo '<th>Domain 3</th>';
              echo '<th>Date</th>';
            echo '</tr>';


          foreach($licenses as $License) {

            echo '<tr>';
              echo '<td><a href="/licenses/license/'.$License->licenseSlug().'/">'.$License->licenseKey().'</a></td>';
              echo '<td>'.$License->licenseDomain1().'</td>';
              echo '<td>'.$License->licenseDomain2().'</td>';
              echo '<td>'.$License->licenseDomain3().'</td>';
              echo '<td>'.date('d F Y', strtotime($License->licenseDate())).'</td>';
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