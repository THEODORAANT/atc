var addressPoints = [
<?php
	if (Util::count($customers)) {
		foreach($customers as $Customer) {
			if ($Customer->customerLng()!='' && $Customer->customerLat()!='') {
				echo '['.$Customer->customerLat().', '.$Customer->customerLng().', "'.Util::html($Customer->customerFirstName().' '.$Customer->customerLastName()).'", '.$Customer->id().'],';	
			}
			
		}
	}
?>
];