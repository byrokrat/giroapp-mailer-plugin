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
 * Copyright 2018-21 Hannes Forsgård
 */

declare(strict_types = 1);

namespace byrokrat\giroapp\Mailer;

use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Event\Listener\ListenerInterface;
use Psr\Log\LoggerInterface;

final class MailCreatingListener implements ListenerInterface
{
    private MessageFactoryInterface $messageFactory;
    private MessageBuffer $messageBuffer;
    private TemplateReader $templateReader;
    private LoggerInterface $logger;

    public function __construct(
        MessageFactoryInterface $messageFactory,
        MessageBuffer $messageBuffer,
        TemplateReader $templateReader,
        LoggerInterface $logger
    ) {
        $this->templateReader = $templateReader;
        $this->messageFactory = $messageFactory;
        $this->messageBuffer = $messageBuffer;
        $this->logger = $logger;
    }

    public function __invoke(DonorEvent $event): void
    {
        foreach ($this->templateReader->getTemplatesForEvent($event) as $template) {
            $message = $this->messageFactory->createMessage($template);

            $this->messageBuffer->add($message);

            $this->logger->info("Created message '{$message->getSubject()}' to '{$message->getTo()}'");
        }
    }
}
