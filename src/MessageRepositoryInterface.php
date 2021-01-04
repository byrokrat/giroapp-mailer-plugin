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

namespace byrokrat\giroapp\Mailer;

interface MessageRepositoryInterface extends \Countable
{
    /**
     * Fetch all messages from repository
     *
     * @return \Generator<MessageInterface>
     */
    public function fetchAll(): \Generator;

    /**
     * Inspect repository, messages are not removed
     *
     * @return \Generator<MessageInterface>
     */
    public function inspectAll(): \Generator;

    /**
     * Fetch all messages to recipient
     *
     * @return \Generator<MessageInterface>
     */
    public function fetchForRecipient(string $recipient): \Generator;

    /**
     * Write a message to repository
     */
    public function store(MessageInterface $message): void;

    /**
     * Fetch message from repository
     */
    public function fetch(): ?MessageInterface;
}
