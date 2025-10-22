<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li class="active"><a href="/tax/">Countries</a></li>
        <li><a href="/tax/changes/">Rate changes</a></li>

      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Tax rates</h1>
<?php  
      if (Util::count($countries)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Country</th>';
              echo '<th>EU</th>';
              echo '<th>Tax rate</th>';
              echo '<th>Xero code</th>';
            echo '</tr>';


          foreach($countries as $Country) {

            echo '<tr>';
              echo '<td><a href="/tax/rate/'.$Country->id().'/">'.$Country->countryName().'</a></td>';
              echo '<td>';
                if ($Country->countryInEU()){
                  echo '<span class="glyphicon glyphicon-ok"></span>';
                }
              echo '</td>';
              echo '<td>';
                if ($Country->countryVATRate()==0) {
                  echo '-';
                }else{
                  echo $Country->countryVATRate().'%';
                }
              echo '</td>';
              echo '<td>';
                echo $Country->countryXeroTaxType();

                  
              echo '</td>';
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