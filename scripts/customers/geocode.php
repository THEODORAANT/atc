#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $DB = DB::fetch();


    $sql = 'SELECT * FROM tblCustomers u, tblCountries c WHERE u.countryID=c.countryID AND customerLat IS NULL AND customerActive=1 ORDER BY RAND() LIMIT 16';
    $customers = $DB->get_rows($sql);

    if (Util::count($customers)) {
        foreach($customers as $customer) {
	       sleep(1);
            $data = false;

            $adr = $customer['customerStreetAdr1'] .', '.$customer['customerStreetAdr2'] .', '.$customer['customerLocality'] .', '.$customer['customerRegion'] .', '.$customer['customerPostalCode'] .', '.$customer['countryName'];

            $result = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address='.urlencode($adr));
            if ($result) {
                $json = json_decode($result);
                if ($json && $json->status == 'OK') {

                    $data = array(
                        'customerLat' => $json->results[0]->geometry->location->lat,
                        'customerLng' => $json->results[0]->geometry->location->lng,
                        );

                    if (Util::count($data)) {
                        $DB->update('tblCustomers', $data, 'customerID', $customer['customerID']);
                    }

                }
            }

            echo $customer['customerFirstName'] .' '.$customer['customerLastName'].': '.$adr ."\n";

            

        }
    }



