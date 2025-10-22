#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');
    
    $prefix = 'WP'; // web portfolio
    $discount = 50;
    $devdiscount = 0;

    $count = 100;
    
    $PromoCodes = Factory::get('PromoCodes');

    for($i=0; $i<$count; $i++) {

    	$PromoCode = $PromoCodes->generate_single_use($prefix, $discount, $devdiscount);

    	if ($PromoCode) {
    		echo $PromoCode->promoCode(). PHP_EOL;
    	}

    }