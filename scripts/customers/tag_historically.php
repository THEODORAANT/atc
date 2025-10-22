#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $month = $argv[1];
    $day = $month .'-01';

    $continue = true;

    $Customers = Factory::get('Customers');

    while($continue) {

            $customers = $Customers->get_historically($day);

            $date = $day.' 00:00:00';

            echo str_repeat('=', 72).PHP_EOL;
            echo $day.' ('.Util::count($customers).' customers)'.PHP_EOL;
            echo str_repeat('=', 72).PHP_EOL;

            if (Util::count($customers)) {

                foreach($customers as $Customer) {

                    //echo 'Tagging '.$Customer->customerFirstName(). ' ' .$Customer->customerLastName();
                    echo '.';

                    /* License tags */

                        // Do they have a license?
                        $licenses_purchased = $Customer->get_license_purchase_count($date);

                        $tag = false;

                        if ($licenses_purchased==1) {
                            $tag = 'licenses:new';
                        }

                        if ($licenses_purchased>1 && $licenses_purchased<10) {
                            $tag = 'licenses:casual';   
                        }

                        if ($licenses_purchased>=10 && $licenses_purchased<=39) {
                            $tag = 'licenses:committed';    
                        }

                        if ($licenses_purchased>=40) {
                            $tag = 'licenses:super';    
                        }


                        if ($tag) {
                            $Customer->update_tag($tag, $date);
                            #echo ' as '.$tag;


                        }

                        #echo PHP_EOL;

                    $Customer->update(['customerTagsUpdated'=>date('Y-m-d H:i:s')]);
                }

            }
            echo PHP_EOL.' '.Util::count($customers) .' customers'.PHP_EOL;

            $day = date('Y-m-d', strtotime($date. ' +1 DAY')).PHP_EOL;


            if (substr($day, 0, 7) != $month) exit;



    }




    //echo Console::output();
