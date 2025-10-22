#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

	$Products = Factory::get('Products');
    $Product  = $Products->find(1);

    echo $Product->generate_license_key();

    