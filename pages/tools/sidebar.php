<?php
	$opts = [
    'demos'        => 'Demos',
    'deployment'   => 'Deployment',
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

      			echo '<a href="/tools/'.$path.'/">'.$label.'</a>';

      			echo '</li>';
      		}
      	?>
      </ul>
    </div>