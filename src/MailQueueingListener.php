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

use byrokrat\giroapp\Event\ChangesCommitted;
use byrokrat\giroapp\Event\Listener\ListenerInterface;
use Psr\Log\LoggerInterface;

final class MailQueueingListener implements ListenerInterface
{
    private MessageBuffer $messageBuffer;
    private MessageRepositoryInterface $repository;
    private LoggerInterface $logger;

    public function __construct(
        MessageBuffer $messageBuffer,
        MessageRepositoryInterface $repository,
        LoggerInterface $logger
    ) {
        $this->messageBuffer = $messageBuffer;
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function __invoke(ChangesCommitted $event): void
    {
        foreach ($this->messageBuffer->getMessages() as $message) {
            $this->repository->store($message);
            $this->logger->info("Queued message '{$message->getSubject()}' to '{$message->getTo()}'");
        }
    }
}
