#!/usr/bin/env php
<?php

    include(__DIR__.'/../env.php');

    $Plans = Factory::get('SubscriptionPlans');
    $Plans->create_plans_with_stripe();

    echo Console::output();
