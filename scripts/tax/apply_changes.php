#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Countries = Factory::get('Countries');
    $Countries->apply_tax_changes();