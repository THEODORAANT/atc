<?php 
	#use \DrewM\Drip\Drip;

	// no longer using Drip
	exit;

	if (!isset($_GET['token']) || $_GET['token']!='FV87uuZrxZLnZxPyX') exit;


	$input = file_get_contents("php://input");	
	$event = json_decode($input, true);

	if ($event && isset($event['event'])) {

		$Customers = Factory::get('Customers');

		$data = $event['data'];

		$Customer = $Customers->find_by_email($data['subscriber']['email']);
		
		// No customer? End it all.
		if (!$Customer) exit;

		switch ($event['event']) {
			case 'subscriber.created':
				$Customer->update([
						'customerDripID' => $data['subscriber']['id'],
					]);
				break;

			case 'subscriber.applied_tag':

				break;			

			case 'subscriber.removed_tag':
				$Customer->detag($data['properties']['tag']);
				break;

			case 'subscriber.unsubscribed_all':
				$Customer->unsubscribe_from_lists();
				break;

		}

	}


	