<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header">Deployments</h1>
            <?php echo Alert::get(); ?>

            <div class="row">

                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Create a new Perch release</h3></div>
                    <div class="panel-body">
                        <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form">
                            <div class="form-group">
                                <?php echo $Form->label('versionCode', 'Version number (4.x.x)', 'control-label'); ?>
                                <?php echo $Form->text('versionCode', '', 'form-control'); ?>
                            </div>

                         /* <!--<div class="form-group">
                                <?php echo $Form->label('perchPrice', 'Perch price', 'control-label'); ?>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">£</div>
                                            <?php echo $Form->text('perchPriceGBP', '50', 'form-control'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">$</div>
                                            <?php echo $Form->text('perchPriceUSD', '69', 'form-control'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">€</div>
                                            <?php echo $Form->text('perchPriceEUR', '65', 'form-control'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div> -->*/
                            
                            <div class="form-group">
                                <?php echo $Form->label('runwayPrice', 'Runway price', 'control-label'); ?>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">£</div>
                                            <?php echo $Form->text('runwayPriceGBP', '6.49', 'form-control'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">$</div>
                                            <?php echo $Form->text('runwayPriceUSD', '8.44', 'form-control'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">€</div>
                                            <?php echo $Form->text('runwayPriceEUR', '7.79', 'form-control'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                          /* <!-- <div class="form-group">
                                <?php echo $Form->label('runwaydevPrice', 'Runway Developer price', 'control-label'); ?>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">£</div>
                                            <?php echo $Form->text('runwaydevPriceGBP', '50', 'form-control'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">$</div>
                                            <?php echo $Form->text('runwaydevPriceUSD', '69', 'form-control'); ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <div class="input-group-addon">€</div>
                                            <?php echo $Form->text('runwaydevPriceEUR', '65', 'form-control'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>-->*/

                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <?php echo $Form->checkbox('versionOnSale', '1', true, 'checkbox'); ?>
                                            On sale
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="checkbox">
                                        <label>
                                            <?php echo $Form->checkbox('versionAnnounce', '1', false, 'checkbox'); ?>
                                            Announce
                                        </label>
                                    </div>
                                </div>
                            </div>
                            

                            <p class="help-block">Zip files should be deployed to the server first.</p>
              
                          <?php echo $Form->submit('submit', 'Create', 'btn btn-primary'); ?>
                        </form>
                    </div>
                </div>
                </div>

               
            </div>



        </div>
    </div>
</div>

