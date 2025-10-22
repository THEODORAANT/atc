<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li><a href="/licenses/license/<?php echo $License->licenseSlug(); ?>">Overview</a></li>
        <li class="active"><a href="/licenses/transfer/<?php echo $License->licenseSlug(); ?>">Transfer</a></li>
        <li><a href="/licenses/activations/<?php echo $License->licenseSlug(); ?>">Activations</a></li>
      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

  		<h1 class="page-header">Transfer License</h1>

        <?php echo Alert::get(); ?>

        <div class="row">
            <div class="col-xs-6">

                <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Transfer details</h3></div>
                    <div class="panel-body">


                        <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form">

                          <div class="form-group">
                            <?php echo $Form->label('new_owner_email', 'New owner account email', 'control-label'); ?>
                            <?php echo $Form->text('new_owner_email', '', 'form-control'); ?>
                          </div>
                          
                          <?php echo $Form->submit('submit', 'Transfer now', 'btn btn-danger'); ?>
                        </form>


                    </div>
                </div>
            </div>


            <div class="col-xs-6">
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">License</h3></div>
                <div class="panel-body">
                  <table class="table table-condensed">
                    <tr><th>ID</th><td><?php echo $License->licenseID(); ?></td></tr>
                    <tr><th>Key</th><td><?php echo $License->licenseKey(); ?></td></tr>
                    <tr><th>Domain 1</th><td><a href="http://<?php echo $License->licenseDomain1(); ?>"><?php echo $License->licenseDomain1(); ?></a></td></tr>
                    <tr><th>Domain 2</th><td><a href="http://<?php echo $License->licenseDomain2(); ?>"><?php echo $License->licenseDomain2(); ?></a></td></tr>
                    <tr><th>Domain 3</th><td><a href="http://<?php echo $License->licenseDomain3(); ?>"><?php echo $License->licenseDomain3(); ?></a></td></tr>
                    <tr><th>Date</th><td><?php echo date('d F Y', strtotime($License->licenseDate())); ?></td></tr>
                    <tr><th>Description</th><td><?php echo $License->licenseDesc(); ?></td></tr>
                    <tr><th>Ignore host</th><td><span class="glyphicon <?php echo $License->licenseIgnoreHost() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
                    <tr><th>Active</th><td><span class="glyphicon <?php echo $License->licenseActive() ? 'glyphicon-ok' : '' ;?>"></span></td></tr>
                    <tr><th>Slug</th><td><?php echo $License->licenseSlug(); ?></td></tr>
                    
                  </table>
                </div>
              </div>
            </div>

        </div>






    </div>
  </div>
</div>

