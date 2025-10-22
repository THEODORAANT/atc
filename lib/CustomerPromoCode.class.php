<?php

class CustomerPromoCode extends Core_Base 
{
	protected $table  = 'tblCustomerPromoCodes';
    protected $pk     = 'id';


    /**
     * Send the email notifying the customer of their nice new promo code.
     * @return [type] [description]
     */
    public function send_email()
    {
    	$Products = Factory::get('Products');
    	$Product  = $Products->find($this->productID());

    	$Customers = Factory::get('Customers');
    	$Customer  = $Customers->find($this->customerID());


     	$Email = Factory::get('Email', $Product->productPromoCodeEmail(), $use_twig=true);
    	$Email->senderEmail($Product->productEmailFrom());
    	$Email->recipientEmail($Customer->customerEmail());
    	$Email->set('promo', $this->to_array());
    	$Email->set('customer', $Customer->to_array());

    	$Email->bccEmail(''); // don't bcc
 
    	$Email->send();

    }


}
