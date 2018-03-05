<?php

declare(strict_types = 1);

namespace hanneskod\GiroappMailerPlugin;

use byrokrat\giroapp\Events;
use byrokrat\giroapp\Event\LogEvent;
use byrokrat\giroapp\Model\Donor;
use Genkgo\Mail\Queue\QueueInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as Dispatcher;

class Worker
{
    private $templateReader;
    private $messageFactory;
    private $queue;

    public function __construct(TemplateReader $templateReader, MessageFactory $messageFactory, QueueInterface $queue)
    {
        $this->templateReader = $templateReader;
        $this->messageFactory = $messageFactory;
        $this->queue = $queue;
    }

    public function generateMessages(Donor $donor, string $tmplPostfix, Dispatcher $dispatcher): void
    {
        foreach ($this->templateReader->getTemplatesByPostfix($tmplPostfix) as $tmpl) {
            $message = $this->messageFactory->createMessage($tmpl, $donor);
            $this->queue->store($message);

            $logMsg = sprintf(
                "Queued message '%s' to '%s'",
                iconv_mime_decode((string)$message->getHeader('subject')[0]->getValue()),
                iconv_mime_decode((string)$message->getHeader('to')[0]->getValue())
            );

            $dispatcher->dispatch(Events::INFO, new LogEvent($logMsg));
        }
    }
}
