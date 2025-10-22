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

  		<h1 class="page-header">Add a license</h1>

        <?php echo Alert::get(); ?>



        <div class="row">
            
            <div class="col-xs-6">
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">License</h3></div>
                <div class="panel-body">
                  <form method="post" action="<?php echo Util::html($Form->action()); ?>" role="form">
                    <p class="text-info">This will add a license into the customer's account. It does not notify them - you need to do that manually.</p>
                    <div class="form-group">
                      <?php 
                        $opts = [];
                        if (Util::count($products)) {
                            foreach($products as $Product) {
                                $opts[] = ['label'=>$Product->productCode().' - '.$Product->productTitle(), 'value'=>$Product->id()];
                            }
                        }

                        echo $Form->select('productID', $opts, 'form-control'); ?>
                    </div>
                    
                    <?php echo $Form->submit('submit', 'Add now', 'btn btn-success'); ?>
                  </form>

                </div>
              </div>
            </div>

            <div class="col-xs-6">
              <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Customer</h3></div>
                <div class="panel-body">
                  <table class="table table-condensed">
                    <tr><th>First name</th><td><?php echo $Customer->customerFirstName(); ?></td></tr>
                    <tr><th>Last name</th><td><?php echo $Customer->customerLastName(); ?></td></tr>
                    <tr><th>Email</th><td><?php echo $Customer->customerEmail(); ?></td></tr>
                    <tr><th>Company</th><td><?php echo $Customer->customerCompany(); ?></td></tr>
                  </table>
                </div>
              </div>
            </div>

        </div>






    </div>
  </div>
</div>

