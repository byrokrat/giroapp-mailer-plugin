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

declare(strict_types=1);

namespace byrokrat\giroapp\Mailer;

use byrokrat\giroapp\Console\ConsoleInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MailerRemoveConsole implements ConsoleInterface
{
    private MessageRepositoryInterface $repository;
    private LoggerInterface $logger;

    public function __construct(MessageRepositoryInterface $repository, LoggerInterface $logger)
    {
        $this->repository = $repository;
        $this->logger = $logger;
    }

    public function configure(Command $command): void
    {
        $command
            ->setName('mailer:rm')
            ->setDescription('Remove all mails to recipient')
            ->setHelp('Remove all mails to recipient without sending them [giroapp-mailer-plugin @plugin_version@]')
            ->addArgument('recipient', InputArgument::REQUIRED, 'Recipient mail address to remove');
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var string */
        $recipient = $input->getArgument('recipient');

        foreach ($this->repository->fetchForRecipient($recipient) as $message) {
            $this->logger->info("Removed message '{$message->getSubject()}' to '{$message->getTo()}'");
        }
    }
}
