<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Console\CommandInterface;
use byrokrat\giroapp\Console\Adapter;
use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\Exception\EmptyQueueException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MailerStatusCommand implements CommandInterface
{
    /**
     * @var QueueInterface
     */
    private $queue;

    public function __construct(QueueInterface $queue)
    {
        $this->queue = $queue;
    }

    public function configure(Adapter $adapter): void
    {
        $adapter
            ->setName('mailer:status')
            ->setDescription('Inspect the mail queue')
            ->setHelp('Display the list of messages queued with the mailer plugin version @plugin_version@')
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
