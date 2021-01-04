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
 * Copyright 2018-21 Hannes Forsgård
 */

declare(strict_types=1);

namespace byrokrat\giroapp\Mailer;

use Genkgo\Mail\FormattedMessageFactory;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\Header\Cc;
use Genkgo\Mail\Header\Bcc;
use Genkgo\Mail\Header\ReplyTo;

final class GenkgoMessageFactory implements MessageFactoryInterface
{
    private FormattedMessageFactory $internalFactory;

    public function __construct(FormattedMessageFactory $internalFactory)
    {
        $this->internalFactory = $internalFactory;
    }

    public function createMessage(Template $template): MessageInterface
    {
        $message = $this->internalFactory
            ->withHtml($template->body)
            ->createMessage()
            ->withHeader(new Subject($template->subject))
            ->withHeader(From::fromEmailAddress($template->from))
            ->withHeader(To::fromSingleRecipient($template->to));

        if ($template->replyto) {
            $message = $message->withHeader(ReplyTo::fromSingleRecipient($template->replyto));
        }

        foreach ($template->cc as $cc) {
            $message = $message->withHeader(Cc::fromSingleRecipient($cc));
        }

        foreach ($template->bcc as $bcc) {
            $message = $message->withHeader(Bcc::fromSingleRecipient($bcc));
        }

        return new GenkgoMessage($message);
    }
}
