#!/usr/bin/env php
<?php
    // no longer using drip
    return false;


	use \DrewM\Drip\Drip;
	use \DrewM\Drip\Dataset;

    include(__DIR__.'/../env.php');

	$DB = DB::fetch();

    $sql = 'SELECT * 
    		FROM tblCustomerTagHistory 
    		WHERE sentToMailer=0 
    		ORDER BY timestamp ASC
    		LIMIT 200';

    $tags = $DB->get_rows($sql);

    if (Util::count($tags)) {
    
    	$Customers = Factory::get('Customers');
    	$Drip = new Drip($Conf->drip['token'], $Conf->drip['accountID']);

    	foreach($tags as $row) {

    		$Customer = $Customers->find($row['customerID']);

    		if ($Customer) {

    			echo $Customer->customerFirstName().' '.$Customer->customerLastName().PHP_EOL;

    			// Does the customer have a drip subscriber ID?
    			// If not, create them.
    			if (!$Customer->customerDripID()) {

    				$data = new Dataset('subscribers', [
								'email' => $Customer->customerEmail(),
    							'custom_fields' => [
    								'first_name' => $Customer->customerFirstName(),
    								'last_name'  => $Customer->customerLastName(),
    							],
    					]);

    				$Result = $Drip->post('subscribers', $data);

    				if ($Result->status == 200) {
    					$drip_id = $Result->subscribers[0]['id'];
    					$Customer->update(['customerDripID'=>$drip_id]);
    				}
    			}

    			// Customer now has a Drip ID.

    			// Is From Tag set? If so, remove that tag.
    			if ($row['from_tag']!='') {
    				$method = sprintf('subscribers/%s/tags/%s', $Customer->customerEmail(), $row['from_tag']);
    				$Response = $Drip->delete($method, []);
    			}

    			// Add the new tag
    			if ($row['to_tag']!='') {
    				$data = new Dataset('tags', [
								'email' => $Customer->customerEmail(),
    							'tag'   => $row['to_tag'],
    							]);
    				$Result = $Drip->post('tags', $data);
    			}

                $DB->update('tblCustomerTagHistory', [
							'timestamp' => $row['timestamp'],
							'sentToMailer'=>'1'
							], 'id', $row['id']);             

    		}


    	}
    }