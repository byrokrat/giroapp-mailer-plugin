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

namespace byrokrat\giroapp\Mailer;

use Genkgo\Mail\Exception\EmptyQueueException;
use Genkgo\Mail\Queue\QueueInterface;

final class GenkgoRepository implements MessageRepositoryInterface
{
    private QueueInterface $queue;

    public function __construct(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    public function count(): int
    {
        return iterator_count($this->inspectAll());
    }

    /**
     * @return \Generator<MessageInterface>
     */
    public function fetchAll(): \Generator
    {
        while (true) {
            $message = $this->fetch();

            if (!$message) {
                break;
            }

            yield $message;
        }
    }

    /**
     * @return \Generator<MessageInterface>
     */
    public function inspectAll(): \Generator
    {
        $messages = [];

        foreach ($this->fetchAll() as $message) {
            $messages[] = $message;
            yield $message;
        }

        foreach ($messages as $message) {
            $this->store($message);
        }
    }

    /**
     * @return \Generator<MessageInterface>
     */
    public function fetchForRecipient(string $recipient): \Generator
    {
        $toKeep = [];

        foreach ($this->fetchAll() as $message) {
            if ($message->getTo() == $recipient) {
                yield $message;
                continue;
            }

            $toKeep[] = $message;
        }

        foreach ($toKeep as $message) {
            $this->store($message);
        }
    }

    public function store(MessageInterface $message): void
    {
        if (!$message instanceof GenkgoMessage) {
            throw new \LogicException('GenkgoRepository can only store GenkgoMessage objects');
        }

        $this->queue->store($message->getRawMessage());
    }

    public function fetch(): ?MessageInterface
    {
        try {
            return new GenkgoMessage($this->queue->fetch());
        } catch (EmptyQueueException $e) {
            return null;
        }
    }
}
