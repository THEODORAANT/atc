#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $stripe_event = json_decode(file_get_contents('charge_succeeded.json'));


    $Webhook = new StripeWebhook2021();
    $Webhook->handle($stripe_event);

    echo Console::output();
