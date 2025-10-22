<?php
	$Customers       = Factory::get('Customers');
	$Customer        = $Customers->find($Page->arg(1));
	
	$Products        = Factory::get('Products');
	$ProductVersions = Factory::get('ProductVersions');
	$products        = $Products->get_by('productIsService', 0);
	
	$Licenses        = Factory::get('Licenses');
	$Upgrades        = Factory::get('Upgrades');

	$Form = Factory::get('Form', 'add');

	$req = array();
	$req['productID']       = "Required";
	
	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['productID'];

		$data = $Form->receive($postvars);
		
		$productID = $data['productID'];
		$Product = $Products->find($productID);
		if ($Product) {

			$ProductVersion = $ProductVersions->get_latest($Product->id());

			switch($Product->productCode()) {

				case 'PERCH':
				case 'RUNWAY':
				case 'RUNWAYDEV':
					$License = $Licenses->create($Product, $ProductVersion, $Customer->id(), NULL);
					break;
				case 'R2SUBUPGRADE':
            		 $Upgrade    = $Upgrades->create([
            					                    'upgradeDate'    => date('Y-m-d H:i:s'),
            					                    'customerID'     => $Customer->id(),
            					                    'orderID'        => 0,
            					                    'productID'      => PROD_PERCH,
            					                    'versionMajor'   => '3',
            					                    'toProductID'    => PROD_R2SUBUPGRADE,
            					                    'toVersionMajor' => '4',
            					                ]);
            		  break;

				case 'R2UPGRADE':
					$Upgrade    = $Upgrades->create([
					                    'upgradeDate'    => date('Y-m-d H:i:s'),
					                    'customerID'     => $Customer->id(),
					                    'orderID'        => 0,
					                    'productID'      => PROD_PERCH,
					                    'versionMajor'   => '2',
					                    'toProductID'    => PROD_RUNWAY,
					                    'toVersionMajor' => '2',
					                ]);
					break;

				case 'P2UPGRADE':
					$Upgrade    = $Upgrades->create([
					                    'upgradeDate'    => date('Y-m-d H:i:s'),
					                    'customerID'     => $Customer->id(),
					                    'orderID'        => 0,
					                    'productID'      => PROD_PERCH,
					                    'versionMajor'   => '1',
					                    'toProductID'    => PROD_PERCH,
					                    'toVersionMajor' => '2',
									]);
					break;

				case 'DEVELOPER':
					$Developers = Factory::get('RegisteredDevelopers');
					$Developer = $Developers->get_by_customer($Customer->id());

					if (is_object($Developer)) {
					    // if already a developer, extend
					    $Developer->extend(12);
					}else{
					    // else create new
					    $Developer  = $Developers->create([
											'customerID'          => $Customer->id(),
											'devSubscriptionFrom' => date('Y-m-d H:i:s'),
											'devSubscriptionTo'   => date('Y-m-d H:i:s', strtotime('+12 MONTHS')),
					    				]);
					}
					break;

			}	

			Alert::set('success', 'License added. <a href="/customers/customer/'.$Customer->id().'">Back to customer</a>');
		}



		
		
	}

