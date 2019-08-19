<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Console\ConsoleInterface;
use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\Exception\EmptyQueueException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MailerClearConsole implements ConsoleInterface
{
    /**
     * @var QueueInterface
     */
    private $queue;

    public function __construct(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    public function configure(Command $command): void
    {
        $command
            ->setName('mailer:clear')
            ->setDescription('Clear the mail queue without sending mails')
            ->setHelp('Clear the mail queue without sending mails [giroapp-mailer-plugin @plugin_version@]')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        try {
            while (true) {
                $message = $this->queue->fetch();
                $headers = new HeaderReader($message);
                $output->writeln(
                    sprintf(
                        "Removed message '%s' to '%s'",
                        $headers->readHeader('subject'),
                        $headers->readHeader('to')
                    )
                );
            }
        } catch (EmptyQueueException $e) {
            $output->writeln("Mail queue cleared..");
        }
    }
}
