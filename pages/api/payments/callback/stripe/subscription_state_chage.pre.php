<?php
	$Page->layout = 'empty';
    $Conf->debug = false;


		try {

            $input        = file_get_contents("php://input");
            //print_r( $input );
            if ($input) {

                $Webhook = new StripeWebhook2021();

                 $stripe_event = json_decode($input);

              //  $stripe_event = $Webhook->decode($input, $_SERVER['HTTP_STRIPE_SIGNATURE']);
               //  print_r( $stripe_event );
                	//Util::send_email('theodora@mooblu.com', 'Perch Stripe Connector', 'Stripe webhook in stripe_event', "\n\n".$stripe_event.'');
                if (!$stripe_event) {
                    throw(new Exception('Invalid request'));
                }
				$Webhook->handle($stripe_event);

				http_response_code(200);

				echo 'OK.';


			} else {
				throw(new Exception('No input'));
			}

		} catch (Exception $e) {


			Util::send_email('theodora@mooblu.com', 'hello@grabaperch.com', 'Perch Stripe Connector', 'Stripe webhook failure', $e->getMessage()."\n\n".$input.'');
            //file_put_contents('output.log',$e->getMessage()."\n\n".$input);
            http_response_code(500);
		}
