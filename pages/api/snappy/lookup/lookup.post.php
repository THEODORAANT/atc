<?php 

	if (!isset($_POST['token']) || $_POST['token']!='O5ZXqq8Gf1gx') exit;


	if (isset($_POST['contact'])) {
		$contact = json_decode($_POST['contact']);
		$email = $contact->value;

		$Customers = Factory::get('Customers');
		$Customer = $Customers->find_by_email($email);

		if ($Customer) {
			echo '<a href="https://atc.perchcms.com/customers/customer/'.$Customer->id().'/">View in ATC</a>';
			//print_r($Customer->to_array());
		}

	}
