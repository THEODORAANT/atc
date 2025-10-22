<?php
	$Orders = Factory::get('Orders');
	$Order  = $Orders->get_one_by('orderInvoiceNumber', $Page->arg(1));
	if(	$Order  == null){
	$Order     = $Orders->get_one_by('orderID', $Page->arg(1));
	}
	$Customers = Factory::get('Customers');
	$Customer = $Customers->find($Order->customerID());

	$Form = Factory::get('Form', 'refund');

	$req = array();
	$req['orderRefund']       = "Required";
	$req['orderVATrefund']    = "Required";
	$req['orderRefundReason'] = "Required";

	$Form->set_required($req);

	if ($Form->posted() && $Form->validate()) {
		$postvars = ['orderRefund', 'orderVATrefund', 'orderRefundReason', 'revoke'];
		$data = $Form->receive($postvars);
		
		if ($Order->refund($data['orderRefund'], $data['orderVATrefund'], $data['orderRefundReason'])) {

			if (isset($data['revoke']) && $data['revoke']=='1') {
				$Order->cancel_services();
			}

			Alert::set('success', 'Refund processed.');
		}else{
			Alert::set('danger', 'Refund not processed.');
		}
		
	}


	$details = $Order->to_array();
