<?php
    /*
        Gets stats on demos for dashboard etc

     */
    
    $Page->layout = 'json';
    $Conf->debug = false;

    $result = array();

    if (filter_input(INPUT_GET, 'secret')=='833517526c06acd17a092de3be01b09e') {
        $Demos = Factory::get('Demos');
        $result = $Demos->get_referrals();
    }
