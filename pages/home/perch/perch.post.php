<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li><a href="/">Today</a></li>
        <li class="active"><a href="/home/perch/">Perch</a></li>
        <li><a href="/home/runway/">Runway</a></li>
        <li><a href="/home/demos/">Demos</a></li>
        <li><a href="/home/customers/">Customers</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Perch</h1>

		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('PERCH', $today_start, $today_end); ?></h4>
		    <span class="text-muted">Perch today</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('PERCH', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Perch yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('PERCH', $month_start, $month_end); ?></h4>
		    <span class="text-muted">Perch this month</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('PERCH'); ?></h4>
		    <span class="text-muted">Av per day this month</span>
		  </div>
		</div>

		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('P2UPGRADE', $today_start, $today_end); ?></h4>
		    <span class="text-muted">Perch Upgrades today</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('P2UPGRADE', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Perch Upgrades yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('P2UPGRADE', $month_start, $month_end); ?></h4>
		    <span class="text-muted">Perch Upgrades this month</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('P2UPGRADE'); ?></h4>
		    <span class="text-muted">Av per day this month</span>
		  </div>
		</div>


		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('PERCH', '2009-01-01 00:00:00', $today_end); ?></h4>
		    <span class="text-muted">Total Perch licenses</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('P2UPGRADE', '2009-01-01 00:00:00', $today_end); ?></h4>
		    <span class="text-muted">Total Perch upgrades</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day('PERCH'); ?></h4>
		    <span class="text-muted">Av Perch licenses per day</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day('P2UPGRADE'); ?></h4>
		    <span class="text-muted">Av Perch upgrades per day</span>
		  </div>
		</div>

		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php $stockpile = $Dashboard->get_license_stockpile(); echo $stockpile; ?></h4>
		    <span class="text-muted">Stockpile (<?php
		    	$total =  $Dashboard->get_license_total_perch_2();
		    	echo number_format(($stockpile / $total)*100);
		    ?>%)</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		   
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		   
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		   
		  </div>
		</div>


    </div>
  </div>
</div>