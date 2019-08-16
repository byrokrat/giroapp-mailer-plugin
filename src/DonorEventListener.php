<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Event\DonorStateUpdated;
use byrokrat\giroapp\Event\Listener\ListenerInterface;
use byrokrat\giroapp\Utils\ClassIdExtractor;
use Genkgo\Mail\Queue\QueueInterface;
use Psr\Log\LoggerInterface;

final class DonorEventListener implements ListenerInterface
{
    /** @var TemplateReader */
    private $templateReader;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var QueueInterface */
    private $queue;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        TemplateReader $templateReader,
        MessageFactory $messageFactory,
        QueueInterface $queue,
        LoggerInterface $logger
    ) {
        $this->templateReader = $templateReader;
        $this->messageFactory = $messageFactory;
        $this->queue = $queue;
        $this->logger = $logger;
    }

    public function __invoke(DonorEvent $event)
    {
        $donor = $event->getDonor();

        $templateId = $event instanceof DonorStateUpdated
            ? $event->getNewState()->getStateId()
            : (string)new ClassIdExtractor($event);

        foreach ($this->templateReader->readTemplates($templateId) as $template) {
            $message = $this->messageFactory->createMessage($template, $donor);

            if (!$message) {
                continue;
            }

            $this->queue->store($message);

            $headers = new HeaderReader($message);

            $this->logger->notice(
                sprintf(
                    "Queued message '%s' to '%s'",
                    $headers->readHeader('subject'),
                    $headers->readHeader('to')
                )
            );
        }
    }
}
