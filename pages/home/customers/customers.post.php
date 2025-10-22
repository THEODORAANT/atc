<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
      	<li><a href="/">Today</a></li>
      	<li><a href="/home/perch/">Perch</a></li>
      	<li><a href="/home/runway/">Runway</a></li>
      	<li><a href="/home/demos/">Demos</a></li>
      	<li class="active"><a href="/home/customers/">Customers</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Customers</h1>

		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_customer_count(); ?></h4>
		    <span class="text-muted">Customers</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_percent_repeat(); ?>%</h4>
		    <span class="text-muted">Repeat customers</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php $reps = $Dashboard->get_percent_repeat_this_month(); echo $reps ?>%</h4>
		    <span class="text-muted">Repeat this month</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo 100-$reps; ?>%</h4>
		    <span class="text-muted">New this month</span>
		  </div>
		</div>


		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_customer_tag_count('licenses:new'); ?></h4>
		    <span class="text-muted">New</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_customer_tag_count('licenses:casual'); ?></h4>
		    <span class="text-muted">Casual (2-9)</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_customer_tag_count('licenses:committed'); ?></h4>
		    <span class="text-muted">Committed (10+)</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_customer_tag_count('licenses:super'); ?></h4>
		    <span class="text-muted">Super (40+)</span>
		  </div>
		</div>

		<div class="row">
			<?php  
			      if (Util::count($customers)) {
			          echo '<table class="table table-condensed table-hover">';

			            echo '<tr>';
			              echo '<th>Date</th>';
			              echo '<th>Name</th>';
			              echo '<th>Went from</th>';
			              echo '<th>To</th>';
			            echo '</tr>';


			          foreach($customers as $Customer) {

			            echo '<tr>';
			            	echo '<td>'.date('d M H:i', strtotime($Customer->timestamp())).'</td>';
			              echo '<td><a href="/customers/customer/'.$Customer->id().'/">'.$Customer->customerFirstName().' '.$Customer->customerLastName().'</a></td>';
			              echo '<td>'.$Customer->from_tag().'</td>';
			              echo '<td>'.$Customer->to_tag().'</td>';
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
</div>