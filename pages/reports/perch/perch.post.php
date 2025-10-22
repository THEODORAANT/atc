<div class="container-fluid">
    <div class="row">
        <?php include(__DIR__.'/../sidebar.php'); ?>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

            <h1 class="page-header">Perch sales</h1>

            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Unit sales</h3></div>
                <div class="panel-body">
                    <div id="perch-unit-sales" class="ct-chart"></div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading"><h3 class="panel-title">Revenue</h3></div>
                <div class="panel-body">
                    <div id="perch-revenue" class="ct-chart"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Perch sales</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $OrdersReports->get_monthly_sales_for_product('PERCH');

                            $labels = [];
                            $seriesA = [];
                            $seriesB = [];

                            foreach($results as $row) {
                                if (substr($row['displayDate'], 0, 3)=='Jan') {
                                    $labels[] = "'".substr($row['displayDate'], 4)."'";
                                }else{
                                    $labels[] = "'".substr($row['displayDate'], 0,1)."'";    
                                }
                                
                                $seriesA[] = ($row['nocode'] + $row['code']);
                                $seriesB[] = ((int)str_replace(',', '', $row['valueGBP']));
                            }

                            $labels = array_reverse($labels);
                            $seriesA = array_reverse($seriesA);
                            $seriesB = array_reverse($seriesB);

                            echo '<script>';
                                echo 'var data={
                                    labels: ['.implode(',', $labels).'],
                                    series: [
                                        ['.implode(',', $seriesA).']
                                    ]

                                };';
                                echo "new Chartist.Bar('#perch-unit-sales', data, {
                                    showPoint: false,
                                    fullWidth: false,
                                    axisX: {
                                        showLabel: true,
                                        showGrid: false
                                    },
                                    axisY: {
                                        showLabel: true,
                                        showGrid: true,
                                        // offset: 20,
                                    },
                                    height: '360px',
                                    seriesBarDistance: 0
                                });";

                                echo 'var data={
                                    labels: ['.implode(',', $labels).'],
                                    series: [
                                        ['.implode(',', $seriesB).']
                                    ]

                                };';
                                echo "new Chartist.Bar('#perch-revenue', data, {
                                    showPoint: false,
                                    fullWidth: false,
                                    axisX: {
                                        showLabel: true,
                                        showGrid: false
                                    },
                                    axisY: {
                                        showLabel: true,
                                        showGrid: true,
                                        // offset: 20,
                                    },
                                    height: '360px',
                                    seriesBarDistance: 0
                                });";
                            echo '</script>';

                            echo Reports::show_table(['Date', 'Full price', 'Discounted', 'GBP'], $results);

                        ?>
                    </div>
                  </div>
                </div>

                <div class="col-xs-6">
                  <div class="panel panel-default">
                    <div class="panel-heading"><h3 class="panel-title">Perch upgrades</h3></div>
                    <div class="panel-body">
                        <?php
                            $results = $OrdersReports->get_monthly_sales_for_product('P2UPGRADE');
                            echo Reports::show_table(['Date', 'Full price', 'Discounted', 'GBP'], $results);

                        ?>
                    </div>
                  </div>
                </div>
                
            </div>


        </div>
    </div>
</div>