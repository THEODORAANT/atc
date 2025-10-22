<?php
	$Page->layout = 'empty';
	$Conf->debug = false;



	$token 		= filter_input(INPUT_GET, 'token', FILTER_UNSAFE_RAW);

	if ($token == '9431fd944c0395f0fca0c3a87d9448cc') {
		try {

			$input        = file_get_contents("php://input");
			$stripe_event = json_decode($input);

            if ($stripe_event) {

				$Webhook = new StripeWebhook();
				$Webhook->handle($stripe_event);

				http_response_code(200);

				echo 'OK.';

			} else {
				throw(new Exception('No input'));
			}

		} catch (Exception $e) {
			http_response_code(500);
			Util::send_email('grabaperch@gmail.com','theodora@mooblu.com', 'Perch Stripe Connector', 'Stripe webhook failure', $e->getMessage()."\n\n".$input,'');
            //file_put_contents('output.log',$e->getMessage()."\n\n".$input);
		}
	}else{
		http_response_code(500);
		Util::send_email('grabaperch@gmail.com', 'theodora@mooblu.com', 'Perch Stripe Connector', 'Stripe webhook failure', "No token",'');
        //file_put_contents('output.log',"No token.\n\n".$input);
	}

