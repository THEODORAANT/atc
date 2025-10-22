<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

            <h1 class="page-header">Geographical reports</h1>

            <div class="row">
                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Top countries by value (>£5000)</h3></div>
                    <div class="panel-body">
                     	<?php
                     		$results = $CountryReports->get_top_countries_by_value();
                     		echo Reports::show_table(['Country', 'Code', 'Value GBP', '%'], $results);

                     	?>
                    </div>
                  </div>

                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Exports</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $OrdersReports->get_export_figures();
                            echo Reports::show_table(['Region', 'Sales GBP'], $results);

                        ?>
                    </div>
                  </div>
                </div>

                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Top countries by order value</h3></div>
                    <div class="panel-body">
                     	<?php
                     		$results = $CountryReports->get_top_countries_by_order_value();
                     		echo Reports::show_table(['Country', 'Code', 'Orders', 'Average value GBP'], $results);

                     	?>
                    </div>
                  </div>
                </div>

            </div>


        </div>
    </div>
</div>