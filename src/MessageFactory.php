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

use byrokrat\giroapp\Event\DonorEvent;
use byrokrat\giroapp\Event\DonorEmailUpdated;
use hkod\frontmatter\Parser;
use Genkgo\Mail\MessageInterface;
use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\Bcc;
use Genkgo\Mail\Header\ReplyTo;

final class MessageFactory
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

    public function createMessage(string $tmpl, DonorEvent $event): ?MessageInterface
    {
        $targetMail = $event instanceof DonorEmailUpdated
            ? $event->getNewEmail()
            : $event->getDonor()->getEmail();

        if (!$targetMail) {
            return null;
        }

        $result = $this->frontmatterParser->parse($tmpl, $event);

        $meta = array_change_key_case($result->getFrontmatter(), CASE_LOWER);

        if (empty(trim($result->getBody()))) {
            return null;
        }

        $message = $this->messageFactory
            ->withHtml($result->getBody())
            ->createMessage()
            ->withHeader(new Subject($meta['subject'] ?? ''))
            ->withHeader(From::fromEmailAddress($meta['from'] ?? ''))
            ->withHeader(To::fromSingleRecipient($targetMail));

        if (isset($meta['replyto'])) {
            $message = $message->withHeader(ReplyTo::fromSingleRecipient($meta['replyto']));
        }

        foreach ((array)($meta['to'] ?? []) as $to) {
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
