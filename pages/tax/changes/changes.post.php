<div class="container-fluid">
  <div class="row">
    <div class="col-sm-3 col-md-2 sidebar">
      <ul class="nav nav-sidebar">
        <li><a href="/tax/">Countries</a></li>
        <li class="active"><a href="/tax/changes/">Rate changes</a></li>

      </ul>
    </div>
    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

		<h1 class="page-header">Rate changes</h1>
        <?php echo Alert::get(); ?>
<?php  
      if (Util::count($changes)) {
          echo '<table class="table table-condensed table-hover">';

            echo '<tr>';
              echo '<th>Country</th>';
              echo '<th>New rate</th>';
              echo '<th>Changes on</th>';
            echo '</tr>';


          foreach($changes as $Change) {
            $Country = $Countries->find($Change->countryID());
            echo '<tr>';
              echo '<td><a href="/tax/changes/change/'.$Change->id().'">'.$Country->countryName().'</a></td>';
              echo '<td>'.$Change->changeValue().'%</td>';
              echo '<td>'.date('d M Y', strtotime($Change->changeDate())).'</td>';
            echo '</tr>';
          }

          echo '</table>';

          $Paging->set_base_url($Page->URL->path);
          $paging_links = $Paging->get_page_links(true);
          if (Util::count($paging_links)) {
            echo '<ul class="pagination">';
              foreach($paging_links as $link) {
                if (isset($link['selected'])) {
                  echo '<li class="active"><a href="'.$link['url'].'">'.$link['page_number'].'</a></li>';
                }elseif (isset($link['spacer'])) {
                  echo '<li class="disabled"><a href="#">'.$link['page_number'].'</a></li>';
                }else{
                  echo '<li><a href="'.$link['url'].'">'.$link['page_number'].'</a></li>';
                }
                
              }
            echo '</ul>';
          }

      }
?>

        <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form" class="form-horizontal ">
            <fieldset class="well">
                <div class="form-group">
                    <label for="countryID" class="col-sm-2 control-label">Country</label>
                    <div class="input-group col-sm-4">
                        <?php 
                            $opts = [];
                            $opts[] = ['value'=> '', 'label'=>'Choose'];
                            foreach($countries as $Country) $opts[] = ['label'=>$Country->countryName(), 'value'=>$Country->id()];
                            echo $Form->select('countryID', $opts, '', 'form-control'); 
                        ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="changeValue" class="col-sm-2 control-label">Tax rate</label>
                    <div class="input-group col-sm-4">
                        <?php echo $Form->text('changeValue', '', 'form-control', false, 'number'); ?>
                        <div class="input-group-addon">%</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="changeDate" class="col-sm-2 control-label">Date of change</label>
                    <div class="input-group col-sm-4">
                        <?php echo $Form->text('changeDate', '', 'form-control', false, 'date'); ?>
                        
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group col-sm-10 col-sm-offset-2">
                        <?php echo $Form->submit('submit', 'Add new rate change', 'btn btn-primary'); ?>
                    </div>
                </div>
            </fieldset>
        </form>

    </div>
  </div>
</div>