<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

            <h1 class="page-header">Payment reports</h1>

            <div class="row">
                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Payment methods</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $OrdersReports->get_payment_gateway_breakdown();
                            echo Reports::show_table(['Gateway', 'Payments', '%'], $results);

                        ?>
                        <small>Since 01 April 2014</small>
                    </div>
                  </div>
                </div>


                
                
            </div>
        </div>
    </div>
</div>