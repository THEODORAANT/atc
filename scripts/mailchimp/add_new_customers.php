#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

	set_time_limit(0);

	$list_id = 'b4721fffd9';
	$known_addons = "'blog', 'events', 'gallery', 'backup', 'comments', 'forms', 'members', 'shop-paypal', 'shop-foxycart', 'twitter', 'upgrade', 'mailchimp', 'podcasts'";


    $DB = DB::fetch();

    $MailChimp = new MailChimp('key');

	$seen_emails = array();

    /* Load Suppression List */
	$data             = array_chunk(str_getcsv(file_get_contents('SuppressionList.csv'), ","), 3); 
	$suppression_list = array();
	foreach($data as $datum) $suppression_list[] = $datum[0];


	/* Customers */

	$sql = 'SELECT tblCustomers.*
			FROM tblCustomers
			WHERE customerFirstOrder IS NOT NULL AND customerMailChimpEUID IS NULL';
	$rows = $DB->get_rows($sql);

	foreach($rows as $row) {
		if (!in_array($row['customerEmail'], $suppression_list) && !in_array($row['customerEmail'], $seen_emails)) {

			$merge_vars = array();
			$merge_vars['groupings'] = array();

			// Licenses
			$sql = 'SELECT DISTINCT tblProductVersions.versionMajor
					FROM tblLicenses INNER JOIN tblProductVersions ON tblLicenses.versionID = tblProductVersions.versionID
					WHERE tblProductVersions.productID=1 AND tblLicenses.customerID='.$row['customerID'];
			$licenses = $DB->get_rows($sql);

			if (Util::count($licenses)) {

				$groups = array();

				foreach($licenses as $license) {
					switch ($license['versionMajor']) {
						case '1':
							$groups[] = 'Perch 1';
							break;

						case '2':
							$groups[] = 'Perch 2';
							break;
					}
				}

				// Frequent flyer?
				$sql = 'SELECT COUNT(*) AS qty
						FROM tblLicenses 
						WHERE tblLicenses.customerID='.$row['customerID'];
				$count = $DB->get_count($sql);

				if ($count>9) {
					$groups[] = 'Frequent Flyer';
				}

				$merge_vars['groupings'][] = array(
					'name'   => 'Customers',
					'groups' => $groups,
				);

				$merge_vars['groupings'][] = array(
					'name'   => 'Send me',
					'groups' => array('Special offers', 'Regular Tips and Tricks', 'Product release information'),
				);

			}

			// Add-ons
			$sql = 'SELECT DISTINCT addonSlug
					FROM tblAddonDownloads 
					WHERE customerID='.$row['customerID'].' AND addonSlug IN ('.$known_addons.')';
			$addons = $DB->get_rows($sql);

			if (Util::count($addons)) {
				$downloaded = array();
				foreach($addons as $addon) {
					$downloaded[] = $addon['addonSlug'];
				}

				$merge_vars['groupings'][] = array(
					'name'   => 'Add-ons',
					'groups' => $downloaded,
				);
			}

			$merge_vars['groupings'][] = array(
					'name'   => 'Demo',
					'groups' => array(),
				);


			if ($row['customerLat']!='') {
				$merge_vars['mc_location'] = array(
						'latitude' => $row['customerLat'],
						'longitude' => $row['customerLng'],
					);
			}

			$merge_vars['FNAME']      = $row['customerFirstName'];
			$merge_vars['LNAME']      = $row['customerLastName'];
			$merge_vars['SIGNUPDATE'] = $row['customerFirstOrder'];

			// Add them!
			$result = $MailChimp->call('lists/subscribe', array(
				'id'                => $list_id,
				'email'             => array('email'=>$row['customerEmail']),
				'merge_vars'        => $merge_vars,
				'double_optin'      => 'false',
				'update_existing'   => 'true',
				'replace_interests' => 'true',
				'send_welcome'      => 'false',
				));	

			if (is_array($result) && isset($result['email'])) {
				$DB->update('tblCustomers', array('customerMailChimpEUID'=>$result['euid']), 'customerID', $row['customerID']);
			}


			echo "Added ".$row['customerFirstName']." ".$row['customerLastName'] ." \n";

			$seen_emails[] = $row['customerEmail'];

		}
	}



	/* New Demo List */
	$sql = 'SELECT * FROM tblMailingList WHERE userSource='.$DB->pdb('Demo');
	$demo_list = $DB->get_rows($sql);

	if (Util::count($demo_list)) {
		foreach($demo_list as $row) {
			if (!in_array($row['userEmail'], $suppression_list) && !in_array($row['userEmail'], $seen_emails)) {
				$merge_vars = array();
				$merge_vars['groupings'] = array();
				$merge_vars['groupings'][] = array(
					'name'   => 'Demo',
					'groups' => array('Demo'),
				);
				$merge_vars['groupings'][] = array(
					'name'   => 'Send me',
					'groups' => array('Special offers', 'Regular Tips and Tricks', 'Product release information'),
				);

				$merge_vars['FNAME']      = $row['userFirstName'];
				$merge_vars['LNAME']      = $row['userLastName'];
				$merge_vars['SIGNUPDATE'] = $row['userAdded'];

				// Add them!
				$result = $MailChimp->call('lists/subscribe', array(
					'id'                => $list_id,
					'email'             => array('email'=>$row['userEmail']),
					'merge_vars'        => $merge_vars,
					'double_optin'      => 'false',
					'update_existing'   => 'true',
					'replace_interests' => 'false',
					'send_welcome'      => 'false',
					));	

				echo "Added ".$row['userFirstName']." ".$row['userLastName'] ." \n";

				$seen_emails[] = $row['userEmail'];

			}
		}
	}
