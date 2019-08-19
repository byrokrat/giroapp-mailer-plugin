<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use Genkgo\Mail\Exception\EmptyQueueException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MailerStatusConsole extends AbstractBaseConsole
{
    public function configure(Command $command): void
    {
        $command
            ->setName('mailer:status')
            ->setDescription('Inspect the mail queue')
            ->setHelp('Display the list of messages queued [giroapp-mailer-plugin @plugin_version@]')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $messages = [];

        try {
            while (true) {
                $message = $this->queue->fetch();
                $headers = new HeaderReader($message);
                $output->writeln(
                    sprintf(
                        "Message '%s' to '%s'",
                        $headers->readHeader('subject'),
                        $headers->readHeader('to')
                    )
                );
                $messages[] = $message;
            }
        } catch (EmptyQueueException $e) {
            $output->writeln("No more messages..");
        }

        foreach ($messages as $message) {
            $this->queue->store($message);
        }
    }
}
