<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

            <h1 class="page-header">Sales reports</h1>

            <div class="row">
                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Top days by unit</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $OrdersReports->get_top_sales_days_by_unit();
                            echo Reports::show_table(['Date', 'Units'], $results);

                        ?>
                    </div>
                  </div>
                </div>


                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Currency Susan</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $OrdersReports->get_currency_breakdown();
                            echo Reports::show_table(['Currecy', 'Orders', '%'], $results);

                        ?>
                        <small>Since trading in multiple currencies (16 July 2012)</small>
                    </div>
                  </div>
                </div>
                
            </div>



        </div>
    </div>
</div>