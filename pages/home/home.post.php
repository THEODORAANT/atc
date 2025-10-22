<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
      	<li class="active"><a href="/">Today</a></li>
      	<li><a href="/home/perch/">Perch</a></li>
      	<li><a href="/home/runway/">Runway</a></li>
      	<li><a href="/home/demos/">Demos</a></li>
      	<li><a href="/home/customers/">Customers</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Dashboard</h1>


		<div class="row placeholders well">
		  <div class="col-xs-6 col-sm-2">
		  	<img src="https://grabaperch.com/_assets/img/burd1.png" height="80" style="padding-top: 20px;" />
		  </div>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $perch_today; ?></h4>
		    <span class="text-muted">Perch</span>
		  </div>
		  <?php
		  	$renewals = $Dashboard->get_subscription_payments_for_date($today_start, $today_end);
		  	$new_subs = $Dashboard->get_new_subscriptions_for_date($today_start, $today_end);
		  	$renewals = $renewals - $new_subs;
		  ?>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $renewals; ?></h4>
		    <span class="text-muted">Renewal payments</span>
		  </div>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $new_subs; ?></h4>
		    <span class="text-muted">Subscriptions</span>
		  </div>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $perch_downloads; ?></h4>
		    <span class="text-muted">Downloads</span>
		  </div>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $perch_local_licenses; ?></h4>
		    <span class="text-muted">Local licenses</span>
		  </div>
		  
		</div>
		<div class="row placeholders well">
			<div class="col-xs-6 col-sm-2">
				<img src="https://grabaperch.com/_assets/img/runway/burd3.png" height="100" />
			</div>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $runway_today; ?></h4>
		    <span class="text-muted">Runway</span>
		  </div> 
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2UPGRADE', $today_start, $today_end); ?></h4>
		    <span class="text-muted">Runway Upgrades</span>
		  </div>

		  <div class="col-xs-6 col-sm-2 placeholder">
          	<h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2SUBUPGRADE', $today_start, $today_end); ?></h4>
          	<span class="text-muted">Runway Subscription Upgrades</span>
          </div>

		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('RUNWAYDEV', $today_start, $today_end); ?></h4>
		    <span class="text-muted">Runway Developers</span>
		  </div>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $runway_downloads; ?></h4>
		    <span class="text-muted">Downloads</span>
		  </div>
		  <div class="col-xs-6 col-sm-2 placeholder">
		    <h4 class="big-number"><?php echo $runway_local_licenses; ?></h4>
		    <span class="text-muted">Local licenses</span>
		  </div>
		</div>




		<div class="row placeholders">

		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('PERCH', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Perch yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('RUNWAY', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Runway yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2UPGRADE', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Runway Upgrades yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_licenses_for_date('R2SUBUPGRADE', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Runway Subscriptions Upgrades yesterday</span>
		  </div>
		</div>

		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_downloads_for_date(PROD_PERCH, $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Perch downloads yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_downloads_for_date(PROD_RUNWAY, $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Runway downloads yesterday</span>
		  </div>  
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_local_licenses_for_date('PERCH', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Perch locals yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_local_licenses_for_date('RUNWAY', $yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Runway locals yesterday</span>
		  </div>
		</div>


		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('PERCH'); ?></h4>
		    <span class="text-muted">Perch monthly av/day</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('RUNWAY'); ?></h4>
		    <span class="text-muted">Runway monthly av/day</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('R2UPGRADE'); ?></h4>
		    <span class="text-muted">Runway Upgrades monthly av/day</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_average_licenses_per_day_this_month('R2SUBUPGRADE'); ?></h4>
		    <span class="text-muted">Runway Subscriptions Upgrades monthly av/day</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_subscription_payments_for_date($month_start, $month_end); ?></h4>
		    <span class="text-muted">Renewals this month</span>
		  </div>
		</div>


		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_revenue_for_date($today_start, $today_end); ?></h4>
		    <span class="text-muted">Revenue today</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_revenue_for_date($yesterday_start, $yesterday_end); ?></h4>
		    <span class="text-muted">Revenue yesterday</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_revenue_for_date($month_start, $month_end); ?></h4>
		    <span class="text-muted">Revenue this month</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $Dashboard->get_revenue_for_date($last_month_start, $last_month_end); ?></h4>
		    <span class="text-muted">Revenue last month</span>
		  </div>
		</div>

		

    </div>
  </div>
</div>
