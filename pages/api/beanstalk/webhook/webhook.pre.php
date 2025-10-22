<?php
    $Page->layout = 'empty';
    $Conf->debug = false;

    $url = 'https://scrutinizer-ci.com/api/repositories/gp/Perch3/callbacks/post-receive?access_token=';
    $token = 'bfda8009c1cffb590e92927b2197ecdabdf868bdc9a9906b16d863f81631f719';

    $input = file_get_contents("php://input");  
    $event = json_decode($input);

    if (!is_object($event)) {
        die('No event data');
    }

    switch($event->trigger) {

        case 'push':
            $old_rev = $event->payload->before;
            $new_rev = $event->payload->after;
            $ref = $event->payload->ref;

            $encoded = "{\"head\":{\"sha\":\"$new_rev\"},\"base\":{\"sha\":\"$old_rev\"},\"ref\":\"$ref\"}";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url.$token);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json', 'Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_USERAGENT, 'DrewM/Goldenegg/1.0 (github.com/drewm/goldenegg)');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
            curl_setopt($ch, CURLOPT_ENCODING, '');
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $encoded);
            curl_exec($ch);
            curl_close($ch);

            break;

    }