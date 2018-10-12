<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Events;
use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Event\LogEvent;
use byrokrat\giroapp\Model\Donor;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as Dispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Genkgo\Mail\Queue\QueueInterface;

final class MailingSubscriber implements EventSubscriberInterface
{
    /**
     * @var TemplateReader
     */
    private $templateReader;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * @var QueueInterface
     */
    private $queue;

    public function __construct(
        TemplateReader $templateReader,
        MessageFactory $messageFactory,
        QueueInterface $queue
    ) {
        $this->templateReader = $templateReader;
        $this->messageFactory = $messageFactory;
        $this->queue = $queue;
    }

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
        $donor = $event->getDonor();

        foreach ($this->templateReader->readTemplates($name) as $tmpl) {
            $message = $this->messageFactory->createMessage($tmpl, $donor);

            if (!$message) {
                continue;
            }

            $this->queue->store($message);

            $headers = new HeaderReader($message);

            $dispatcher->dispatch(
                Events::INFO,
                new LogEvent(
                    sprintf(
                        "Queued message '%s' to '%s'",
                        $headers->readHeader('subject'),
                        $headers->readHeader('to')
                    )
                )
            );
        }
    }
}
