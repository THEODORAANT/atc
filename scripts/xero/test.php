#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $Xero = Factory::get('Xero', $Conf->xero_api_key, $Conf->xero_secret, $Conf->xero_key_dir.'/publickey.cer', $Conf->xero_key_dir.'/privatekey.pem');

    $result = $Xero->TaxRates();

    file_put_contents('result.php', '<'.'?php'. print_r($result, true));