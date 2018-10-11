<?php

declare(strict_types = 1);

namespace byrokrat\giroappmailerplugin;

use byrokrat\giroapp\Model\Donor;
use hkod\frontmatter\Parser;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\Bcc;
use Genkgo\Mail\Header\ReplyTo;

class MessageFactory
{
    /**
     * @var Parser
     */
    private $frontmatterParser;

    /**
     * @var FormattedMessageFactory
     */
    private $messageFactory;

    public function __construct(Parser $frontmatterParser, FormattedMessageFactory $factory)
    {
        $this->frontmatterParser = $frontmatterParser;
        $this->messageFactory = $factory;
    }

    public function createMessage(string $tmpl, Donor $donor): ?MessageInterface
    {
        $result = $this->frontmatterParser->parse($tmpl, $donor);
        $meta = array_change_key_case($result->getFrontmatter(), CASE_LOWER);

        if (empty(trim($result->getBody()))) {
            return null;
        }

        $message = $this->messageFactory
            ->withHtml($result->getBody())
            ->createMessage()
            ->withHeader(new Subject($meta['subject'] ?? ''))
            ->withHeader(From::fromEmailAddress($meta['from'] ?? ''));

        if (isset($meta['replyto'])) {
            $message = $message->withHeader(ReplyTo::fromSingleRecipient($meta['replyto']));
        }

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
