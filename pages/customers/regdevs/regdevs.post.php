<div class="container-fluid">
  <div class="row">
    <?php include(__DIR__.'/../sidebar.php'); ?>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Registered Developers</h1>
<?php  
      if (Util::count($active)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Name</th>';
              echo '<th>Listing name</th>';
              echo '<th>Country</th>';
              echo '<th>From</th>';
              echo '<th>To</th>';
              echo '<th>Months active</th>';
              echo '<th>Listed</th>';
            echo '</tr>';


          foreach($active as $Customer) {

            echo '<tr>';
              echo '<td><a href="/customers/customer/'.$Customer->customerID().'/">'.$Customer->customerFirstName().' '.$Customer->customerLastName().'</a></td>';
              echo '<td>'.$Customer->devTitle().'</td>';
              echo '<td>'.$Customer->countryName().'</td>';
              echo '<td>'.date('d M Y', strtotime($Customer->devSubscriptionFrom())).'</td>';
              echo '<td>'.date('d M Y', strtotime($Customer->devSubscriptionTo())).'</td>';
              echo '<td>';
                    $start = strtotime($Customer->devSubscriptionFrom());
                    $diff  = time()-$start;
                    echo round($diff/60/60/24/30, 0);
              echo '</td>';
              echo '<td><span class="glyphicon '.($Customer->devListingEnabled() ? 'glyphicon-ok' : '').'"></span></td>';
            echo '</tr>';
          }

          echo '</table>';


      }
 
      if (Util::count($lapsed)) {
          echo '<h2>Lapsed</h2>';
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
                echo '<th>Name</th>';
                echo '<th>Listing name</th>';
                echo '<th>Country</th>';
                echo '<th>From</th>';
                echo '<th>To</th>';
                echo '<th>Months active</th>';
                echo '<th>Listed</th>';
              echo '</tr>';


            foreach($lapsed as $Customer) {

              echo '<tr>';
                echo '<td><a href="/customers/customer/'.$Customer->customerID().'/">'.$Customer->customerFirstName().' '.$Customer->customerLastName().'</a></td>';
                echo '<td>'.$Customer->devTitle().'</td>';
                echo '<td>'.$Customer->countryName().'</td>';
                echo '<td>'.date('d M Y', strtotime($Customer->devSubscriptionFrom())).'</td>';
                echo '<td>'.date('d M Y', strtotime($Customer->devSubscriptionTo())).'</td>';
                echo '<td>';
                      $start = strtotime($Customer->devSubscriptionFrom());
                      $diff  = strtotime($Customer->devSubscriptionTo())-$start;
                      echo round($diff/60/60/24/30, 0);
                echo '</td>';
                echo '<td><span class="glyphicon '.($Customer->devListingEnabled() ? 'glyphicon-ok' : '').'"></span></td>';
              echo '</tr>';
            }

            echo '</table>';



      }
?>
    </div>
  </div>
</div>