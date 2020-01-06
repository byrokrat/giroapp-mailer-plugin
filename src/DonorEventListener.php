<?php
/**
 * This file is part of giroapp-mailer-plugin.
 *
 * giroapp-mailer-plugin is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * giroapp-mailer-plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with giroapp-mailer-plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * Copyright 2018-20 Hannes Forsgård
 */

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

    public function __invoke(DonorEvent $event): void
    {
        $templateId = $event instanceof DonorStateUpdated
            ? $event->getNewState()->getStateId()
            : (string)new ClassIdExtractor($event);

        foreach ($this->templateReader->readTemplates($templateId) as $template) {
            $message = $this->messageFactory->createMessage($template, $event);

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
