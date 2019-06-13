<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Event\DonorStateUpdated;
use byrokrat\giroapp\Event\Listener\ListenerInterface;
use byrokrat\giroapp\Plugin\EnvironmentInterface;
use Psr\Log\LogLevel;
use Genkgo\Mail\Queue\QueueInterface;

final class DonorStateListener implements ListenerInterface
{
    /** @var TemplateReader */
    private $templateReader;

    /** @var MessageFactory */
    private $messageFactory;

    /** @var QueueInterface */
    private $queue;

    /** @var EnvironmentInterface */
    private $giroappEnv;

    public function __construct(
        TemplateReader $templateReader,
        MessageFactory $messageFactory,
        QueueInterface $queue,
        EnvironmentInterface $giroappEnv
    ) {
        $this->templateReader = $templateReader;
        $this->messageFactory = $messageFactory;
        $this->queue = $queue;
        $this->giroappEnv = $giroappEnv;
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

            $this->giroappEnv->log(
                LogLevel::NOTICE,
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
