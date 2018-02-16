<?php

declare(strict_types = 1);

namespace hanneskod\GiroappMailerPlugin;

use byrokrat\giroapp\Model\Donor;
use hkod\frontmatter\Parser;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\To;

/**
 * Create mail messages
 */
class MessageFactory
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var FormattedMessageFactory
     */
    private $messageFactory;

    public function __construct(Parser $parser, FormattedMessageFactory $factory)
    {
        $this->parser = $parser;
        $this->messageFactory = $factory;
    }

    public function createMessage(string $tmpl, Donor $donor): MessageInterface
    {
        $result = $this->parser->parse($tmpl, $donor);
        $meta = $result->getFrontmatter();

        return $this->messageFactory
            ->withHtml($result->getBody())
            ->createMessage()
            ->withHeader(new Subject($meta['subject'] ?? ''))
            ->withHeader(From::fromEmailAddress($meta['from'] ?? ''))
            ->withHeader(To::fromSingleRecipient($meta['to'] ?? $donor->getEmail()));
    }
}
