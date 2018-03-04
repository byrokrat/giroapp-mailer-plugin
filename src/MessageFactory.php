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
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\Bcc;

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

        $message = $this->messageFactory
            ->withHtml($result->getBody())
            ->createMessage()
            ->withHeader(new Subject($meta['subject'] ?? ''))
            ->withHeader(From::fromEmailAddress($meta['from'] ?? ''));

        foreach ((array)($meta['to'] ?? $donor->getEmail()) as $to) {
            $message = $message->withHeader(To::fromSingleRecipient($to));
        }

        foreach ((array)($meta['cc'] ?? []) as $cc) {
            $message = $message->withHeader(Cc::fromSingleRecipient($cc));
        }

        foreach ((array)($meta['bcc'] ?? []) as $bcc) {
            $message = $message->withHeader(Bcc::fromSingleRecipient($bcc));
        }

        return $message;
    }
}
