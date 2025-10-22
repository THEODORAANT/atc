<?php
    include(__DIR__.'/../env.php');
use \Stripe\Stripe;
use \Stripe\Customer as Stripe_Customer;
use \Stripe\Charge as Stripe_Charge;
use \Stripe\Subscription as Stripe_Subscription;

	$DB = DB::fetch();
   function create_from_basket_with_stripe()
    {
             $Conf = Conf::fetch();
        $Products    = Factory::get('Products');
          $Subscriptions       = Factory::get('Subscriptions');
        $Plans       = Factory::get('SubscriptionPlans');
        $Baskets = Factory::get('Baskets');
        $Basket     = $Baskets->get_for_customer(30665);
        $currency="USD";
        $basket = $Basket->get_contents($currency);
        $Customers 	= Factory::get('Customers');
	   $Customer   = $Customers->find(30665);


	   //	Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
	   	Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
       $Stripe_Customer = StripeGateway::get_customer($Customer);
      // echo "stripeee";
      // print_r($Stripe_Customer);

        if (Util::count($basket) && Util::count($basket['items'])) {
            foreach($basket['items'] as $item) {

                $Product = $Products->get_by_item_code($item['code']);
                echo "Product";
 print_r($Product);
                if ($Product && $Product->productIsSubscription()) {

                    $Plan = $Plans->find_matching_plan([
                            'productID'         => $Product->id(),
                            'planCurrency'      => $currency,
                            'planInterval'      => $Product->optionInterval(),
                            'planIntervalCount' => $Product->optionIntervalCount()
                            //'planTaxRate'       => $basket['totals']['vat_rate'],
                            //'planAmount'        => ((float)$item['item_price'] + (float)$item['item_vat']),
                        ], $Product);
                        echo "plan";
print_r($Plan);
                    if ($Plan) {

                            return  $Subscriptions->create([
                                        'customerID'  => $Customer->id(),
                                        'planID'      => $Plan->id(),
                                        'subStripeID' => $Plan->planStripeID(),
                                        'subCreated'  => date('Y-m-d H:i:s'),
                                        'subEnds'     => date('Y-m-d H:i:s', strtotime('1 month')),
                                        'subQty'      => $item['qty'],
                                        'subItem'     => $item['code'],
                                        ]);
}
                }

            }
        }

        return false;
    }
echo create_from_basket_with_stripe();

?>
