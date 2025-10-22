<div class="container-fluid">
  <div class="row">
    <?php include(__DIR__.'/../sidebar.php'); ?>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Activation failures</h1>
<?php  
      if (Util::count($activations)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Date</th>';
              echo '<th>Reason</th>';
             // echo '<th>Details</th>';
            echo '</tr>';


          foreach($activations as $Activation) {

            echo '<tr>';
              echo '<td>'.date('D, d M Y, H:i', strtotime($Activation->logtime())).'</td>';
              echo '<td>'.$Activation->reason().'</td>';
              echo '</tr>';
              echo '<tr><td></td>';
              echo '<td><dl class="dl-horizontal">';
                $post = json_decode($Activation->post());
                foreach($post as $key=>$val) {

                  $key = Util::html($key);
                  $val = Util::html($val);

                  echo '<dt style="text-align: left;">'.strtoupper($key).'</dt>';

                  if ($key=='key' || $key=='host') {
                    echo '<dd><a href="/search/?q='.$val.'">'.$val.'</a></dd>';
                  }else{
                    echo '<dd>'.$val.'</dd>';   
                  }
                 
                }
              echo '</dl></td>';
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