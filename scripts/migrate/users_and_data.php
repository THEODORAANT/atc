#!/usr/bin/env php
<?php
    ini_set('memory_limit','512M');
    
    include(__DIR__.'/../env.php');
    include('OldDB.class.php');

    $old_db = 'dbPerchwww';
    $atc_db = 'dbATC';


    $DB = Factory::get('DB');
    $OldDB = OldDB::fetch();
    $Hasher = Factory::get('PasswordHash', 8, true);
        

    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblCustomers');
    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblOrders');
    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblOrderItems');
    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblLicenses');
    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblUpgrades');
    $DB->execute('TRUNCATE TABLE '.$atc_db.'.tblRegisteredDevelopers');


    // Get customer
    $sql = 'SELECT * FROM '.$old_db.'.tblUsers';
    $rows = $OldDB->get_rows($sql);

    if (Util::count($rows)) {

    	foreach($rows as $customer_row) {

    		// Look to see if customer exists
    		$sql = 'SELECT COUNT(*) FROM '.$atc_db.'.tblCustomers WHERE customerID='.$DB->pdb($customer_row['userID']);
    		$result = $DB->get_count($sql);

    		if ($result===0) {

    			$data = array();

    			foreach($customer_row as $key => $val) {
    				if (substr($key, 0, 4)=='user') {
    					$data['customer'.substr($key, 4)] = $val;
    				}else{
    					$data[$key] = $val;
    				}
    			}

                echo $data['customerFirstName'].' '.$data['customerLastName'].PHP_EOL;

                // hash password
                if (isset($data['customerPassword'])) {
                    $data['customerPassword'] = $Hasher->HashPassword($data['customerPassword']);
                }

                
                unset($data['customerSessionHash']);

    			$DB->insert($atc_db.'.tblCustomers', $data);
    			$customerID = $data['customerID'];


    			// Orders
    			$sql    = 'SELECT * FROM '.$old_db.'.tblOrders WHERE orderStatus="PAID" AND userID='.$DB->pdb($customerID).' ORDER BY orderDate ASC';
    			$orders = $OldDB->get_rows($sql);

    			if (Util::count($orders)) {
    				foreach($orders as $order) {

    					$data = array();
    					
    					foreach($order as $key=>$val) {
    						switch($key) {
    							case 'userID':
    								$data['customerID'] = $customerID;
    							case 'orderSecpayMessage':
    								$data['orderGatewayMessage'] = $val;
    								break;
                                case 'orderFees':
                                    $data['orderFeesGBP'] = $val;
                                    break;
    							case 'orderSecpayResponse':
    							case 'orderSecpayCode':
    							case 'licenseID':
    								break;


    							default: 
    								$data[$key] = $val;
    						}
    					}

                        if ($data['orderCurrencyRate']==0 && $data['orderCurrency']=='GBP') {
                            $data['orderCurrencyRate']=1;
                        }

    					$DB->insert($atc_db.'.tblOrders', $data);

    					// Is this an early order with no order items?
    					if ($order['orderDate']<'2011-01-15') {
							$oi = array();
							$oi['orderID']         = $data['orderID'];
							$oi['itemCode']        = 'PERCH';
							$oi['itemQty']         = 1;
							$oi['itemUnitPrice']   = $order['orderItemsTotal'];
							$oi['itemVatRate']     = $order['orderVATrate'];
							$oi['itemUnitVat']     = $order['orderVAT'];
							$oi['itemTotalPrice']  = $order['orderItemsTotal'];
							$oi['itemTotalVat']    = $order['orderVAT'];
							$oi['itemTotalIncVat'] = ((float)$order['orderItemsTotal'] + (float)$order['orderVAT']);
							$oi['itemDescription'] = 'Perch 1 single site license';

							$DB->insert($atc_db.'.tblOrderItems', $oi);
    					}else{
    						$sql    = 'SELECT * FROM '.$old_db.'.tblOrderItems WHERE orderID='.$DB->pdb($order['orderID']);
    						$order_items = $OldDB->get_rows($sql);
    						if (Util::count($order_items)) {
    							foreach($order_items as $oi) {
    								unset($oi['itemID']);
    								$DB->insert($atc_db.'.tblOrderItems', $oi);
    							}
    						}
    					}


    				}
    			}


    			// Licenses
    			$sql    = 'SELECT * FROM '.$old_db.'.tblLicenses WHERE userID='.$DB->pdb($customerID);
    			$licenses = $OldDB->get_rows($sql);

    			if (Util::count($licenses)) {
    				foreach($licenses as $license) {

    					unset($license['userID']);
    					unset($license['licenseInGallery']);
    					$license['customerID'] = $customerID;
    					$license['productID'] = '1';

    					$DB->insert($atc_db.'.tblLicenses', $license);

    				}
    			}

                // Upgrades
                $sql    = 'SELECT * FROM '.$old_db.'.tblUpgrades WHERE userID='.$DB->pdb($customerID);
                $upgrades = $OldDB->get_rows($sql);

                if (Util::count($upgrades)) {
                    foreach($upgrades as $upgrade) {

                        unset($upgrade['userID']);
                        $upgrade['customerID'] = $customerID;
                        $upgrade['productID'] = '1';

                        $DB->insert($atc_db.'.tblUpgrades', $upgrade);

                    }
                }


                // Reg devs
                $sql    = 'SELECT * FROM '.$old_db.'.tblRegisteredDevelopers WHERE userID='.$DB->pdb($customerID);
                $regdevs = $OldDB->get_rows($sql);

                if (Util::count($regdevs)) {
                    foreach($regdevs as $regdev) {

                        unset($regdev['userID']);
                        $regdev['customerID'] = $customerID;

                        $DB->insert($atc_db.'.tblRegisteredDevelopers', $regdev);

                    }
                }


    		}

    	}


    }







    echo Console::output();