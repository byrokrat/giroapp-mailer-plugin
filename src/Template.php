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

final class Template
{
    public string $body;
    public string $subject;
    public string $from;
    public string $replyto;
    public string $to;
    /** @var array<string> */
    public array $cc;
    /** @var array<string> */
    public array $bcc;

    /**
     * @param array<string> $cc
     * @param array<string> $bcc
     */
    public function __construct(
        string $body,
        string $subject,
        string $from,
        string $replyto,
        string $to,
        array $cc,
        array $bcc
    ) {
        $this->body = $body;
        $this->subject = $subject;
        $this->from = $from;
        $this->replyto = $replyto;
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
    }
}
