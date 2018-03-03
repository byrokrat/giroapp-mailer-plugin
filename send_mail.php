#!/usr/bin/env php
<?php

declare(strict_types = 1);

namespace hanneskod\GiroappMailerPlugin;

(@include __DIR__ . '/../plugins/GiroappMailerPlugin.php') || @include __DIR__ . '/src/GiroappMailerPlugin.php';

use Genkgo\Mail\Exception\AbstractProtocolException;
use Genkgo\Mail\Exception\EmptyQueueException;

echo "Sending giroapp queued messages\n";

$queue = DependencyLocator::getMessageQueue();
$transport = DependencyLocator::getMessageTransport();

try {
    while ($message = $queue->fetch()) {
        try {
            $transport->send($message);
            printf(
                "Sent message '%s' to '%s'\n",
                iconv_mime_decode((string)$message->getHeader('subject')[0]->getValue()),
                iconv_mime_decode((string)$message->getHeader('to')[0]->getValue())
            );
        } catch (AbstractProtocolException $e) {
            $queue->store($message);
            echo "Unable to send messages: transport not ready,\n";
            die(1);
        }
    }
} catch (EmptyQueueException $e) {
    echo "No more messages to send...\n";
    die(0);
}
