<?php

declare(strict_types = 1);

namespace hanneskod\GiroappMailerPlugin;

(@include __DIR__ . '/../vendor/autoload.php') || @require __DIR__ . '/../giroapp-mailer-plugin/vendor/autoload.php';

################################################################################

# EDIT THIS SECTION...

// TODO make smarter so that password does not have to bee in cleartext...

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

use byrokrat\giroapp\Events;
use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Event\LogEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as Dispatcher;

class GiroappMailerPlugin implements EventSubscriberInterface
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

    public function onMailableEvent(DonorEvent $event, string $name, Dispatcher $dispatcher)
    {
        $templateReader = DependencyLocator::getTemplateReader();
        $messageFactory = DependencyLocator::getMessageFactory();
        $queue = DependencyLocator::getMessageQueue();

        foreach ($templateReader->getTemplatesForEvent($name) as $tmpl) {
            $message = $messageFactory->createMessage($tmpl, $event->getDonor());
            $queue->store($message);

            $logMsg = sprintf(
                "Queued message '%s' to '%s'",
                iconv_mime_decode((string)$message->getHeader('subject')[0]->getValue()),
                $event->getDonor()->getName()
            );

            $dispatcher->dispatch(Events::INFO, new LogEvent($logMsg));
        }
    }
}
