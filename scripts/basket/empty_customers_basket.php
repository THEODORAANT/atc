#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $DB = DB::fetch();
    $valid_products = array("SUBRUNWAY-1M" ,"R2SUBUPGRADE-1M","SUBRUNWAYNEW-1M","YEARSUBRUNWAY-12M");
    //$sql = 'SELECT customerID FROM tblCustomers WHERE customerFirstOrder IS NULL';
    $sql = 'SELECT * FROM dbATC.tblBaskets as b inner join dbATC.tblBasketItems as i on b.basketID=i.basketID order by b.basketDate  desc ';
    $basketitems = $DB->get_rows($sql);

    if (Util::count($basketitems)) {
        foreach($basketitems as $basketitem) {
        $Baskets = Factory::get('Baskets');
		$Basket  = $Baskets->get_for_customer($basketitem["customerID"]);
	    if (is_object($Basket)) {
       // print_r($Basket);    print_r($basketitem);
	      if (!in_array($basketitem["itemCode"], $valid_products)){

                   $Basket->empty_contents();
                   $Basket->delete();
                   echo $basketitem["customerID"];echo "<br/>";
                   echo $basketitem["itemCode"];
                     //print_r($Basket);

	      }

        }



        }
        }

