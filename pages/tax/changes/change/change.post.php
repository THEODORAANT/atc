<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li><a href="/tax/">Countries</a></li>
        <li class="active"><a href="/tax/changes/">Rate changes</a></li>

      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Rate change</h1>
      <?php echo Alert::get(); ?>


        <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form" class="form-horizontal ">
            <fieldset class="well">
                <div class="form-group">
                    <label for="countryID" class="col-sm-2 control-label">Country</label>
                    <div class="input-group col-sm-4">
                        <?php 
                            $opts = [];
                            $opts[] = ['value'=> '', 'label'=>'Choose'];
                            foreach($countries as $Country) $opts[] = ['label'=>$Country->countryName(), 'value'=>$Country->id()];
                            echo $Form->select('countryID', $opts, $Form->get('countryID', $details), 'form-control'); 
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="changeValue" class="col-sm-2 control-label">Tax rate</label>
                    <div class="input-group col-sm-4">
                        <?php echo $Form->text('changeValue', $Form->get('changeValue', $details), 'form-control', false, 'number'); ?>
                        <div class="input-group-addon">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="changeDate" class="col-sm-2 control-label">Date of change</label>
                    <div class="input-group col-sm-4">
                        <?php echo $Form->text('changeDate', $Form->get('changeDate', $details), 'form-control', false, 'date'); ?>
                        
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group col-sm-10 col-sm-offset-2">
                        <?php echo $Form->submit('submit', 'Update rate change', 'btn btn-primary'); ?>
                    </div>
                </div>
            </fieldset>
        </form>

        <form method="post" action="<?php echo Util::html($RmForm->action()); ?>" role="form" class="form-horizontal">
          
          <div class="form-group right">
            
              <div class="input-group col-sm-1 col-sm-offset-10">
                 <?php echo $RmForm->submit('submit', 'Delete rate change', 'btn btn-danger'); ?>
              </div>

          </div>

        </form>

    </div>
  </div>
</div>