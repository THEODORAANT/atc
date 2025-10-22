<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">



            <div class="page-header">
                <h1>Customer reports</h1>
                <?php include(__DIR__.'/_subnav.php'); ?>
            </div>


            <div class="row">
                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Segments</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $CustomersReports->get_license_tag_breakdown();
                            echo Reports::show_table(['Tag', 'Qty'], $results);

                        ?>
                    </div>
                  </div>
                </div>

                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Order intervals</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $CustomersReports->get_segment_buy_intervals();
                            echo Reports::show_table(['Tag', 'Days'], $results);

                        ?>
                        <small>New customers sometimes place orders for upgrades, but only have one license.</small>
                    </div>
                  </div>
                </div>


               
            </div>



        </div>
    </div>
</div>