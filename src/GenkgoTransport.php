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

use Genkgo\Mail\TransportInterface as GenkgoTransportInterface;

final class GenkgoTransport implements TransportInterface
{
    private GenkgoTransportInterface $transport;

    public function __construct(GenkgoTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function send(MessageInterface $message): void
    {
        if (!$message instanceof GenkgoMessage) {
            throw new \LogicException('GenkgoTransport can only send GenkgoMessage objects');
        }

        try {
            $this->transport->send($message->getRawMessage());
        } catch (\Exception $e) {
            throw new TransportNotReadyException(
                "Unable to send message to '{$message->getTo()}': transport not ready?"
            );
        }
    }
}
