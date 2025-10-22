<?php
	$opts = [
    'customers'           => 'Overview',
    'top'                 => 'Top customers',
    'top100'              => 'Top 100',
    'regdevs'              => 'Registered Developers',
    'perch'               => 'Perch customers',
    'runway'              => 'Runway customers',
    'runwaydev'           => 'Runway Dev customers',

	];
?>
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
      	<?php 
      		foreach($opts as $path=>$label) {
      			
      			if ($Page->URL->page == $path) {
      				echo '<li class="active">';
      			}else{
      				echo '<li>';
      			}

      			echo '<a href="/customers/'.str_replace('customers', '', $path).'">'.$label.'</a>';

      			echo '</li>';
      		}
      	?>
      </ul>
    </div>