<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
            <h1 class="page-header">Demos</h1>


            <div class="row">

                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Create a demo site</h3></div>
                    <div class="panel-body">
                        <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form">
                            <div class="form-group">
                                <?php echo $Form->label('demoSite', 'Site', 'control-label'); ?>
                                <?php echo $Form->select('demoSite', [
                                                                        //['label'=>'Default', 'value'=>'default'],
                                                                        ['label'=>'Swift', 'value'=>'swift'],
                                                                        ['label'=>'Nest', 'value'=>'nest'],
                                                                     ], '', 'form-control'); ?>
                            </div>
                            <div class="form-group">
                                <?php echo $Form->label('demoVersion', 'Version', 'control-label'); ?>
                                <?php 
                                    $Versions = Factory::get('ProductVersions');
                                    $versions = $Versions->get_versions_for_demo_options();
                                    $opts = [];
                                    if (Util::count($versions)) {
                                        foreach($versions as $Version) {
                                            $opts[] = ['label'=>$Version->versionCode(), 'value'=>$Version->versionCode()];
                                        }
                                    }
                                    echo $Form->select('demoVersion', $opts, '', 'form-control'); ?>
                            </div>
                            <div class="form-group">
                                <?php echo $Form->label('demoUsername', 'Username', 'control-label'); ?>
                                <?php 
                                    $name = Util::urlify($AuthenticatedUser->userFirstName()).'.'.Util::urlify($AuthenticatedUser->userLastName());
                                    echo $Form->text('demoUsername', $name, 'form-control'); 
                                ?>
                            </div>
                            <div class="form-group">
                                <?php echo $Form->label('demoPasswordClear', 'Password', 'control-label'); ?>
                                <?php echo $Form->text('demoPasswordClear', 'password', 'form-control'); ?>
                            </div>
                            <div class="form-group">
                                <?php echo $Form->label('demoNode', 'Node', 'control-label'); ?>
                                <?php echo $Form->select('demoNode', [
                                                                        ['label'=>'Any available', 'value'=>'*'],
                                                                        ['label'=>'perchlabs.net', 'value'=>'perchlabs.net'],
                                                                        ['label'=>'perchdemo.com', 'value'=>'perchdemo.com'],
                                                                        ], '*', 'form-control'); ?>
                            </div>
                            <div class="form-group">
                                <?php echo $Form->label('demoProduct', 'Product', 'control-label'); ?>
                                <?php echo $Form->select('demoProduct', [
                                                                            ['label'=>'Perch', 'value'=>'perch'],
                                                                            ['label'=>'Runway', 'value'=>'runway'],
                                                                        ], '', 'form-control'); ?>
                            </div>
                            
                            
                          
                          <?php echo $Form->submit('submit', 'Create', 'btn btn-primary'); ?>
                        </form>
                    </div>
                </div>
                </div>


                <div class="col-xs-6">
                    
                            <?php
                                if (Util::count($pending_demos)) {
                                    foreach($pending_demos as $Demo) {
                                    ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading"><h3 class="panel-title"><?php echo $Demo->demoStatus().': '.$Demo->demoHost() .' - '. $Demo->demoSite(); ?></h3></div>
                                        <div class="panel-body">
                                            <table class="table table-condensed">
                                                <tr>
                                                    <th>Created</th>
                                                    <td><?php echo $Demo->demoCreated(); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Node</th>
                                                    <td><?php echo $Demo->demoNode(); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Version</th>
                                                    <td><?php echo $Demo->demoVersion(); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Customer</th>
                                                    <td><?php echo $Demo->userFirstName().' '.$Demo->userLastName(); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>

                                    <?php
                                    }
                                }
                            ?>
                        
                </div>


               
            </div>



        </div>
    </div>
</div>

