<?php
	$Stash = Factory::get('OAuthStash');
	$token_return = false;

	$response = new OAuth2\Response();

	if (isset($_GET['token'])) {
		$stashed      = $Stash->get_stashed($_GET['token']);
		if (Util::count($stashed)) {
			$request      = $stashed['request'];
			$customerID   = $stashed['customerID'];
			$token_return = true;	
		}
	}else{
		$request = OAuth2\Request::createFromGlobals();
	}

	// validate the authorize request
	if (!$server->validateAuthorizeRequest($request, $response)) {
	    $response->send();
	    die;
	}

	// Send off to log in
	if (empty($_POST) && !$token_return) {
		$token = $Stash->init($request, Util::url_origin($_SERVER).'/oauth/authorize/?token=');

		Util::redirect($Conf->oauth_login_url.$token);
	}

	// print the authorization code if the user has authorized your client
	
	// DM orig:
	//$is_authorized = ($customerID > 0);

	$is_authorized = false;

	if ($customerID > 0) {
		$Customers = Factory::get('Customers');
		$Customer  = $Customers->find($customerID);


		if (!$Customer->customerToReviewAddress()) {
			$is_authorized = true;
		}
	}
	// DM end mod
	


	$server->handleAuthorizeRequest($request, $response, $is_authorized, $customerID);
	if ($is_authorized) {
	  // this is only here so that you get to see your code in the cURL request. Otherwise, we'd redirect back to the client
	  $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=')+5, 40);
	  //exit("SUCCESS! Authorization Code: $code");
	}
	$response->send();