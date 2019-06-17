<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Event\DonorStateUpdated;
use byrokrat\giroapp\Event\Listener\ListenerInterface;
use Psr\Log\LoggerInterface;
use Genkgo\Mail\Queue\QueueInterface;

final class DonorStateListener implements ListenerInterface
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

    public function __invoke(DonorStateUpdated $event)
    {
        $donor = $event->getDonor();

        $stateId = $event->getNewState()->getStateId();

        foreach ($this->templateReader->readTemplates($stateId) as $tmpl) {
            $message = $this->messageFactory->createMessage($tmpl, $donor);

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
                ),
                ['new_state' => $stateId]
            );
        }
    }
}
