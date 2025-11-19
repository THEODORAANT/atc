#!/usr/bin/env php
<?php
    include(__DIR__.'/../env.php');

    $payloadFile = $argv[1] ?? __DIR__.'/manual_charge_payload.json';

    if (!is_file($payloadFile)) {
        fwrite(STDERR, "Unable to find payload file: {$payloadFile}\n");
        exit(1);
    }

    $rawPayload = file_get_contents($payloadFile);
    $decodedPayload = json_decode($rawPayload);

    if (!$decodedPayload || !isset($decodedPayload->object)) {
        fwrite(STDERR, "Payload does not contain a valid charge object.\n");
        exit(1);
    }

    $event = (object) [
        'id' => 'evt_manual_charge_simulation',
        'object' => 'event',
        'api_version' => '2020-08-27',
        'created' => time(),
        'data' => (object) [
            'object' => $decodedPayload->object,
            'previous_attributes' => $decodedPayload->previous_attributes ?? null,
        ],
        'livemode' => (bool) ($decodedPayload->object->livemode ?? false),
        'pending_webhooks' => 1,
        'type' => 'charge.succeeded',
    ];

    $Webhook = new StripeWebhook2021();
    $Webhook->handle($event);

    echo Console::output();
