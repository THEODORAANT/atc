<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
      	<li><a href="/">Today</a></li>
      	<li><a href="/home/perch/">Perch</a></li>
      	<li class="active"><a href="/home/runway/">Runway</a></li>
      	<li><a href="/home/demos/">Demos</a></li>
      	<li><a href="/home/customers/">Customers</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Runway</h1>

		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('RUNWAY', $today_start, $today_end); ?></h4>
		    <span class="text-muted">Runway today</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('RUNWAY', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Runway yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('RUNWAY', $month_start, $month_end); ?></h4>
		    <span class="text-muted">Runway this month</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('RUNWAY'); ?></h4>
		    <span class="text-muted">Av per day this month</span>
		  </div>
		</div>


		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2UPGRADE', $today_start, $today_end); ?></h4>
		    <span class="text-muted">Runway Upgrades today</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2UPGRADE', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Runway Upgrades yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2UPGRADE', $month_start, $month_end); ?></h4>
		    <span class="text-muted">Runway Upgrades this month</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('R2UPGRADE'); ?></h4>
		    <span class="text-muted">Av per day this month</span>
		  </div>
		</div>


		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('RUNWAY', '2009-01-01 00:00:00', $today_end); ?></h4>
		    <span class="text-muted">Total Runway licenses</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2UPGRADE', '2009-01-01 00:00:00', $today_end); ?></h4>
		    <span class="text-muted">Total Runway upgrades</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day('RUNWAY'); ?></h4>
		    <span class="text-muted">Av Runway licenses per day</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day('R2UPGRADE'); ?></h4>
		    <span class="text-muted">Av Runway upgrades per day</span>
		  </div>
		</div>

		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('RUNWAYDEV', '2009-01-01 00:00:00', $today_end); ?></h4>
		    <span class="text-muted">Total Runway Dev licenses</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_license_total_runway_crossgrade(); ?></h4>
		    <span class="text-muted">Total Runway Dev crossgrades</span>
		  </div>
		  
		</div>


    </div>
  </div>
</div>