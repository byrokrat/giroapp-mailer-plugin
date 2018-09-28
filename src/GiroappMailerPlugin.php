<?php
/**
 * This file does a little of everything to load and activate the plugin
 *
 * 1. Loads the plugin autoloader
 * 2. Loads settings
 * 3. Define the subscriber
 */

declare(strict_types = 1);

namespace byrokrat\giroappmailer;

(@include __DIR__ . '/../vendor/autoload.php') || @include __DIR__ . '/../giroapp-mailer-plugin/vendor/autoload.php';

################################################################################

// Edit settings here..
DependencyLocator::setup([
    // smtp authentication string
    'smtp_string' => 'smtp://user:pass@host/',

    // directory where mail templates are stored
    // default value should be fine
    'template_dir' => __DIR__ . '/../templates',

    // directory where queued messages are stored
    // default value should be fine
    'queue_dir' => __DIR__ . '/../queue',
]);

################################################################################

namespace Symfony\Component\EventDispatcher;

// Hack to make this file load when symfony is not in the autoloader
if (!interface_exists('Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
    interface EventSubscriberInterface {}
}

namespace byrokrat\giroappmailer;

use byrokrat\giroapp\Events;
use byrokrat\giroapp\Event\DonorEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MailingSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            Events::DONOR_ADDED => 'onMailableEvent',
            Events::DONOR_REMOVED => 'onMailableEvent',
            Events::MANDATE_APPROVED => 'onMailableEvent',
            Events::MANDATE_REVOKED => 'onMailableEvent',
            Events::MANDATE_INVALIDATED => 'onMailableEvent',
        ];
    }

    public function onMailableEvent(DonorEvent $event, string $name, EventDispatcherInterface $dispatcher)
    {
        DependencyLocator::getWorker()->generateMessages($event->getDonor(), $name, $dispatcher);
    }
}
