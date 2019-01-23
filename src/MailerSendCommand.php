<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Console\CommandInterface;
use byrokrat\giroapp\Console\Adapter;
use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\TransportInterface;
use Genkgo\Mail\Exception\EmptyQueueException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MailerSendCommand implements CommandInterface
{
    /**
     * @var QueueInterface
     */
    private $queue;

    /**
     * @var TransportInterface
     */
    private $transport;

    public function __construct(QueueInterface $queue, TransportInterface $transport)
    {
        $this->queue = $queue;
        $this->transport = $transport;
    }

    public function configure(Adapter $adapter): void
    {
        $adapter
            ->setName('mailer:send')
            ->setDescription('Send queued mails')
            ->setHelp('Send all messages queued with the mailer plugin version @plugin_version@')
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
                    $output->writeln(
                        sprintf(
                            "Sent message '%s' to '%s'",
                            $headers->readHeader('subject'),
                            $headers->readHeader('to')
                        )
                    );
                } catch (\Exception $e) {
                    $this->queue->store($message);
                    $output->writeln(
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
