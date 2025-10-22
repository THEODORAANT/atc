<?php
use \Stripe\Subscription as Stripe_Subscription;
class Subscriptions extends Factory 
{
	protected $singularClassName = 'Subscription';
    protected $table    = 'tblSubscriptions';
    protected $pk   = 'subID';

    protected $default_sort_column  = 'subID';  
    

    /**
     * Set up subscription with Stripe. Only one per basket.
     * @param  [type] $Basket   [description]
     * @param  [type] $Customer [description]
     * @param  [type] $token    [description]
     * @return [type]           [description]
     */
    public function create_from_basket_with_stripe($Basket, $Customer, $currency,$stripe_token)
    {
        $Products    = Factory::get('Products');
        $Plans       = Factory::get('SubscriptionPlans');

        $basket = $Basket->get_contents($currency);

        $Stripe_Customer = StripeGateway::get_customer($Customer);

        if (Util::count($basket) && Util::count($basket['items'])) {
            foreach($basket['items'] as $item) {

                $Product = $Products->get_by_item_code( $item['code']);

                if ($Product && $Product->productIsSubscription()) {

                    $Plan = $Plans->find_matching_plan([
                            'productID'         => $Product->id(),
                            'planCurrency'      => $currency,
                            'planInterval'      => $Product->optionInterval(),
                            'planIntervalCount' => $Product->optionIntervalCount(),
                            'planTaxRate'       => $basket['totals']['vat_rate'],
                           // 'planAmount'        => ((float)$item['item_price'] + (float)$item['item_vat']),
                        ], $Product);


                    if ($Plan) {
                        /*try {

                            $sub = $Stripe_Customer->subscriptions->create([
                                'plan'     => $Plan->planStripeID(),
                                'quantity' => $item['qty'],
                                ]);

                        } catch (Exception $e) {

                            GatewayLogger::log([
                                'logGateway' => 'STRIPE',
                                'orderID' => $this->id(),
                                'logData' => json_encode(['message'=>$e->getMessage()]),
                            ]);

                            return false;
                        }

                        if ($sub->status == 'active') {*/
                     /*   $sub=Stripe_Subscription::create([
                          'customer' => $Stripe_Customer->id,
                          'items' => [
                            ['price' => $Plan->planStripeID()],
                          ],
                        ]);*/

                            return $this->create([
                                        'customerID'  => $Customer->id(),
                                        'planID'      => $Plan->id(),
                                        'subStripeID' => '',
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
    public function get_StripePlan($subID,$planID)
    {
            $sql = 'SELECT * FROM :table c, :plans p
                    WHERE c.planID=p.planID AND c.planID=:planID AND c.subID=:subID
                    LIMIT 1';

            $Query = Factory::get('Query', $sql);
            $Query->set('table', $this->table, 'table');
            $Query->set('plans', 'tblSubscriptionPlans', 'table');
            $Query->set('planID', $planID, 'int');
            $Query->set('subID', $subID, 'int');

            $row = $this->db->get_row($Query);

            return $this->get_instance($row);
    }

    public function get_for_customer($customerID)
    {
        $sql = 'SELECT * FROM :table c, :plans p
                WHERE c.planID=p.planID AND customerID=:customerID
                    AND subInitialised=1  AND (subCancelled=0 || (subCancelled=1 AND subEnds>:now))
                ORDER BY subEnds DESC';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('plans', 'tblSubscriptionPlans', 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('now', date('Y-m-d H:i:s'));
       // $Query->set('subInitialised', '1');

        $rows = $this->db->get_rows($Query);
        return $this->get_instances($rows); 
    }

    public function get_one_for_customer($customerID, $subID)
    {
        $sql = 'SELECT * FROM :table c, :plans p
                WHERE c.planID=p.planID AND customerID=:customerID AND subID=:subID
                    AND subInitialised=1
                LIMIT 1';

        $Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('plans', 'tblSubscriptionPlans', 'table');
        $Query->set('customerID', $customerID, 'int');
        $Query->set('subID', $subID, 'int');
       // $Query->set('now', date('Y-m-d H:i:s'));
        //print_r($Query);
        $row = $this->db->get_row($Query);
        //echo "row";
       // print_r($row);
        return $this->get_instance($row); 
    }


     public function active_subscription_for_customer($customerID)
      {
           $sql = 'SELECT COUNT(*) as count FROM :table
                   WHERE  customerID=:customerID
                   AND subInitialised=1  AND subCancelled=0 ';

            $Query = Factory::get('Query', $sql);
            $Query->set('table', $this->table, 'table');
            $Query->set('customerID', $customerID, 'int');
            $row = $this->db->get_row($Query);
            $count=  $row["count"];

            if( $count>=1){
            return true;
            }
           return false;
      }

}
