<?php

use \Stripe\Stripe;
use \Stripe\Plan as Stripe_Plan;

class SubscriptionPlans extends Factory 
{
	protected $singularClassName = 'SubscriptionPlan';
    protected $table    = 'tblSubscriptionPlans';
    protected $pk   = 'planID';

    protected $default_sort_column  = 'planID';  


    public function find_matching_plan($opts, $Product)
    {
        $sql = 'SELECT * FROM '.$this->table.'
                WHERE ';
        $where = [];
        foreach($opts as $field=>$value) {
            $where[] = $field.'='.$this->db->pdb($value);
        }
        $sql .= implode(' AND ', $where);
        $sql .= ' LIMIT 1 ';

        $Plan = $this->get_instance($this->db->get_row($sql));

        if (!is_object($Plan)) {
            // create a new plan locally
            $Plan = $this->create_new_plan($opts, $Product);

            // sych the plan up with Stripe
            $this->create_plans_with_stripe();

            // Refresh this plan object to make sure we have the Stripe  ID
            $Plan = $this->find($Plan->id());
        }

        return $Plan;
    }

    public function create_new_plan($opts, $Product)
    {

        return $this->create([
            'productID'         => $Product->id(),
            'planTitle'         => $Product->productTitle().' '.$opts['planIntervalCount'].' '.$opts['planInterval'],
            'planStatementDesc' => $Product->optionStatementDesc(),
            'planAmount'        => $opts['planAmount'],
            'planCurrency'      => $opts['planCurrency'],
            'planInterval'      => $opts['planInterval'],
            'planIntervalCount' => $opts['planIntervalCount'],
            'planTaxRate'       => $opts['planTaxRate'],
        ]);
    }

    public function create_plans_with_stripe()
    {
        $sql = 'SELECT * FROM '.$this->table.' WHERE planStripeID=\'\'';
        $plans = $this->get_instances($this->db->get_rows($sql));
        echo $sql;
echo "planss count"; echo Util::count($plans);
//print_r($plans);
        if (Util::count($plans)) {
     
            $Conf = Conf::fetch();

           if ($Conf->payment_gateway['test_mode']) {
                Console::log('Using Stripe in test mode');
                Stripe::setApiKey($Conf->stripe['keys']['test']['secret']);
            }else{
                Stripe::setApiKey($Conf->stripe['keys']['live']['secret']);
            }

            foreach($plans as $Plan) {
            echo "*********";
            print_r($Plan);

                $new_plan = Stripe_Plan::create([
                   // 'id'                    => 'plan_'.$Plan->planID(),
                    'amount'                => (int)((float)$Plan->planAmount()*100), // in pence
                    'currency'              => strtolower($Plan->planCurrency()),
                    'interval'              => $Plan->planInterval(),
                    'interval_count'        => (int)$Plan->planIntervalCount(),
                     'product'=> 'prod_PQGonj2jI50BLF'
                   // 'name'                  => $Plan->planTitle(),
                    //'statement_description' => $Plan->planStatementDesc(),
                ]);

                if ($new_plan) {
                    $Plan->update(['planStripeID'=>$new_plan->id]);
                }
            }
        }
    }


}
