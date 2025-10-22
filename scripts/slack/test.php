#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    Slack::notify('This is a test from ATC');
