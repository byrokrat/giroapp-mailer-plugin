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

declare(strict_types = 1);

namespace byrokrat\giroapp\Mailer;

use Genkgo\Mail\MessageInterface as GenkgoMessageInterface;

final class GenkgoMessage implements MessageInterface
{
    private GenkgoMessageInterface $message;

    public function __construct(GenkgoMessageInterface $message)
    {
        $this->message = $message;
    }

    public function getBody(): string
    {
        return $this->message->getBody()->__toString();
    }

    public function getSubject(): string
    {
        return $this->getHeader('Subject');
    }

    public function getFrom(): string
    {
        return $this->getHeader('From');
    }

    public function getReplyTo(): string
    {
        return $this->getHeader('Reply-To');
    }

    public function getTo(): string
    {
        return $this->getHeader('To');
    }

    /** @return array<string> */
    public function getCc(): array
    {
        return iterator_to_array($this->getHeaders('Cc'));
    }

    /** @return array<string> */
    public function getBcc(): array
    {
        return iterator_to_array($this->getHeaders('Bcc'));
    }

    public function getRawMessage(): GenkgoMessageInterface
    {
        return $this->message;
    }

    /** @return \Generator<string> */
    private function getHeaders(string $name): \Generator
    {
        foreach ($this->message->getHeader($name) as $header) {
            yield (string)iconv_mime_decode($header->getValue()->__toString());
        }
    }

    private function getHeader(string $name): string
    {
        foreach ($this->getHeaders($name) as $header) {
            return $header;
        }

        return '';
    }
}
