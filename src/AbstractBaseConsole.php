<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Console\ConsoleInterface;
use Genkgo\Mail\Queue\QueueInterface;
use Psr\Log\LoggerInterface;

abstract class AbstractBaseConsole implements ConsoleInterface
{
    /** @var LoggerInterface */
    protected $logger;

    /** @var QueueInterface */
    protected $queue;

    public function __construct(LoggerInterface $logger, QueueInterface $queue)
    {
        $this->logger = $logger;
        $this->queue = $queue;
    }
}
