#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Customers = Factory::get('Customers');
    $customers = $Customers->tag_lapsed();

    echo Console::output();
