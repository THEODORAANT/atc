<?php

	// Handle a request for an OAuth2.0 Access Token and send the response to the client
	if (!$server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
	    $server->getResponse()->send();
	    die;
	}

	$token = $server->getAccessTokenData(OAuth2\Request::createFromGlobals());

	$customerID = $token['user_id'];

	if (!$customerID) {
		die('Unable to authenticate, sorry! Please try again.');
	}

	$Customers = Factory::get('Customers');
	$Customer  = $Customers->find($customerID);


	if ($Customer->customerToReviewAddress()) {
		die('Unable to authenticate, sorry! Please try again.');
	}


	$is_regdev = is_object($Customer->is_registered_developer());
	$is_staff  = $Customer->is_staff();

	$badge 	= 'user';
	if ($is_regdev) $badge = 'regdev';
	if ($is_staff) $badge = 'staff';


	$result = [
		'login'               => $Customer->customerEmail(),
		'id'                  => $Customer->id(),
		'email'               => $Customer->customerEmail(),
		'gravatar_id'         => md5($Customer->customerEmail()),
		'url'                 => $Customer->customerURL(),
		'company'             => $Customer->customerCompany(),
		'name'                => $Customer->customerFirstName() .' '. $Customer->customerLastName(),
		'location'            => $Customer->country_name(),
		'regdev'              => $is_regdev,
		'staff'               => $is_staff,
		'badge'               => $badge,
		'created_at'          => $Customer->customerFirstOrder(),
		'slug'                => $Customer->customerReferralCode(),
		'forum_notifications' => $Customer->customerForumEmailNotifications(),
	];
