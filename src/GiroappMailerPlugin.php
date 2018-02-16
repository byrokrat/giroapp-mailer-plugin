<?php

declare(strict_types = 1);

namespace hanneskod\GiroappMailerPlugin;

(@include __DIR__ . '/../vendor/autoload.php') || require __DIR__ . '/../giroapp-mailer-plugin/vendor/autoload.php';

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

if (interface_exists('Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
    class GiroappMailerPlugin implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
    {
        public static function getSubscribedEvents()
        {
            return [
                \byrokrat\giroapp\Events::DONOR_ADDED => 'onMailableEvent',
                \byrokrat\giroapp\Events::DONOR_REMOVED => 'onMailableEvent',
                \byrokrat\giroapp\Events::MANDATE_APPROVED => 'onMailableEvent',
                \byrokrat\giroapp\Events::MANDATE_REVOKED => 'onMailableEvent',
                \byrokrat\giroapp\Events::MANDATE_INVALIDATED => 'onMailableEvent',
            ];
        }

        public function onMailableEvent(
            \byrokrat\giroapp\Event\DonorEvent $event,
            string $name,
            \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
        ) {
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

                $dispatcher->dispatch(
                    \byrokrat\giroapp\Events::INFO,
                    new \byrokrat\giroapp\Event\LogEvent($logMsg)
                );
            }
        }
    }
}
