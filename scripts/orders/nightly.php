#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

	$Orders = Factory::get('Orders');

	$Orders->count_matching_tax_evidence();