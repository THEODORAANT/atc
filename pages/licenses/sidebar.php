<?php
	$opts = [
    'licenses'            => 'Overview',
    'activation-failures' => 'Activation failures',
    'perch'               => 'Perch',
    'runway'              => 'Runway',
    'runwaydev'           => 'Runway Dev',

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

      			echo '<a href="/licenses/'.str_replace('licenses', '', $path).'">'.$label.'</a>';

      			echo '</li>';
      		}
      	?>
      </ul>
    </div>