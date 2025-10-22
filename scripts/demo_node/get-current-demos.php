#!/usr/bin/env php
<?php
	// Config
	
	// JSON file to output to
	$output_file = '/etc/puppet/hieradata/newdemo.perchdemo.com.json';

	// Generate SQL files?
	$generate_sql      = true;
	$sql_template      = '/etc/puppet/modules/perchdemo/templates/SITENAME_db-VERSION.sql.erb';
	$sql_target_folder = '/home/data/sql/';


	// This node
	$node_domain = 'perchlabs.net';

	// ATC
	$atc_url = 'https://atc.perchcms.com/api/demo/perch/get-current';

	
	$post = array(
		'secret' => 'cbbdfd55f07f37b1d05ccf1129e4c110',
		'node'   => $node_domain,
	);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $atc_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-ATC-CLIENT: '.$node_domain));
	$result = curl_exec($ch);

	$out = array();

	if ($result) {

		$json = json_decode($result);

		if (is_object($json) && $json->result == 'OK') {

			$out['demo_sites'] = array();

			foreach($json->demos as $demo) {

				/* Content for JSON */

				$tmp = array(
					'vhostname'    => $demo->demoHost,
					'domain'       => $node_domain,
					'firstname'    => $demo->userFirstName,
					'lastname'     => $demo->userLastName,
					'username'     => $demo->demoUsername,
					'password'     => $demo->demoPassword,
					'email'        => $demo->userEmail,
					'deploy_site'  => $demo->demoSite,
					'core_version' => $demo->demoVersion,
					);

				$out['demo_sites'][$demo->demoHost] = $tmp;



				/* Generate SQL file */

				if ($generate_sql) {

					$target = $sql_target_folder.$demo->demoHost.'_db.sql';

					if (!file_exists($target)) {

						$file_name = str_replace('SITENAME', $demo->demoSite, $sql_template);
						$file_name = str_replace('VERSION', $demo->demoVersion, $file_name);

						$sql = file_get_contents($file_name);

						foreach($tmp as $key=>$val) {

							$safe_val = stripslashes($val);
							$safe_val = addslashes($safe_val);
							$safe_val = str_replace(array('<', '>'), '', $safe_val);
							$safe_val = rtrim($safe_val, '\\');

							$sql = str_replace('<%= '.$key.' %>', $safe_val, $sql);

						}

						file_put_contents($target, $sql);
					}

				}


			}

			file_put_contents($output_file, json_encode($out));


			if ($json->new_sites) {
				// New sites have been added since this node last asked.
				exec('su -c "ssh puppet_cb@newdemo.perchdemo.com sudo puppet agent --onetime --no-daemonize" - puppet_cb');

			}

		}

	}

	

	exit;