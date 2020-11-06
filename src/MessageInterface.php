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

namespace byrokrat\giroapp\Mailer;

interface MessageInterface
{
    public function getBody(): string;

    public function getSubject(): string;

    public function getFrom(): string;

    public function getReplyTo(): string;

    public function getTo(): string;

    /** @return array<string> */
    public function getCc(): array;

    /** @return array<string> */
    public function getBcc(): array;
}
