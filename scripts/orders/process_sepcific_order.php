#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

	$Orders = Factory::get('Orders');
    $Order  = $Orders->find(28467);

    // mark it paid
    $Order->update([
    		'orderStatus' => 'PAID',
    	]);

    $Order->process_order();

