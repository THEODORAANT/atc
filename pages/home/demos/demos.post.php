<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
      	<li><a href="/">Today</a></li>
      	<li><a href="/home/perch/">Perch</a></li>
      	<li><a href="/home/runway/">Runway</a></li>
      	<li class="active"><a href="/home/demos/">Demos</a></li>
      	<li><a href="/home/customers/">Customers</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Demos</h1>


		<div class="row placeholders">
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $demos['today']; ?></h4>
		    <span class="text-muted">Demo sites today</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $demos['users_today']; ?></h4>
		    <span class="text-muted">Demo users today</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $demos['month']; ?></h4>
		    <span class="text-muted">Demo sites this month</span>
		  </div>
		  <div class="col-xs-6 col-sm-3 placeholder">
		    <h4 class="big-number"><?php echo $demos['average']; ?></h4>
		    <span class="text-muted">Av demos per day</span>
		  </div>
		</div>


    </div>
  </div>
</div>