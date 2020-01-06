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

use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\TransportInterface;
use Genkgo\Mail\Exception\EmptyQueueException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MailerSendConsole extends AbstractBaseConsole
{
    /** @var TransportInterface */
    private $transport;

    public function __construct(LoggerInterface $logger, QueueInterface $queue, TransportInterface $transport)
    {
        parent::__construct($logger, $queue);
        $this->transport = $transport;
    }

    public function configure(Command $command): void
    {
        $command
            ->setName('mailer:send')
            ->setDescription('Send queued mails')
            ->setHelp('Send all messages queued [giroapp-mailer-plugin @plugin_version@]')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            while (true) {
                $message = $this->queue->fetch();
                $headers = new HeaderReader($message);
                try {
                    $this->transport->send($message);
                    $this->logger->notice(
                        sprintf(
                            "Sent message '%s' to '%s'",
                            $headers->readHeader('subject'),
                            $headers->readHeader('to')
                        )
                    );
                } catch (\Exception $e) {
                    $this->queue->store($message);
                    $this->logger->error(
                        sprintf(
                            "Unable to send message to '%s': transport not ready?",
                            $headers->readHeader('to')
                        )
                    );
                    break;
                }
            }
        } catch (EmptyQueueException $e) {
            $output->writeln("No more messages..");
        }
    }
}
