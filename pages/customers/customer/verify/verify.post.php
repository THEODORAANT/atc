<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li class="active"><a href="/customers/customer/<?php echo $Customer->id(); ?>">Overview</a></li>
        <li><a href="/customers/top/">Top customers</a></li>
        <li><a href="/customers/top100/">Top 100</a></li>
        <li><a href="/customers/regdevs/">Registered Developers</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

  		<h1 class="page-header">Verify Address</h1>

        <?php echo Alert::get(); ?>

        <div class="row">

            <div class="col-xs-6">
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">License</h3></div>
                <div class="panel-body">
                  <table class="table table-condensed">
                    <tr><th>Street Adr 1</th><td><?php echo $Customer->customerStreetAdr1(); ?></td></tr>
                    <tr><th>Street Adr 2</th><td><?php echo $Customer->customerStreetAdr2(); ?></td></tr>
                    <tr><th>Locality</th><td><?php echo $Customer->customerLocality(); ?></td></tr>
                    <tr><th>Region</th><td><?php echo $Customer->customerRegion(); ?></td></tr>
                    <tr><th>Postal Code</th><td><?php echo $Customer->customerPostalCode(); ?></td></tr>
                    <tr><th>Country</th><td><?php echo $Customer->country_name(); ?></td></tr>
                  </table>
                  <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form">

                    <div class="form-group">
                      <?php echo $Form->hidden('customerAdrManuallyVerified', '1', 'form-control'); ?>
                    </div>
                    
                    <?php echo $Form->submit('submit', 'Verify now', 'btn btn-danger'); ?>
                  </form>

                </div>
              </div>
            </div>

        </div>






    </div>
  </div>
</div>

