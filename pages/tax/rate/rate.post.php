<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li class="active"><a href="/tax/">Countries</a></li>
        <li><a href="/tax/changes/">Rate changes</a></li>

      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		  <h1 class="page-header">Tax rates</h1>

      <?php echo Alert::get(); ?>

      <div class="row">

          <div class="col-xs-6">
            <div class="panel panel-default">
              <div class="panel-heading"><h3 class="panel-title"><?php echo $Country->countryName(); ?></h3></div>
              <div class="panel-body">
                <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form">
                  <p>Changes here take immediate effect. You should normally schedule a rate change instead of changing it here.</p>
                  <div class="form-group">
                    <label for="countryVATRate">Tax rate</label>
                    <div class="input-group">
                        <?php echo $Form->text('countryVATRate', $Form->get('countryVATRate', $details), 'form-control'); ?>
                        <div class="input-group-addon">%</div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="countryXeroTaxType">Xero tax type code</label>
                    <?php echo $Form->text('countryXeroTaxType', $Form->get('countryXeroTaxType', $details), 'form-control'); ?>
                    
                  </div>
                  
                  <?php echo $Form->submit('submit', 'Update now', 'btn btn-danger'); ?>
                </form>

              </div>
            </div>
          </div>

      </div>
    </div>
  </div>
</div>