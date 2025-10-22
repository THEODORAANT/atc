<?php

class Demo extends Core_Base
{
	protected $table  = 'tblDemos';
    protected $pk     = 'demoID';

    private $duration = '3 days';


    public function activate($node)
    {
        if ($this->demoStatus() == 'PENDING') {

            $Conf = Conf::fetch();
            $dt = new DateTime('now', $Conf->displayTimeZone);

            $dt_from = $dt->format('Y-m-d H:i:s');

            $dt->add(DateInterval::createFromDateString($this->duration));

            $dt_to = $dt->format('Y-m-d H:i:s');

            $this->update(array(
                'demoValidFrom' => $dt_from,
                'demoValidTo'   => $dt_to,
                'demoStatus'    => 'LIVE',
                'demoNode'      => $node,
                ));

            return true;

        }

        return false;

    }

    public function kill($node)
    {
        if ($this->demoStatus() == 'LIVE') {

            $this->update(array(
                'demoStatus'    => 'DEAD',
                ));

            return true;
        }

        return false;
    }


    public function send_welcome_email_to_customer()
    {

        $Demos = Factory::get('Demos');
        $count = $Demos->get_pending_count_for_user($this->userID());

        if($count === 0) {

            $sites = $Demos->get_live_for_user($this->userID());

            $s = '';

            if($sites) {
                foreach($sites as $Site) {
                    // .= to =
                    // only include the most recent.
                    $s = '<p>View the <a href="http://'.$Site->demoHost().'.'.$Site->demoNode().'">'.ucfirst($Site->demoSite()) . ' site</a> and log in to its <a href="http://'.$Site->demoHost().'.'.$Site->demoNode().'/perch">control panel</a>.</p>';
                }
            }



            $Users = Factory::get('DemoUsers');
            $User = $Users->find($this->userID());

            $email_file   = 'demo_ready_details.html';

            $Email = Factory::get('Email', $email_file, $use_twig=true);
        	$Email->senderEmail('hello@grabaperch.com');
        	$Email->recipientEmail($User->userEmail());
            $Email->set_bulk($this->to_array());
            $Email->set_bulk($User->to_array());
            $Email->set('site_string', $s);
            $Email->send();
        }
    }

    public function send_thanks_for_trying_email_to_customer()
    {

        // stop this sending as moving drip emails to getdrip.
        return false;

        $Users = Factory::get('DemoUsers');
        $User = $Users->find($this->userID());

        if (is_object($User) && $User->can_send_email('demo_thanks')) {

            $Email = Factory::get('Email', 'demo_thanks.html');
            $Email->senderEmail('hello@grabaperch.com');
            $Email->recipientEmail($User->userEmail());
            $Email->set_bulk($this->to_array());
            $Email->set_bulk($User->to_array());

            $Email->send();

            $User->update(array('userLastThanksForTrying' => Util::time_now()));

            return true;
        }

        return false;

    }


    public function url()
    {
        return 'http://'.$this->demoHost().'.'.$this->demoNode().'/';
    }

    public function login_url()
    {
        return $this->url().$this->demoProduct().'/';
    }



    public function to_array()
    {
    	$out = parent::to_array();

    	return $out;
    }


}